<?php

namespace App\Core;

use PDO;

class Auth
{
    private static ?array $currentUser = null;

    public static function check(): bool
    {
        return Session::get('user_id') !== null;
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$currentUser !== null) {
            return self::$currentUser;
        }

        $userId = self::id();
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.*, r.name as role_name, r.slug as role_slug
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.id = :id AND u.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            self::logout();
            return null;
        }

        // Fetch user permissions
        $permStmt = $db->prepare("
            SELECT DISTINCT p.slug
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN user_roles ur ON rp.role_id = ur.role_id
            WHERE ur.user_id = :id
        ");
        $permStmt->execute([':id' => $userId]);
        $user['permissions'] = $permStmt->fetchAll(PDO::FETCH_COLUMN);

        self::$currentUser = $user;
        return $user;
    }

    public static function attempt(string $email, string $password): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => strtolower(trim($email))]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        if ($user['status'] !== 'active') {
            return false;
        }

        if (password_verify($password, $user['password_hash'])) {
            Session::regenerate();
            Session::set('user_id', (int)$user['id']);
            Session::set('user_email', $user['email']);
            Session::set('user_name', $user['name']);

            // Update last login
            $upStmt = $db->prepare("UPDATE users SET last_login_at = datetime('now') WHERE id = :id");
            $upStmt->execute([':id' => $user['id']]);

            // Audit
            self::audit('login', 'auth', 'users', $user['id'], 'Autenticação bem-sucedida');
            return true;
        }

        return false;
    }

    public static function logout(): void
    {
        if (self::check()) {
            self::audit('logout', 'auth', 'users', self::id(), 'Sessão terminada');
        }
        Session::destroy();
        self::$currentUser = null;
    }

    public static function hasRole(string|array $roles): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        $roles = (array)$roles;
        if ($user['role_slug'] === 'super_admin') {
            return true; // Super admin has full access
        }

        return in_array($user['role_slug'], $roles, true);
    }

    public static function can(string $permission): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        if ($user['role_slug'] === 'super_admin') {
            return true;
        }

        return in_array($permission, $user['permissions'] ?? [], true);
    }

    public static function audit(string $action, string $module, ?string $entity = null, ?int $entityId = null, ?string $details = null): void
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, module, entity, entity_id, details, ip_address, created_at)
                VALUES (:user_id, :action, :module, :entity, :entity_id, :details, :ip, datetime('now'))
            ");
            $stmt->execute([
                ':user_id' => self::id(),
                ':action' => $action,
                ':module' => $module,
                ':entity' => $entity,
                ':entity_id' => $entityId,
                ':details' => $details,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
        } catch (\Throwable $e) {
            Logger::error("Audit log error: " . $e->getMessage());
        }
    }
}
