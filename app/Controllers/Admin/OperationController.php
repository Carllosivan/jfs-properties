<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\StockService;
use PDO;

class OperationController
{
    // TECHNICIANS & TEAMS
    public function technicians(Request $request): void
    {
        $db = Database::getConnection();

        $technicians = $db->query("
            SELECT t.*, tm.name as team_name,
                   count(DISTINCT w.id) as assigned_orders,
                   sum(CASE WHEN w.status IN ('completed', 'validated') THEN 1 ELSE 0 END) as completed_orders
            FROM technicians t
            LEFT JOIN teams tm ON t.team_id = tm.id
            LEFT JOIN work_orders w ON t.id = w.technician_id
            GROUP BY t.id
            ORDER BY t.name ASC
        ")->fetchAll();

        $teams = $db->query("
            SELECT tm.*, count(t.id) as members_count 
            FROM teams tm
            LEFT JOIN technicians t ON tm.id = t.team_id
            GROUP BY tm.id
        ")->fetchAll();

        View::render('admin/operations/technicians', [
            'title' => 'Técnicos e Equipas de Manutenção - JFS Properties',
            'technicians' => $technicians,
            'teams' => $teams
        ]);
    }

    public function storeTechnician(Request $request): void
    {
        $db = Database::getConnection();

        $name = trim((string)$request->input('name'));
        $email = trim((string)$request->input('email'));
        $phone = trim((string)$request->input('phone'));
        $specialization = trim((string)$request->input('specialization'));
        $teamId = $request->input('team_id') ? (int)$request->input('team_id') : null;
        $hourlyRate = (float)$request->input('hourly_rate', 400.0);

        if (empty($name) || empty($phone) || empty($specialization)) {
            Session::setFlash('error', 'Nome, telefone e especialização são obrigatórios.');
            Response::back();
        }

        $stmt = $db->prepare("
            INSERT INTO technicians (name, email, phone, specialization, team_id, status, hourly_rate, created_at)
            VALUES (:name, :email, :phone, :spec, :team_id, 'available', :rate, datetime('now'))
        ");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':spec' => $specialization,
            ':team_id' => $teamId,
            ':rate' => $hourlyRate
        ]);

        Session::setFlash('success', "Técnico '{$name}' registado com sucesso.");
        Response::redirect('/operations/technicians');
    }

    // SUPPLIERS
    public function suppliers(Request $request): void
    {
        $db = Database::getConnection();

        $suppliers = $db->query("
            SELECT s.*, 
                   count(DISTINCT e.id) as evaluations_count,
                   avg(e.quality_score) as avg_quality
            FROM suppliers s
            LEFT JOIN supplier_evaluations e ON s.id = e.supplier_id
            GROUP BY s.id
            ORDER BY s.rating DESC, s.name ASC
        ")->fetchAll();

        View::render('admin/operations/suppliers', [
            'title' => 'Fornecedores e Prestadores Externos - JFS Properties',
            'suppliers' => $suppliers
        ]);
    }

    public function storeSupplier(Request $request): void
    {
        $db = Database::getConnection();

        $name = trim((string)$request->input('name'));
        $tradeName = trim((string)$request->input('trade_name'));
        $nuit = trim((string)$request->input('nuit'));
        $serviceType = trim((string)$request->input('service_type'));
        $contactPerson = trim((string)$request->input('contact_person'));
        $phone = trim((string)$request->input('phone'));
        $email = trim((string)$request->input('email'));
        $address = trim((string)$request->input('address'));

        if (empty($name) || empty($serviceType) || empty($phone)) {
            Session::setFlash('error', 'Nome, tipo de serviço e telefone são obrigatórios.');
            Response::back();
        }

        $stmt = $db->prepare("
            INSERT INTO suppliers (name, trade_name, nuit, service_type, contact_person, phone, email, address, status, rating, created_at)
            VALUES (:name, :trade, :nuit, :service, :contact, :phone, :email, :address, 'active', 5.0, datetime('now'))
        ");
        $stmt->execute([
            ':name' => $name,
            ':trade' => $tradeName,
            ':nuit' => $nuit,
            ':service' => $serviceType,
            ':contact' => $contactPerson,
            ':phone' => $phone,
            ':email' => $email,
            ':address' => $address
        ]);

        Session::setFlash('success', "Fornecedor '{$name}' registado com sucesso.");
        Response::redirect('/operations/suppliers');
    }

    // STOCK & MATERIALS
    public function stock(Request $request): void
    {
        $db = Database::getConnection();

        $materials = $db->query("
            SELECT m.*, 
                   mc.name as category_name,
                   w.name as warehouse_name
            FROM materials m
            JOIN material_categories mc ON m.category_id = mc.id
            LEFT JOIN warehouses w ON m.warehouse_id = w.id
            ORDER BY m.name ASC
        ")->fetchAll();

        $movements = $db->query("
            SELECT sm.*, m.name as material_name, m.code as material_code, m.unit_of_measure,
                   u.name as user_name
            FROM stock_movements sm
            JOIN materials m ON sm.material_id = m.id
            LEFT JOIN users u ON sm.user_id = u.id
            ORDER BY sm.created_at DESC LIMIT 20
        ")->fetchAll();

        $categories = $db->query("SELECT * FROM material_categories ORDER BY name ASC")->fetchAll();
        $warehouses = $db->query("SELECT * FROM warehouses ORDER BY name ASC")->fetchAll();

        $lowStockCount = (int)$db->query("SELECT count(*) FROM materials WHERE quantity_available <= min_stock_level")->fetchColumn();

        View::render('admin/operations/stock', [
            'title' => 'Gestão de Materiais e Stock - JFS Properties',
            'materials' => $materials,
            'movements' => $movements,
            'categories' => $categories,
            'warehouses' => $warehouses,
            'lowStockCount' => $lowStockCount
        ]);
    }

    public function recordStockMovement(Request $request): void
    {
        $materialId = (int)$request->input('material_id');
        $type = trim((string)$request->input('movement_type'));
        $qty = (float)$request->input('quantity', 0);
        $reason = trim((string)$request->input('reason'));

        if (!$materialId || $qty <= 0) {
            Session::setFlash('error', 'Artigo e quantidade válidos são obrigatórios.');
            Response::back();
        }

        try {
            StockService::registerMovement(
                $materialId,
                $type,
                $qty,
                null,
                Auth::id(),
                $reason ?: 'Movimento manual de stock no armazém'
            );

            Session::setFlash('success', 'Movimento de stock registado com sucesso.');
            Response::redirect('/operations/stock');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            Response::back();
        }
    }
}
