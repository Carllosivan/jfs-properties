<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use PDO;

class AdminController
{
    // USERS MANAGEMENT
    public function users(Request $request): void
    {
        $db = Database::getConnection();

        $users = $db->query("
            SELECT u.*, r.name as role_name, r.slug as role_slug
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            ORDER BY u.created_at DESC
        ")->fetchAll();

        $roles = $db->query("SELECT * FROM roles ORDER BY name ASC")->fetchAll();

        View::render('admin/administration/users', [
            'title' => 'Gestão de Utilizadores e Perfis - JFS Properties',
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function storeUser(Request $request): void
    {
        $db = Database::getConnection();

        $name = trim((string)$request->input('name'));
        $email = strtolower(trim((string)$request->input('email')));
        $phone = trim((string)$request->input('phone'));
        $roleId = (int)$request->input('role_id');
        $password = (string)$request->input('password');

        if (empty($name) || empty($email) || empty($password) || !$roleId) {
            Session::setFlash('error', 'Nome, e-mail, perfil e palavra-passe são obrigatórios.');
            Response::back();
        }

        // Check unique email
        $chk = $db->prepare("SELECT id FROM users WHERE email = :email");
        $chk->execute([':email' => $email]);
        if ($chk->fetch()) {
            Session::setFlash('error', 'Este endereço de e-mail já se encontra registado.');
            Response::back();
        }

        Database::beginTransaction();
        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("
                INSERT INTO users (name, email, password_hash, phone, status, created_at, updated_at)
                VALUES (:name, :email, :pass, :phone, 'active', datetime('now'), datetime('now'))
            ");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':pass' => $passwordHash,
                ':phone' => $phone
            ]);
            $newUserId = (int)$db->lastInsertId();

            $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")
               ->execute([$newUserId, $roleId]);

            Database::commit();
            Auth::audit('create', 'users', 'users', $newUserId, "Utilizador {$name} ({$email}) criado.");
            Session::setFlash('success', "Utilizador '{$name}' criado com sucesso.");
            Response::redirect('/admin/users');
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', "Erro ao criar utilizador: " . $e->getMessage());
            Response::back();
        }
    }

    // AUDIT LOGS
    public function audit(Request $request): void
    {
        $db = Database::getConnection();

        $logs = $db->query("
            SELECT al.*, u.name as user_name, u.email as user_email
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT 100
        ")->fetchAll();

        View::render('admin/administration/audit', [
            'title' => 'Auditoria e Rastreabilidade - JFS Properties',
            'logs' => $logs
        ]);
    }

    // SETTINGS
    public function settings(Request $request): void
    {
        $db = Database::getConnection();
        $settingsRows = $db->query("SELECT * FROM settings ORDER BY category, key")->fetchAll();

        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['key']] = $row['value'];
        }

        View::render('admin/administration/settings', [
            'title' => 'Configurações do Sistema - JFS Properties',
            'settings' => $settings
        ]);
    }

    public function updateSettings(Request $request): void
    {
        $db = Database::getConnection();
        $inputs = $request->input();

        Database::beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO settings (key, value, updated_at)
                VALUES (:key, :val, datetime('now'))
                ON CONFLICT(key) DO UPDATE SET value = :val2, updated_at = datetime('now')
            ");

            foreach ($inputs as $key => $val) {
                if (str_starts_with($key, '_')) continue; // Skip _csrf_token etc
                $stmt->execute([
                    ':key' => $key,
                    ':val' => (string)$val,
                    ':val2' => (string)$val
                ]);
            }

            Database::commit();
            Auth::audit('update', 'settings', 'settings', null, "Configurações do sistema actualizadas.");
            Session::setFlash('success', 'Configurações guardadas com sucesso.');
            Response::redirect('/admin/settings');
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', "Erro ao guardar configurações: " . $e->getMessage());
            Response::back();
        }
    }
}
