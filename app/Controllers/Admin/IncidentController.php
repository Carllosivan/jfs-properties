<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\SlaService;
use PDO;

class IncidentController
{
    public function index(Request $request): void
    {
        $db = Database::getConnection();
        $status = $request->query('status');
        $priority = $request->query('priority');
        $propertyId = $request->query('property_id');
        $search = trim((string)$request->query('q'));

        $query = "
            SELECT i.*, 
                   p.name as property_name, 
                   c.name as category_name, c.color as category_color,
                   u.reference as unit_ref,
                   t.name as technician_name,
                   o.name as occupant_name
            FROM incidents i
            JOIN properties p ON i.property_id = p.id
            JOIN incident_categories c ON i.category_id = c.id
            LEFT JOIN units u ON i.unit_id = u.id
            LEFT JOIN technicians t ON i.assigned_technician_id = t.id
            LEFT JOIN occupants o ON i.occupant_id = o.id
            WHERE 1=1
        ";
        $params = [];

        if ($status) {
            if ($status === 'critical') {
                $query .= " AND i.priority IN ('critical', 'urgent') AND i.status NOT IN ('resolved', 'closed', 'cancelled')";
            } else {
                $query .= " AND i.status = :status";
                $params[':status'] = $status;
            }
        }
        if ($priority) {
            $query .= " AND i.priority = :priority";
            $params[':priority'] = $priority;
        }
        if ($propertyId) {
            $query .= " AND i.property_id = :prop_id";
            $params[':prop_id'] = (int)$propertyId;
        }
        if ($search) {
            $query .= " AND (i.title LIKE :search OR i.reference LIKE :search OR p.name LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $query .= " ORDER BY CASE WHEN i.priority = 'critical' THEN 1 WHEN i.priority = 'urgent' THEN 2 ELSE 3 END, i.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $incidents = $stmt->fetchAll();

        // Counts for tabs
        $counts = [
            'all' => (int)$db->query("SELECT count(*) FROM incidents")->fetchColumn(),
            'new' => (int)$db->query("SELECT count(*) FROM incidents WHERE status = 'new'")->fetchColumn(),
            'critical' => (int)$db->query("SELECT count(*) FROM incidents WHERE priority IN ('critical', 'urgent') AND status NOT IN ('resolved', 'closed', 'cancelled')")->fetchColumn(),
            'in_progress' => (int)$db->query("SELECT count(*) FROM incidents WHERE status IN ('in_progress', 'assigned')")->fetchColumn(),
            'resolved' => (int)$db->query("SELECT count(*) FROM incidents WHERE status = 'resolved'")->fetchColumn(),
            'closed' => (int)$db->query("SELECT count(*) FROM incidents WHERE status = 'closed'")->fetchColumn(),
        ];

        $properties = $db->query("SELECT id, name FROM properties ORDER BY name ASC")->fetchAll();

        View::render('admin/incidents/index', [
            'title' => 'Gestão de Incidentes - JFS Properties',
            'incidents' => $incidents,
            'counts' => $counts,
            'currentStatus' => $status,
            'properties' => $properties,
            'filters' => [
                'status' => $status,
                'priority' => $priority,
                'property_id' => $propertyId,
                'q' => $search
            ]
        ]);
    }

    public function create(Request $request): void
    {
        $db = Database::getConnection();
        $properties = $db->query("SELECT id, name FROM properties ORDER BY name ASC")->fetchAll();
        $categories = $db->query("SELECT id, name FROM incident_categories ORDER BY name ASC")->fetchAll();
        $technicians = $db->query("SELECT id, name, specialization FROM technicians WHERE status != 'inactive'")->fetchAll();

        View::render('admin/incidents/create', [
            'title' => 'Registar Incidente - JFS Properties',
            'properties' => $properties,
            'categories' => $categories,
            'technicians' => $technicians
        ]);
    }

    public function store(Request $request): void
    {
        $db = Database::getConnection();

        $title = trim((string)$request->input('title'));
        $description = trim((string)$request->input('description'));
        $categoryId = (int)$request->input('category_id');
        $propertyId = (int)$request->input('property_id');
        $unitId = $request->input('unit_id') ? (int)$request->input('unit_id') : null;
        $priority = trim((string)$request->input('priority', 'normal'));
        $severity = trim((string)$request->input('severity', 'medium'));
        $technicianId = $request->input('technician_id') ? (int)$request->input('technician_id') : null;

        if (empty($title) || empty($description) || !$categoryId || !$propertyId) {
            Session::setFlash('error', 'Por favor, preencha todos os campos obrigatórios.');
            Response::back();
        }

        // Generate Reference INC-YYYY-NNNN
        $year = date('Y');
        $count = (int)$db->query("SELECT count(*) FROM incidents")->fetchColumn() + 1;
        $reference = sprintf("INC-%s-%04d", $year, $count);

        // Calculate SLAs
        $deadlines = SlaService::calculateDeadlines($priority);

        $initialStatus = $technicianId ? 'assigned' : 'new';

        Database::beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO incidents (
                    reference, title, description, category_id, priority, severity, origin, status,
                    property_id, unit_id, assigned_technician_id, sla_response_deadline, sla_resolution_deadline,
                    reported_by_id, created_at, updated_at
                ) VALUES (
                    :ref, :title, :desc, :cat_id, :priority, :severity, 'admin', :status,
                    :prop_id, :unit_id, :tech_id, :resp_deadline, :resol_deadline,
                    :user_id, datetime('now'), datetime('now')
                )
            ");
            $stmt->execute([
                ':ref' => $reference,
                ':title' => $title,
                ':desc' => $description,
                ':cat_id' => $categoryId,
                ':priority' => $priority,
                ':severity' => $severity,
                ':status' => $initialStatus,
                ':prop_id' => $propertyId,
                ':unit_id' => $unitId,
                ':tech_id' => $technicianId,
                ':resp_deadline' => $deadlines['response_deadline'],
                ':resol_deadline' => $deadlines['resolution_deadline'],
                ':user_id' => Auth::id()
            ]);
            $incidentId = (int)$db->lastInsertId();

            // Status history
            $hStmt = $db->prepare("
                INSERT INTO incident_status_history (incident_id, user_id, previous_status, new_status, comment, created_at)
                VALUES (:inc_id, :user_id, NULL, :status, 'Incidente criado via backoffice', datetime('now'))
            ");
            $hStmt->execute([
                ':inc_id' => $incidentId,
                ':user_id' => Auth::id(),
                ':status' => $initialStatus
            ]);

            // If critical, trigger alert notification
            if (in_array($priority, ['critical', 'urgent'], true)) {
                $db->prepare("
                    INSERT INTO notifications (user_id, title, message, type, link)
                    SELECT id, 'Alerta: Incidente Crítico Registado', :msg, 'danger', :link
                    FROM users WHERE status = 'active' AND id IN (SELECT user_id FROM user_roles WHERE role_id IN (1, 2, 4, 5))
                ")->execute([
                    ':msg' => "Incidente {$reference} ({$title}) com prioridade {$priority} foi criado.",
                    ':link' => "/incidents/{$incidentId}"
                ]);
            }

            Database::commit();
            Auth::audit('create', 'incidents', 'incidents', $incidentId, "Incidente {$reference} criado.");
            Session::setFlash('success', "Incidente {$reference} registado com sucesso.");
            Response::redirect("/incidents/{$incidentId}");
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', "Erro ao registar incidente: " . $e->getMessage());
            Response::back();
        }
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT i.*, 
                   p.name as property_name, p.address as property_address,
                   b.name as building_name,
                   f.name as floor_name,
                   u.reference as unit_ref, u.name as unit_name,
                   c.name as category_name, c.color as category_color,
                   t.name as technician_name, t.phone as technician_phone,
                   o.name as occupant_name, o.phone as occupant_phone, o.email as occupant_email,
                   a.name as asset_name, a.code as asset_code
            FROM incidents i
            JOIN properties p ON i.property_id = p.id
            JOIN incident_categories c ON i.category_id = c.id
            LEFT JOIN buildings b ON i.building_id = b.id
            LEFT JOIN floors f ON i.floor_id = f.id
            LEFT JOIN units u ON i.unit_id = u.id
            LEFT JOIN technicians t ON i.assigned_technician_id = t.id
            LEFT JOIN occupants o ON i.occupant_id = o.id
            LEFT JOIN assets a ON i.asset_id = a.id
            WHERE i.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $incident = $stmt->fetch();

        if (!$incident) {
            Session::setFlash('error', 'Incidente não encontrado.');
            Response::redirect('/incidents');
        }

        // Fetch comments
        $cStmt = $db->prepare("
            SELECT ic.*, u.name as author_name, r.slug as author_role
            FROM incident_comments ic
            LEFT JOIN users u ON ic.user_id = u.id
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE ic.incident_id = :id
            ORDER BY ic.created_at ASC
        ");
        $cStmt->execute([':id' => $id]);
        $comments = $cStmt->fetchAll();

        // Fetch status history
        $hStmt = $db->prepare("
            SELECT h.*, u.name as user_name
            FROM incident_status_history h
            LEFT JOIN users u ON h.user_id = u.id
            WHERE h.incident_id = :id
            ORDER BY h.created_at DESC
        ");
        $hStmt->execute([':id' => $id]);
        $history = $hStmt->fetchAll();

        // Fetch related Work Orders
        $woStmt = $db->prepare("
            SELECT w.*, t.name as technician_name
            FROM work_orders w
            LEFT JOIN technicians t ON w.technician_id = t.id
            WHERE w.incident_id = :id
            ORDER BY w.created_at DESC
        ");
        $woStmt->execute([':id' => $id]);
        $workOrders = $woStmt->fetchAll();

        $technicians = $db->query("SELECT id, name, specialization FROM technicians WHERE status != 'inactive'")->fetchAll();

        View::render('admin/incidents/show', [
            'title' => "{$incident['reference']} - {$incident['title']} - JFS Properties",
            'incident' => $incident,
            'comments' => $comments,
            'history' => $history,
            'workOrders' => $workOrders,
            'technicians' => $technicians
        ]);
    }

    public function updateStatus(Request $request): void
    {
        $id = (int)$request->param('id');
        $newStatus = trim((string)$request->input('status'));
        $comment = trim((string)$request->input('comment'));

        $validStatuses = [
            'new', 'under_review', 'pending_approval', 'approved', 
            'assigned', 'in_progress', 'waiting_materials', 'waiting_supplier', 
            'resolved', 'validation_pending', 'closed', 'rejected', 'cancelled'
        ];

        if (!in_array($newStatus, $validStatuses, true)) {
            Session::setFlash('error', 'Estado inválido.');
            Response::back();
        }

        $db = Database::getConnection();
        $currStmt = $db->prepare("SELECT status, reference, occupant_id, property_id FROM incidents WHERE id = :id");
        $currStmt->execute([':id' => $id]);
        $incident = $currStmt->fetch();

        if (!$incident) {
            Session::setFlash('error', 'Incidente não encontrado.');
            Response::redirect('/incidents');
        }

        $prevStatus = $incident['status'];

        Database::beginTransaction();
        try {
            $extraUpdates = '';
            if ($newStatus === 'resolved' && empty($incident['resolved_at'])) {
                $extraUpdates .= ", resolved_at = datetime('now') ";
            }
            if ($newStatus === 'closed') {
                $extraUpdates .= ", closed_at = datetime('now') ";
            }

            $updateStmt = $db->prepare("
                UPDATE incidents 
                SET status = :status, updated_at = datetime('now') {$extraUpdates}
                WHERE id = :id
            ");
            $updateStmt->execute([':status' => $newStatus, ':id' => $id]);

            // Record history
            $hStmt = $db->prepare("
                INSERT INTO incident_status_history (incident_id, user_id, previous_status, new_status, comment, created_at)
                VALUES (:inc_id, :user_id, :prev, :new, :comm, datetime('now'))
            ");
            $hStmt->execute([
                ':inc_id' => $id,
                ':user_id' => Auth::id(),
                ':prev' => $prevStatus,
                ':new' => $newStatus,
                ':comm' => $comment ?: "Estado alterado para {$newStatus}"
            ]);

            Database::commit();
            Auth::audit('status_update', 'incidents', 'incidents', $id, "Estado de {$incident['reference']} alterado para {$newStatus}");
            Session::setFlash('success', "Estado do incidente actualizado para '{$newStatus}'.");
            Response::redirect("/incidents/{$id}");
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', "Erro ao actualizar estado: " . $e->getMessage());
            Response::back();
        }
    }

    public function assign(Request $request): void
    {
        $id = (int)$request->param('id');
        $technicianId = (int)$request->input('technician_id');

        $db = Database::getConnection();
        $db->prepare("
            UPDATE incidents 
            SET assigned_technician_id = :tech_id, status = 'assigned', updated_at = datetime('now')
            WHERE id = :id
        ")->execute([':tech_id' => $technicianId, ':id' => $id]);

        $db->prepare("
            INSERT INTO incident_status_history (incident_id, user_id, previous_status, new_status, comment, created_at)
            VALUES (:id, :user_id, 'under_review', 'assigned', 'Técnico atribuído ao incidente', datetime('now'))
        ")->execute([':id' => $id, ':user_id' => Auth::id()]);

        Session::setFlash('success', 'Técnico atribuído com sucesso.');
        Response::redirect("/incidents/{$id}");
    }

    public function addComment(Request $request): void
    {
        $id = (int)$request->param('id');
        $comment = trim((string)$request->input('comment'));
        $isInternal = (int)(bool)$request->input('is_internal', 0);

        if (!empty($comment)) {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO incident_comments (incident_id, user_id, is_internal, comment, created_at)
                VALUES (:inc_id, :user_id, :internal, :comm, datetime('now'))
            ");
            $stmt->execute([
                ':inc_id' => $id,
                ':user_id' => Auth::id(),
                ':internal' => $isInternal,
                ':comm' => $comment
            ]);
            Session::setFlash('success', 'Comentário registado.');
        }

        Response::redirect("/incidents/{$id}");
    }
}
