<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use PDO;

class PropertyController
{
    public function index(Request $request): void
    {
        $db = Database::getConnection();
        $status = $request->query('status');
        $type = $request->query('type');
        $search = trim((string)$request->query('q'));

        $query = "
            SELECT p.*, 
                   count(DISTINCT b.id) as buildings_count,
                   count(DISTINCT u.id) as units_count,
                   count(DISTINCT a.id) as assets_count
            FROM properties p
            LEFT JOIN buildings b ON p.id = b.property_id
            LEFT JOIN units u ON p.id = u.property_id
            LEFT JOIN assets a ON p.id = a.property_id
            WHERE 1=1
        ";
        $params = [];

        if ($status) {
            $query .= " AND p.status = :status";
            $params[':status'] = $status;
        }
        if ($type) {
            $query .= " AND p.type = :type";
            $params[':type'] = $type;
        }
        if ($search) {
            $query .= " AND (p.name LIKE :search OR p.reference LIKE :search OR p.address LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $query .= " GROUP BY p.id ORDER BY p.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $properties = $stmt->fetchAll();

        View::render('admin/properties/index', [
            'title' => 'Gestão de Imóveis - JFS Properties',
            'properties' => $properties,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'q' => $search
            ]
        ]);
    }

    public function create(Request $request): void
    {
        $db = Database::getConnection();
        $managers = $db->query("SELECT id, name FROM users WHERE status = 'active'")->fetchAll();

        View::render('admin/properties/create', [
            'title' => 'Novo Imóvel - JFS Properties',
            'managers' => $managers
        ]);
    }

    public function store(Request $request): void
    {
        $db = Database::getConnection();

        $name = trim((string)$request->input('name'));
        $type = trim((string)$request->input('type'));
        $address = trim((string)$request->input('address'));
        $city = trim((string)$request->input('city', 'Maputo'));
        $province = trim((string)$request->input('province', 'Maputo Cidade'));
        $totalArea = (float)$request->input('total_area', 0);
        $description = trim((string)$request->input('description'));
        $managerId = $request->input('manager_id') ? (int)$request->input('manager_id') : null;

        if (empty($name) || empty($address)) {
            Session::setFlash('error', 'Nome e endereço são obrigatórios.');
            Response::back();
        }

        // Generate unique reference PRP-YYYY-NNN
        $year = date('Y');
        $count = (int)$db->query("SELECT count(*) FROM properties")->fetchColumn() + 1;
        $ref = sprintf("PRP-%s-%03d", $year, $count);

        $stmt = $db->prepare("
            INSERT INTO properties (reference, name, type, description, address, province, city, total_area, status, manager_id, created_at, updated_at)
            VALUES (:ref, :name, :type, :desc, :addr, :prov, :city, :area, 'active', :mgr, datetime('now'), datetime('now'))
        ");
        $stmt->execute([
            ':ref' => $ref,
            ':name' => $name,
            ':type' => $type,
            ':desc' => $description,
            ':addr' => $address,
            ':prov' => $province,
            ':city' => $city,
            ':area' => $totalArea,
            ':mgr' => $managerId
        ]);

        $propId = (int)$db->lastInsertId();
        Auth::audit('create', 'properties', 'properties', $propId, "Imóvel {$ref} - {$name} criado.");
        Session::setFlash('success', "Imóvel {$ref} registado com sucesso!");
        Response::redirect("/properties/{$propId}");
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT p.*, u.name as manager_name, u.email as manager_email
            FROM properties p
            LEFT JOIN users u ON p.manager_id = u.id
            WHERE p.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $property = $stmt->fetch();

        if (!$property) {
            Session::setFlash('error', 'Imóvel não encontrado.');
            Response::redirect('/properties');
        }

        // Tab selection
        $tab = $request->query('tab', 'overview');

        // Fetch buildings & units
        $bStmt = $db->prepare("
            SELECT b.*, count(u.id) as units_count 
            FROM buildings b
            LEFT JOIN units u ON b.id = u.building_id
            WHERE b.property_id = :id
            GROUP BY b.id
        ");
        $bStmt->execute([':id' => $id]);
        $buildings = $bStmt->fetchAll();

        $uStmt = $db->prepare("
            SELECT u.*, b.name as building_name, f.name as floor_name, o.name as occupant_name
            FROM units u
            LEFT JOIN buildings b ON u.building_id = b.id
            LEFT JOIN floors f ON u.floor_id = f.id
            LEFT JOIN property_occupants po ON u.id = po.unit_id AND po.status = 'active'
            LEFT JOIN occupants o ON po.occupant_id = o.id
            WHERE u.property_id = :id
            ORDER BY u.reference ASC
        ");
        $uStmt->execute([':id' => $id]);
        $units = $uStmt->fetchAll();

        // Incidents
        $iStmt = $db->prepare("
            SELECT i.*, c.name as category_name, t.name as technician_name
            FROM incidents i
            JOIN incident_categories c ON i.category_id = c.id
            LEFT JOIN technicians t ON i.assigned_technician_id = t.id
            WHERE i.property_id = :id
            ORDER BY i.created_at DESC
        ");
        $iStmt->execute([':id' => $id]);
        $incidents = $iStmt->fetchAll();

        // Work Orders
        $woStmt = $db->prepare("
            SELECT w.*, t.name as technician_name
            FROM work_orders w
            LEFT JOIN technicians t ON w.technician_id = t.id
            WHERE w.property_id = :id
            ORDER BY w.created_at DESC
        ");
        $woStmt->execute([':id' => $id]);
        $workOrders = $woStmt->fetchAll();

        // Assets
        $aStmt = $db->prepare("
            SELECT a.*, c.name as category_name
            FROM assets a
            JOIN asset_categories c ON a.category_id = c.id
            WHERE a.property_id = :id
            ORDER BY a.name ASC
        ");
        $aStmt->execute([':id' => $id]);
        $assets = $aStmt->fetchAll();

        // Expenses
        $eStmt = $db->prepare("
            SELECT e.*, c.name as category_name, s.name as supplier_name
            FROM expenses e
            JOIN expense_categories c ON e.category_id = c.id
            LEFT JOIN suppliers s ON e.supplier_id = s.id
            WHERE e.property_id = :id
            ORDER BY e.expense_date DESC
        ");
        $eStmt->execute([':id' => $id]);
        $expenses = $eStmt->fetchAll();

        // Documents
        $docStmt = $db->prepare("
            SELECT * FROM property_documents WHERE property_id = :id ORDER BY created_at DESC
        ");
        $docStmt->execute([':id' => $id]);
        $documents = $docStmt->fetchAll();

        View::render('admin/properties/show', [
            'title' => "{$property['name']} ({$property['reference']}) - JFS Properties",
            'property' => $property,
            'tab' => $tab,
            'buildings' => $buildings,
            'units' => $units,
            'incidents' => $incidents,
            'workOrders' => $workOrders,
            'assets' => $assets,
            'expenses' => $expenses,
            'documents' => $documents,
        ]);
    }

    public function archive(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getConnection();

        $stmt = $db->prepare("UPDATE properties SET status = 'archived', updated_at = datetime('now') WHERE id = :id");
        $stmt->execute([':id' => $id]);

        Auth::audit('archive', 'properties', 'properties', $id, "Imóvel arquivado.");
        Session::setFlash('success', 'Imóvel arquivado com sucesso.');
        Response::redirect('/properties');
    }
}
