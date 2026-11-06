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

class WorkOrderController
{
    public function index(Request $request): void
    {
        $db = Database::getConnection();
        $status = $request->query('status');
        $propertyId = $request->query('property_id');
        $priority = $request->query('priority');
        $search = trim((string)$request->query('q'));

        $query = "
            SELECT w.*, 
                   p.name as property_name,
                   t.name as technician_name,
                   i.reference as incident_ref,
                   a.name as asset_name
            FROM work_orders w
            JOIN properties p ON w.property_id = p.id
            LEFT JOIN technicians t ON w.technician_id = t.id
            LEFT JOIN incidents i ON w.incident_id = i.id
            LEFT JOIN assets a ON w.asset_id = a.id
            WHERE 1=1
        ";
        $params = [];

        if ($status) {
            $query .= " AND w.status = :status";
            $params[':status'] = $status;
        }
        if ($propertyId) {
            $query .= " AND w.property_id = :prop_id";
            $params[':prop_id'] = (int)$propertyId;
        }
        if ($priority) {
            $query .= " AND w.priority = :priority";
            $params[':priority'] = $priority;
        }
        if ($search) {
            $query .= " AND (w.title LIKE :search OR w.reference LIKE :search OR p.name LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $query .= " ORDER BY w.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $workOrders = $stmt->fetchAll();

        $counts = [
            'all' => (int)$db->query("SELECT count(*) FROM work_orders")->fetchColumn(),
            'pending_approval' => (int)$db->query("SELECT count(*) FROM work_orders WHERE status = 'pending_approval'")->fetchColumn(),
            'assigned' => (int)$db->query("SELECT count(*) FROM work_orders WHERE status = 'assigned'")->fetchColumn(),
            'in_progress' => (int)$db->query("SELECT count(*) FROM work_orders WHERE status = 'in_progress'")->fetchColumn(),
            'in_validation' => (int)$db->query("SELECT count(*) FROM work_orders WHERE status = 'in_validation'")->fetchColumn(),
            'completed' => (int)$db->query("SELECT count(*) FROM work_orders WHERE status IN ('completed', 'validated')")->fetchColumn(),
        ];

        $properties = $db->query("SELECT id, name FROM properties ORDER BY name ASC")->fetchAll();

        View::render('admin/work_orders/index', [
            'title' => 'Ordens de Serviço - JFS Properties',
            'workOrders' => $workOrders,
            'counts' => $counts,
            'currentStatus' => $status,
            'properties' => $properties,
            'filters' => [
                'status' => $status,
                'property_id' => $propertyId,
                'priority' => $priority,
                'q' => $search
            ]
        ]);
    }

    public function create(Request $request): void
    {
        $db = Database::getConnection();
        $incidentId = $request->query('incident_id');
        $incident = null;

        if ($incidentId) {
            $iStmt = $db->prepare("SELECT * FROM incidents WHERE id = :id");
            $iStmt->execute([':id' => (int)$incidentId]);
            $incident = $iStmt->fetch();
        }

        $properties = $db->query("SELECT id, name FROM properties ORDER BY name ASC")->fetchAll();
        $technicians = $db->query("SELECT id, name, specialization FROM technicians WHERE status != 'inactive'")->fetchAll();
        $assets = $db->query("SELECT id, name, code, property_id FROM assets ORDER BY name ASC")->fetchAll();

        View::render('admin/work_orders/create', [
            'title' => 'Nova Ordem de Serviço - JFS Properties',
            'incident' => $incident,
            'properties' => $properties,
            'technicians' => $technicians,
            'assets' => $assets
        ]);
    }

    public function store(Request $request): void
    {
        $db = Database::getConnection();

        $title = trim((string)$request->input('title'));
        $description = trim((string)$request->input('description'));
        $origin = trim((string)$request->input('origin', 'direct'));
        $incidentId = $request->input('incident_id') ? (int)$request->input('incident_id') : null;
        $propertyId = (int)$request->input('property_id');
        $unitId = $request->input('unit_id') ? (int)$request->input('unit_id') : null;
        $assetId = $request->input('asset_id') ? (int)$request->input('asset_id') : null;
        $technicianId = $request->input('technician_id') ? (int)$request->input('technician_id') : null;
        $priority = trim((string)$request->input('priority', 'normal'));
        $scheduledDate = $request->input('scheduled_date') ?: date('Y-m-d');
        $estimatedCost = (float)$request->input('estimated_cost', 0);

        if (empty($title) || !$propertyId) {
            Session::setFlash('error', 'Título e Imóvel são obrigatórios.');
            Response::back();
        }

        // Generate Reference OS-YYYY-NNNN
        $year = date('Y');
        $count = (int)$db->query("SELECT count(*) FROM work_orders")->fetchColumn() + 1;
        $ref = sprintf("OS-%s-%04d", $year, $count);

        $initialStatus = $technicianId ? 'assigned' : 'new';

        Database::beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO work_orders (
                    reference, title, description, origin, incident_id, property_id, unit_id, asset_id,
                    technician_id, priority, status, scheduled_date, estimated_cost, created_at, updated_at
                ) VALUES (
                    :ref, :title, :desc, :origin, :inc_id, :prop_id, :unit_id, :asset_id,
                    :tech_id, :priority, :status, :sched_date, :est_cost, datetime('now'), datetime('now')
                )
            ");
            $stmt->execute([
                ':ref' => $ref,
                ':title' => $title,
                ':desc' => $description,
                ':origin' => $origin,
                ':inc_id' => $incidentId,
                ':prop_id' => $propertyId,
                ':unit_id' => $unitId,
                ':asset_id' => $assetId,
                ':tech_id' => $technicianId,
                ':priority' => $priority,
                ':status' => $initialStatus,
                ':sched_date' => $scheduledDate,
                ':est_cost' => $estimatedCost
            ]);
            $woId = (int)$db->lastInsertId();

            // Link status to incident if applicable
            if ($incidentId) {
                $db->prepare("UPDATE incidents SET status = 'in_progress', updated_at = datetime('now') WHERE id = :id")
                   ->execute([':id' => $incidentId]);
            }

            Database::commit();
            Auth::audit('create', 'work_orders', 'work_orders', $woId, "Ordem de Serviço {$ref} criada.");
            Session::setFlash('success', "Ordem de Serviço {$ref} criada com sucesso.");
            Response::redirect("/work-orders/{$woId}");
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', "Erro ao criar Ordem de Serviço: " . $e->getMessage());
            Response::back();
        }
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT w.*, 
                   p.name as property_name, p.address as property_address,
                   u.reference as unit_ref, u.name as unit_name,
                   t.name as technician_name, t.phone as technician_phone, t.hourly_rate,
                   i.reference as incident_ref, i.title as incident_title,
                   a.name as asset_name, a.code as asset_code
            FROM work_orders w
            JOIN properties p ON w.property_id = p.id
            LEFT JOIN units u ON w.unit_id = u.id
            LEFT JOIN technicians t ON w.technician_id = t.id
            LEFT JOIN incidents i ON w.incident_id = i.id
            LEFT JOIN assets a ON w.asset_id = a.id
            WHERE w.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $workOrder = $stmt->fetch();

        if (!$workOrder) {
            Session::setFlash('error', 'Ordem de Serviço não encontrada.');
            Response::redirect('/work-orders');
        }

        // Materials consumed
        $mStmt = $db->prepare("
            SELECT wom.*, m.name as material_name, m.code as material_code, m.unit_of_measure
            FROM work_order_materials wom
            JOIN materials m ON wom.material_id = m.id
            WHERE wom.work_order_id = :id
        ");
        $mStmt->execute([':id' => $id]);
        $materials = $mStmt->fetchAll();

        // Available materials list for modal/form
        $availableMaterials = $db->query("
            SELECT id, code, name, unit_of_measure, quantity_available, unit_cost 
            FROM materials WHERE quantity_available > 0 AND status = 'active'
            ORDER BY name ASC
        ")->fetchAll();

        // Activities performed
        $aStmt = $db->prepare("
            SELECT woa.*, t.name as technician_name 
            FROM work_order_activities woa
            LEFT JOIN technicians t ON woa.technician_id = t.id
            WHERE woa.work_order_id = :id
            ORDER BY woa.date_performed DESC
        ");
        $aStmt->execute([':id' => $id]);
        $activities = $aStmt->fetchAll();

        // Status history
        $hStmt = $db->prepare("
            SELECT h.*, u.name as user_name 
            FROM work_order_status_history h
            LEFT JOIN users u ON h.user_id = u.id
            WHERE h.work_order_id = :id
            ORDER BY h.created_at DESC
        ");
        $hStmt->execute([':id' => $id]);
        $history = $hStmt->fetchAll();

        $technicians = $db->query("SELECT id, name, specialization FROM technicians WHERE status != 'inactive'")->fetchAll();

        View::render('admin/work_orders/show', [
            'title' => "{$workOrder['reference']} - {$workOrder['title']} - JFS Properties",
            'workOrder' => $workOrder,
            'materials' => $materials,
            'availableMaterials' => $availableMaterials,
            'activities' => $activities,
            'history' => $history,
            'technicians' => $technicians
        ]);
    }

    public function recordIntervention(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getConnection();

        $diagnosis = trim((string)$request->input('diagnosis'));
        $actionsTaken = trim((string)$request->input('actions_taken'));
        $hours = (float)$request->input('hours_spent', 0);
        $technicianNotes = trim((string)$request->input('technician_notes'));
        $status = trim((string)$request->input('status', 'in_validation'));

        // Fetch work order and technician hourly rate
        $woStmt = $db->prepare("
            SELECT w.*, t.hourly_rate 
            FROM work_orders w
            LEFT JOIN technicians t ON w.technician_id = t.id
            WHERE w.id = :id
        ");
        $woStmt->execute([':id' => $id]);
        $wo = $woStmt->fetch();

        if (!$wo) {
            Session::setFlash('error', 'Ordem de Serviço não encontrada.');
            Response::redirect('/work-orders');
        }

        $laborRate = (float)($wo['hourly_rate'] ?: 400);
        $laborCost = $hours * $laborRate;
        $materialCost = (float)$db->query("SELECT sum(total_cost) FROM work_order_materials WHERE work_order_id = {$id}")->fetchColumn() ?: 0;
        $totalCost = $laborCost + $materialCost;

        Database::beginTransaction();
        try {
            $stmt = $db->prepare("
                UPDATE work_orders 
                SET diagnosis = :diag,
                    actions_taken = :actions,
                    technician_notes = :notes,
                    total_hours = total_hours + :hrs,
                    actual_labor_cost = :labor_cost,
                    actual_material_cost = :mat_cost,
                    actual_total_cost = :total_cost,
                    status = :status,
                    completion_time = datetime('now'),
                    updated_at = datetime('now')
                WHERE id = :id
            ");
            $stmt->execute([
                ':diag' => $diagnosis,
                ':actions' => $actionsTaken,
                ':notes' => $technicianNotes,
                ':hrs' => $hours,
                ':labor_cost' => $laborCost,
                ':mat_cost' => $materialCost,
                ':total_cost' => $totalCost,
                ':status' => $status,
                ':id' => $id
            ]);

            // Add activity row
            if ($hours > 0) {
                $db->prepare("
                    INSERT INTO work_order_activities (work_order_id, technician_id, description, hours_spent, date_performed, created_at)
                    VALUES (:wo_id, :tech_id, :desc, :hrs, date('now'), datetime('now'))
                ")->execute([
                    ':wo_id' => $id,
                    ':tech_id' => $wo['technician_id'],
                    ':desc' => $actionsTaken ?: 'Intervenção técnica realizada',
                    ':hrs' => $hours
                ]);
            }

            // Status history
            $db->prepare("
                INSERT INTO work_order_status_history (work_order_id, user_id, previous_status, new_status, notes, created_at)
                VALUES (:wo_id, :user_id, :prev, :new, :notes, datetime('now'))
            ")->execute([
                ':wo_id' => $id,
                ':user_id' => Auth::id(),
                ':prev' => $wo['status'],
                ':new' => $status,
                ':notes' => 'Registo de intervenção técnica pelo técnico'
            ]);

            // If validated or completed and linked to incident, resolve incident
            if ($status === 'validated' && $wo['incident_id']) {
                $db->prepare("
                    UPDATE incidents 
                    SET status = 'resolved', resolved_at = datetime('now'), updated_at = datetime('now')
                    WHERE id = :id
                ")->execute([':id' => $wo['incident_id']]);
            }

            Database::commit();
            Session::setFlash('success', 'Intervenção técnica registada com sucesso.');
            Response::redirect("/work-orders/{$id}");
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', "Erro ao registar intervenção: " . $e->getMessage());
            Response::back();
        }
    }

    public function addMaterial(Request $request): void
    {
        $id = (int)$request->param('id');
        $materialId = (int)$request->input('material_id');
        $quantity = (float)$request->input('quantity', 0);

        if (!$materialId || $quantity <= 0) {
            Session::setFlash('error', 'Seleccione o material e indique uma quantidade válida.');
            Response::back();
        }

        $db = Database::getConnection();

        try {
            // Deduct stock using transactional StockService
            StockService::registerMovement(
                $materialId,
                'out',
                $quantity,
                $id,
                Auth::id(),
                "Aplicação directa na Ordem de Serviço #{$id}"
            );

            // Fetch unit cost
            $mStmt = $db->prepare("SELECT unit_cost FROM materials WHERE id = :id");
            $mStmt->execute([':id' => $materialId]);
            $unitCost = (float)$mStmt->fetchColumn();
            $totalCost = $quantity * $unitCost;

            // Link material to work order
            $db->prepare("
                INSERT INTO work_order_materials (work_order_id, material_id, quantity_used, unit_cost, total_cost, created_at)
                VALUES (:wo_id, :mat_id, :qty, :unit_cost, :total_cost, datetime('now'))
            ")->execute([
                ':wo_id' => $id,
                ':mat_id' => $materialId,
                ':qty' => $quantity,
                ':unit_cost' => $unitCost,
                ':total_cost' => $totalCost
            ]);

            // Recalculate work order material cost
            $db->prepare("
                UPDATE work_orders 
                SET actual_material_cost = (SELECT sum(total_cost) FROM work_order_materials WHERE work_order_id = :id),
                    actual_total_cost = actual_labor_cost + (SELECT sum(total_cost) FROM work_order_materials WHERE work_order_id = :id),
                    updated_at = datetime('now')
                WHERE id = :id
            ")->execute([':id' => $id]);

            Session::setFlash('success', 'Material consumido e deduzido do stock com sucesso.');
            Response::redirect("/work-orders/{$id}");
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            Response::back();
        }
    }

    public function report(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT w.*, 
                   p.name as property_name, p.address as property_address, p.reference as prop_ref,
                   u.reference as unit_ref, u.name as unit_name,
                   t.name as technician_name, t.phone as technician_phone, t.specialization,
                   i.reference as incident_ref, i.title as incident_title,
                   a.name as asset_name, a.code as asset_code
            FROM work_orders w
            JOIN properties p ON w.property_id = p.id
            LEFT JOIN units u ON w.unit_id = u.id
            LEFT JOIN technicians t ON w.technician_id = t.id
            LEFT JOIN incidents i ON w.incident_id = i.id
            LEFT JOIN assets a ON w.asset_id = a.id
            WHERE w.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $wo = $stmt->fetch();

        if (!$wo) {
            Session::setFlash('error', 'Ordem de serviço não encontrada.');
            Response::redirect('/work-orders');
        }

        $materials = $db->query("
            SELECT wom.*, m.name as material_name, m.code as material_code, m.unit_of_measure
            FROM work_order_materials wom
            JOIN materials m ON wom.material_id = m.id
            WHERE wom.work_order_id = {$id}
        ")->fetchAll();

        $activities = $db->query("
            SELECT * FROM work_order_activities WHERE work_order_id = {$id} ORDER BY date_performed ASC
        ")->fetchAll();

        // Print-friendly view
        View::render('admin/work_orders/report', [
            'title' => "Relatório Técnico - {$wo['reference']}",
            'wo' => $wo,
            'materials' => $materials,
            'activities' => $activities
        ], 'layouts/print');
    }
}
