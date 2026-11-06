<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use PDO;

class AssetController
{
    public function index(Request $request): void
    {
        $db = Database::getConnection();
        $propertyId = $request->query('property_id');
        $categoryId = $request->query('category_id');
        $search = trim((string)$request->query('q'));

        $query = "
            SELECT a.*, 
                   p.name as property_name,
                   ac.name as category_name,
                   s.name as supplier_name,
                   count(DISTINCT w.id) as work_orders_count
            FROM assets a
            JOIN properties p ON a.property_id = p.id
            JOIN asset_categories ac ON a.category_id = ac.id
            LEFT JOIN suppliers s ON a.supplier_id = s.id
            LEFT JOIN work_orders w ON a.id = w.asset_id
            WHERE 1=1
        ";
        $params = [];

        if ($propertyId) {
            $query .= " AND a.property_id = :prop_id";
            $params[':prop_id'] = (int)$propertyId;
        }
        if ($categoryId) {
            $query .= " AND a.category_id = :cat_id";
            $params[':cat_id'] = (int)$categoryId;
        }
        if ($search) {
            $query .= " AND (a.name LIKE :search OR a.code LIKE :search OR a.manufacturer LIKE :search OR a.model LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $query .= " GROUP BY a.id ORDER BY a.name ASC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $assets = $stmt->fetchAll();

        $properties = $db->query("SELECT id, name FROM properties ORDER BY name ASC")->fetchAll();
        $categories = $db->query("SELECT id, name FROM asset_categories ORDER BY name ASC")->fetchAll();

        View::render('admin/assets/index', [
            'title' => 'Equipamentos e Activos - JFS Properties',
            'assets' => $assets,
            'properties' => $properties,
            'categories' => $categories,
            'filters' => [
                'property_id' => $propertyId,
                'category_id' => $categoryId,
                'q' => $search
            ]
        ]);
    }

    public function create(Request $request): void
    {
        $db = Database::getConnection();
        $properties = $db->query("SELECT id, name FROM properties ORDER BY name ASC")->fetchAll();
        $categories = $db->query("SELECT id, name FROM asset_categories ORDER BY name ASC")->fetchAll();
        $suppliers = $db->query("SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name ASC")->fetchAll();

        View::render('admin/assets/create', [
            'title' => 'Registar Equipamento / Activo - JFS Properties',
            'properties' => $properties,
            'categories' => $categories,
            'suppliers' => $suppliers
        ]);
    }

    public function store(Request $request): void
    {
        $db = Database::getConnection();

        $name = trim((string)$request->input('name'));
        $categoryId = (int)$request->input('category_id');
        $propertyId = (int)$request->input('property_id');
        $location = trim((string)$request->input('location_details'));
        $manufacturer = trim((string)$request->input('manufacturer'));
        $model = trim((string)$request->input('model'));
        $serial = trim((string)$request->input('serial_number'));
        $installDate = $request->input('installation_date') ?: date('Y-m-d');
        $warrantyExp = $request->input('warranty_expiration') ?: null;
        $supplierId = $request->input('supplier_id') ? (int)$request->input('supplier_id') : null;

        if (empty($name) || !$categoryId || !$propertyId) {
            Session::setFlash('error', 'Nome, Categoria e Imóvel são obrigatórios.');
            Response::back();
        }

        // Generate Code AST-CODE-NNN
        $catCode = $db->query("SELECT code FROM asset_categories WHERE id = {$categoryId}")->fetchColumn() ?: 'GEN';
        $count = (int)$db->query("SELECT count(*) FROM assets")->fetchColumn() + 1;
        $code = sprintf("AST-%s-%03d", $catCode, $count);

        $stmt = $db->prepare("
            INSERT INTO assets (
                code, name, category_id, property_id, location_details, manufacturer, model,
                serial_number, installation_date, warranty_expiration, supplier_id, status, created_at, updated_at
            ) VALUES (
                :code, :name, :cat_id, :prop_id, :loc, :mfg, :model,
                :serial, :inst_date, :warranty, :sup_id, 'operational', datetime('now'), datetime('now')
            )
        ");
        $stmt->execute([
            ':code' => $code,
            ':name' => $name,
            ':cat_id' => $categoryId,
            ':prop_id' => $propertyId,
            ':loc' => $location,
            ':mfg' => $manufacturer,
            ':model' => $model,
            ':serial' => $serial,
            ':inst_date' => $installDate,
            ':warranty' => $warrantyExp,
            ':sup_id' => $supplierId
        ]);

        $assetId = (int)$db->lastInsertId();
        Auth::audit('create', 'assets', 'assets', $assetId, "Equipamento {$code} - {$name} criado.");
        Session::setFlash('success', "Activo {$code} registado com sucesso.");
        Response::redirect("/assets/{$assetId}");
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT a.*, 
                   p.name as property_name, p.address as property_address,
                   ac.name as category_name,
                   s.name as supplier_name, s.phone as supplier_phone
            FROM assets a
            JOIN properties p ON a.property_id = p.id
            JOIN asset_categories ac ON a.category_id = ac.id
            LEFT JOIN suppliers s ON a.supplier_id = s.id
            WHERE a.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $asset = $stmt->fetch();

        if (!$asset) {
            Session::setFlash('error', 'Equipamento não encontrado.');
            Response::redirect('/assets');
        }

        // Fetch related Work Orders
        $woStmt = $db->prepare("
            SELECT w.*, t.name as technician_name
            FROM work_orders w
            LEFT JOIN technicians t ON w.technician_id = t.id
            WHERE w.asset_id = :id
            ORDER BY w.created_at DESC
        ");
        $woStmt->execute([':id' => $id]);
        $workOrders = $woStmt->fetchAll();

        // Fetch maintenance plans
        $pStmt = $db->prepare("
            SELECT * FROM maintenance_plans WHERE asset_id = :id
        ");
        $pStmt->execute([':id' => $id]);
        $plans = $pStmt->fetchAll();

        View::render('admin/assets/show', [
            'title' => "{$asset['name']} ({$asset['code']}) - JFS Properties",
            'asset' => $asset,
            'workOrders' => $workOrders,
            'plans' => $plans
        ]);
    }
}
