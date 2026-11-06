<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use PDO;

class OccupantController
{
    public function index(Request $request): void
    {
        $db = Database::getConnection();
        $search = trim((string)$request->query('q'));

        $query = "
            SELECT o.*, 
                   u.reference as unit_ref, u.name as unit_name,
                   p.name as property_name,
                   po.start_date, po.rent_amount
            FROM occupants o
            LEFT JOIN property_occupants po ON o.id = po.occupant_id AND po.status = 'active'
            LEFT JOIN units u ON po.unit_id = u.id
            LEFT JOIN properties p ON u.property_id = p.id
            WHERE 1=1
        ";
        $params = [];

        if ($search) {
            $query .= " AND (o.name LIKE :search OR o.email LIKE :search OR o.phone LIKE :search OR u.reference LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $query .= " ORDER BY o.name ASC";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $occupants = $stmt->fetchAll();

        View::render('admin/occupants/index', [
            'title' => 'Gestão de Ocupantes - JFS Properties',
            'occupants' => $occupants,
            'search' => $search
        ]);
    }

    public function create(Request $request): void
    {
        $db = Database::getConnection();
        // Fetch available units
        $units = $db->query("
            SELECT u.id, u.reference, u.name, p.name as property_name 
            FROM units u
            JOIN properties p ON u.property_id = p.id
            WHERE u.status = 'available'
            ORDER BY p.name ASC, u.reference ASC
        ")->fetchAll();

        View::render('admin/occupants/create', [
            'title' => 'Registar Novo Ocupante - JFS Properties',
            'units' => $units
        ]);
    }

    public function store(Request $request): void
    {
        $db = Database::getConnection();

        $name = trim((string)$request->input('name'));
        $type = trim((string)$request->input('type', 'tenant'));
        $email = strtolower(trim((string)$request->input('email')));
        $phone = trim((string)$request->input('phone'));
        $idCard = trim((string)$request->input('id_card_number'));
        $nuit = trim((string)$request->input('nuit'));
        $emergencyName = trim((string)$request->input('emergency_contact_name'));
        $emergencyPhone = trim((string)$request->input('emergency_contact_phone'));
        $unitId = $request->input('unit_id') ? (int)$request->input('unit_id') : null;
        $rentAmount = (float)$request->input('rent_amount', 0);
        $createPortalAccess = (bool)$request->input('create_portal_access');

        if (empty($name) || empty($email) || empty($phone)) {
            Session::setFlash('error', 'Nome, e-mail e telefone são campos obrigatórios.');
            Response::back();
        }

        Database::beginTransaction();
        try {
            $userId = null;
            if ($createPortalAccess) {
                // Check if user already exists
                $userCheck = $db->prepare("SELECT id FROM users WHERE email = :email");
                $userCheck->execute([':email' => $email]);
                $existingUser = $userCheck->fetch();

                if ($existingUser) {
                    $userId = (int)$existingUser['id'];
                } else {
                    $defaultPassword = password_hash('Ocupante123!', PASSWORD_BCRYPT);
                    $uStmt = $db->prepare("
                        INSERT INTO users (name, email, password_hash, phone, status, created_at)
                        VALUES (:name, :email, :pass, :phone, 'active', datetime('now'))
                    ");
                    $uStmt->execute([
                        ':name' => $name,
                        ':email' => $email,
                        ':pass' => $defaultPassword,
                        ':phone' => $phone
                    ]);
                    $userId = (int)$db->lastInsertId();

                    // Assign occupant role
                    $occRoleId = $db->query("SELECT id FROM roles WHERE slug = 'occupant'")->fetchColumn();
                    if ($occRoleId) {
                        $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")
                           ->execute([$userId, $occRoleId]);
                    }
                }
            }

            // Insert occupant
            $occStmt = $db->prepare("
                INSERT INTO occupants (user_id, name, type, email, phone, id_card_number, nuit, emergency_contact_name, emergency_contact_phone, status, created_at, updated_at)
                VALUES (:user_id, :name, :type, :email, :phone, :id_card, :nuit, :e_name, :e_phone, 'active', datetime('now'), datetime('now'))
            ");
            $occStmt->execute([
                ':user_id' => $userId,
                ':name' => $name,
                ':type' => $type,
                ':email' => $email,
                ':phone' => $phone,
                ':id_card' => $idCard,
                ':nuit' => $nuit,
                ':e_name' => $emergencyName,
                ':e_phone' => $emergencyPhone
            ]);
            $occupantId = (int)$db->lastInsertId();

            // Link to unit if provided
            if ($unitId) {
                $poStmt = $db->prepare("
                    INSERT INTO property_occupants (occupant_id, unit_id, start_date, rent_amount, status, created_at)
                    VALUES (:occ_id, :unit_id, date('now'), :rent, 'active', datetime('now'))
                ");
                $poStmt->execute([
                    ':occ_id' => $occupantId,
                    ':unit_id' => $unitId,
                    ':rent' => $rentAmount
                ]);

                // Update unit status to occupied
                $db->prepare("UPDATE units SET status = 'occupied', updated_at = datetime('now') WHERE id = :id")
                   ->execute([':id' => $unitId]);
            }

            Database::commit();
            Auth::audit('create', 'occupants', 'occupants', $occupantId, "Ocupante {$name} registado.");
            Session::setFlash('success', "Ocupante '{$name}' registado com sucesso.");
            Response::redirect('/occupants');
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', "Erro ao registar ocupante: " . $e->getMessage());
            Response::back();
        }
    }
}
