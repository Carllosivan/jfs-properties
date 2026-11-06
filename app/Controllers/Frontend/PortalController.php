<?php

namespace App\Controllers\Frontend;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\SlaService;
use PDO;

class PortalController
{
    public function dashboard(Request $request): void
    {
        $user = Auth::user();
        $db = Database::getConnection();

        // Find occupant record associated with this user
        $occStmt = $db->prepare("SELECT * FROM occupants WHERE user_id = :uid LIMIT 1");
        $occStmt->execute([':uid' => $user['id']]);
        $occupant = $occStmt->fetch();

        $occupantId = $occupant['id'] ?? 0;

        // Fetch occupant's active units
        $unitStmt = $db->prepare("
            SELECT u.*, p.name as property_name, p.address as property_address,
                   b.name as building_name, f.name as floor_name, po.start_date
            FROM property_occupants po
            JOIN units u ON po.unit_id = u.id
            JOIN properties p ON u.property_id = p.id
            LEFT JOIN buildings b ON u.building_id = b.id
            LEFT JOIN floors f ON u.floor_id = f.id
            WHERE po.occupant_id = :occ_id AND po.status = 'active'
        ");
        $unitStmt->execute([':occ_id' => $occupantId]);
        $units = $unitStmt->fetchAll();

        // Fetch occupant's reported incidents
        $incStmt = $db->prepare("
            SELECT i.*, c.name as category_name, c.color as category_color,
                   u.reference as unit_ref, p.name as property_name
            FROM incidents i
            JOIN properties p ON i.property_id = p.id
            JOIN incident_categories c ON i.category_id = c.id
            LEFT JOIN units u ON i.unit_id = u.id
            WHERE i.occupant_id = :occ_id OR i.reported_by_id = :user_id
            ORDER BY i.created_at DESC
        ");
        $incStmt->execute([
            ':occ_id' => $occupantId,
            ':user_id' => $user['id']
        ]);
        $incidents = $incStmt->fetchAll();

        $categories = $db->query("SELECT id, name FROM incident_categories ORDER BY name ASC")->fetchAll();

        View::render('frontend/portal/dashboard', [
            'title' => 'Portal do Ocupante - JFS Properties',
            'occupant' => $occupant,
            'units' => $units,
            'incidents' => $incidents,
            'categories' => $categories
        ], 'layouts/portal');
    }

    public function submitIncident(Request $request): void
    {
        $user = Auth::user();
        $db = Database::getConnection();

        $occStmt = $db->prepare("SELECT * FROM occupants WHERE user_id = :uid LIMIT 1");
        $occStmt->execute([':uid' => $user['id']]);
        $occupant = $occStmt->fetch();

        $occupantId = $occupant['id'] ?? null;
        $unitId = (int)$request->input('unit_id');
        $categoryId = (int)$request->input('category_id');
        $title = trim((string)$request->input('title'));
        $description = trim((string)$request->input('description'));
        $priority = trim((string)$request->input('priority', 'normal'));

        if (empty($title) || empty($description) || !$categoryId || !$unitId) {
            Session::setFlash('error', 'Por favor, indique a unidade, categoria, assunto e descrição detalhada.');
            Response::redirect('/portal');
        }

        // Get property ID from unit
        $uStmt = $db->prepare("SELECT property_id, building_id, floor_id FROM units WHERE id = :id");
        $uStmt->execute([':id' => $unitId]);
        $unitData = $uStmt->fetch();

        if (!$unitData) {
            Session::setFlash('error', 'Unidade inválida.');
            Response::redirect('/portal');
        }

        $year = date('Y');
        $count = (int)$db->query("SELECT count(*) FROM incidents")->fetchColumn() + 1;
        $reference = sprintf("INC-%s-%04d", $year, $count);

        $deadlines = SlaService::calculateDeadlines($priority);

        Database::beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO incidents (
                    reference, title, description, category_id, priority, severity, origin, status,
                    property_id, building_id, floor_id, unit_id, occupant_id, reported_by_id,
                    sla_response_deadline, sla_resolution_deadline, created_at, updated_at
                ) VALUES (
                    :ref, :title, :desc, :cat_id, :prio, 'medium', 'portal', 'new',
                    :prop_id, :bld_id, :flr_id, :unit_id, :occ_id, :user_id,
                    :resp, :resol, datetime('now'), datetime('now')
                )
            ");
            $stmt->execute([
                ':ref' => $reference,
                ':title' => $title,
                ':desc' => $description,
                ':cat_id' => $categoryId,
                ':prio' => $priority,
                ':prop_id' => $unitData['property_id'],
                ':bld_id' => $unitData['building_id'],
                ':flr_id' => $unitData['floor_id'],
                ':unit_id' => $unitId,
                ':occ_id' => $occupantId,
                ':user_id' => $user['id'],
                ':resp' => $deadlines['response_deadline'],
                ':resol' => $deadlines['resolution_deadline'],
            ]);
            $incidentId = (int)$db->lastInsertId();

            // History
            $db->prepare("
                INSERT INTO incident_status_history (incident_id, user_id, previous_status, new_status, comment, created_at)
                VALUES (:inc_id, :uid, NULL, 'new', 'Submetido pelo ocupante via portal', datetime('now'))
            ")->execute([':inc_id' => $incidentId, ':uid' => $user['id']]);

            // Notify staff
            $db->prepare("
                INSERT INTO notifications (user_id, title, message, type, link)
                SELECT id, 'Novo Incidente Comunicado por Ocupante', :msg, 'info', :link
                FROM users WHERE status = 'active' AND id IN (SELECT user_id FROM user_roles WHERE role_id IN (1, 2, 4))
            ")->execute([
                ':msg' => "Novo incidente {$reference} comunicado para a fracção.",
                ':link' => "/incidents/{$incidentId}"
            ]);

            Database::commit();
            Session::setFlash('success', "Incidente {$reference} comunicado com sucesso. A nossa equipa entrará em contacto brevemente.");
            Response::redirect('/portal');
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', "Erro ao submeter ocorrência: " . $e->getMessage());
            Response::redirect('/portal');
        }
    }

    public function showIncident(Request $request): void
    {
        $id = (int)$request->param('id');
        $user = Auth::user();
        $db = Database::getConnection();

        $occStmt = $db->prepare("SELECT id FROM occupants WHERE user_id = :uid LIMIT 1");
        $occStmt->execute([':uid' => $user['id']]);
        $occupantId = (int)$occStmt->fetchColumn();

        // Enforce occupant security boundary
        $stmt = $db->prepare("
            SELECT i.*, c.name as category_name, p.name as property_name, u.reference as unit_ref
            FROM incidents i
            JOIN properties p ON i.property_id = p.id
            JOIN incident_categories c ON i.category_id = c.id
            LEFT JOIN units u ON i.unit_id = u.id
            WHERE i.id = :id AND (i.occupant_id = :occ_id OR i.reported_by_id = :user_id)
        ");
        $stmt->execute([':id' => $id, ':occ_id' => $occupantId, ':user_id' => $user['id']]);
        $incident = $stmt->fetch();

        if (!$incident) {
            Session::setFlash('error', 'Incidente não encontrado ou acesso restrito.');
            Response::redirect('/portal');
        }

        // Fetch only public comments (is_internal = 0)
        $cStmt = $db->prepare("
            SELECT ic.*, u.name as author_name
            FROM incident_comments ic
            LEFT JOIN users u ON ic.user_id = u.id
            WHERE ic.incident_id = :id AND ic.is_internal = 0
            ORDER BY ic.created_at ASC
        ");
        $cStmt->execute([':id' => $id]);
        $comments = $cStmt->fetchAll();

        // Fetch history timeline
        $hStmt = $db->prepare("
            SELECT * FROM incident_status_history WHERE incident_id = :id ORDER BY created_at ASC
        ");
        $hStmt->execute([':id' => $id]);
        $history = $hStmt->fetchAll();

        View::render('frontend/portal/show_incident', [
            'title' => "Incidente {$incident['reference']} - Portal do Ocupante",
            'incident' => $incident,
            'comments' => $comments,
            'history' => $history
        ], 'layouts/portal');
    }

    public function addComment(Request $request): void
    {
        $id = (int)$request->param('id');
        $comment = trim((string)$request->input('comment'));
        $user = Auth::user();

        if (!empty($comment)) {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO incident_comments (incident_id, user_id, is_internal, comment, created_at)
                VALUES (:inc_id, :user_id, 0, :comm, datetime('now'))
            ");
            $stmt->execute([
                ':inc_id' => $id,
                ':user_id' => $user['id'],
                ':comm' => $comment
            ]);
            Session::setFlash('success', 'Mensagem enviada à equipa técnica.');
        }

        Response::redirect("/portal/incidents/{$id}");
    }
}
