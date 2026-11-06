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

class InspectionController
{
    public function index(Request $request): void
    {
        $db = Database::getConnection();

        $inspections = $db->query("
            SELECT i.*, 
                   it.title as template_title, it.type as template_type,
                   p.name as property_name,
                   u.name as inspector_name
            FROM inspections i
            JOIN inspection_templates it ON i.template_id = it.id
            JOIN properties p ON i.property_id = p.id
            LEFT JOIN users u ON i.inspector_id = u.id
            ORDER BY i.inspection_date DESC
        ")->fetchAll();

        $templates = $db->query("
            SELECT it.*, count(iti.id) as items_count 
            FROM inspection_templates it
            LEFT JOIN inspection_template_items iti ON it.id = iti.template_id
            GROUP BY it.id
        ")->fetchAll();

        View::render('admin/inspections/index', [
            'title' => 'Inspecções e Checklists Técnicas - JFS Properties',
            'inspections' => $inspections,
            'templates' => $templates
        ]);
    }

    public function create(Request $request): void
    {
        $db = Database::getConnection();
        $templates = $db->query("SELECT * FROM inspection_templates ORDER BY title ASC")->fetchAll();
        $properties = $db->query("SELECT id, name FROM properties ORDER BY name ASC")->fetchAll();

        View::render('admin/inspections/create', [
            'title' => 'Nova Inspecção Técnica - JFS Properties',
            'templates' => $templates,
            'properties' => $properties
        ]);
    }

    public function store(Request $request): void
    {
        $db = Database::getConnection();

        $templateId = (int)$request->input('template_id');
        $propertyId = (int)$request->input('property_id');
        $date = $request->input('inspection_date') ?: date('Y-m-d');

        if (!$templateId || !$propertyId) {
            Session::setFlash('error', 'Seleccione o Modelo e o Imóvel.');
            Response::back();
        }

        $year = date('Y');
        $count = (int)$db->query("SELECT count(*) FROM inspections")->fetchColumn() + 1;
        $ref = sprintf("INSP-%s-%03d", $year, $count);

        $stmt = $db->prepare("
            INSERT INTO inspections (reference, template_id, property_id, inspector_id, inspection_date, status, created_at)
            VALUES (:ref, :tmpl_id, :prop_id, :inspector_id, :date, 'in_progress', datetime('now'))
        ");
        $stmt->execute([
            ':ref' => $ref,
            ':tmpl_id' => $templateId,
            ':prop_id' => $propertyId,
            ':inspector_id' => Auth::id(),
            ':date' => $date
        ]);

        $inspId = (int)$db->lastInsertId();
        Session::setFlash('success', "Inspecção {$ref} iniciada. Preencha os itens do checklist.");
        Response::redirect("/inspections/{$inspId}/execute");
    }

    public function execute(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT i.*, it.title as template_title, p.name as property_name 
            FROM inspections i
            JOIN inspection_templates it ON i.template_id = it.id
            JOIN properties p ON i.property_id = p.id
            WHERE i.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $inspection = $stmt->fetch();

        if (!$inspection) {
            Session::setFlash('error', 'Inspecção não encontrada.');
            Response::redirect('/inspections');
        }

        // Fetch checklist items
        $itemsStmt = $db->prepare("
            SELECT iti.*, ir.result, ir.notes as result_notes
            FROM inspection_template_items iti
            LEFT JOIN inspection_results ir ON iti.id = ir.item_id AND ir.inspection_id = :insp_id
            WHERE iti.template_id = :tmpl_id
            ORDER BY iti.sort_order ASC, iti.id ASC
        ");
        $itemsStmt->execute([
            ':insp_id' => $id,
            ':tmpl_id' => $inspection['template_id']
        ]);
        $items = $itemsStmt->fetchAll();

        View::render('admin/inspections/execute', [
            'title' => "Executar Inspecção {$inspection['reference']} - JFS Properties",
            'inspection' => $inspection,
            'items' => $items
        ]);
    }

    public function submitResults(Request $request): void
    {
        $id = (int)$request->param('id');
        $db = Database::getConnection();
        $results = (array)$request->input('results', []);
        $notes = (array)$request->input('notes', []);
        $summary = trim((string)$request->input('overall_summary'));

        $stmt = $db->prepare("SELECT * FROM inspections WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $inspection = $stmt->fetch();

        if (!$inspection) {
            Session::setFlash('error', 'Inspecção não encontrada.');
            Response::redirect('/inspections');
        }

        $hasNonCompliant = false;

        Database::beginTransaction();
        try {
            // Delete previous results if editing
            $db->prepare("DELETE FROM inspection_results WHERE inspection_id = :id")->execute([':id' => $id]);

            $insResStmt = $db->prepare("
                INSERT INTO inspection_results (inspection_id, item_id, result, notes)
                VALUES (:insp_id, :item_id, :result, :notes)
            ");

            foreach ($results as $itemId => $res) {
                $itemNote = $notes[$itemId] ?? '';
                $insResStmt->execute([
                    ':insp_id' => $id,
                    ':item_id' => (int)$itemId,
                    ':result' => $res,
                    ':notes' => $itemNote
                ]);

                if ($res === 'non_compliant') {
                    $hasNonCompliant = true;
                }
            }

            $finalStatus = $hasNonCompliant ? 'issues_found' : 'completed';

            // Auto-generate incident if non-compliant issue was flagged
            if ($hasNonCompliant) {
                $year = date('Y');
                $count = (int)$db->query("SELECT count(*) FROM incidents")->fetchColumn() + 1;
                $incRef = sprintf("INC-%s-%04d", $year, $count);
                $deadlines = SlaService::calculateDeadlines('high');

                $db->prepare("
                    INSERT INTO incidents (
                        reference, title, description, category_id, priority, severity, origin, status,
                        property_id, sla_response_deadline, sla_resolution_deadline, reported_by_id, created_at
                    ) VALUES (
                        :ref, :title, :desc, 6, 'high', 'high', 'inspection', 'new',
                        :prop_id, :resp, :resol, :user_id, datetime('now')
                    )
                ")->execute([
                    ':ref' => $incRef,
                    ':title' => "Não conformidade detectada na Inspecção {$inspection['reference']}",
                    ':desc' => "Inspecção identificou anomalias que requerem intervenção: " . ($summary ?: 'Ver relatório da vistoria'),
                    ':prop_id' => $inspection['property_id'],
                    ':resp' => $deadlines['response_deadline'],
                    ':resol' => $deadlines['resolution_deadline'],
                    ':user_id' => Auth::id()
                ]);
            }

            $db->prepare("
                UPDATE inspections 
                SET status = :status, overall_summary = :summary
                WHERE id = :id
            ")->execute([
                ':status' => $finalStatus,
                ':summary' => $summary,
                ':id' => $id
            ]);

            Database::commit();
            Session::setFlash('success', 'Inspecção concluída com sucesso!' . ($hasNonCompliant ? ' Foi gerado um incidente automaticamente devido a não conformidades encontradas.' : ''));
            Response::redirect('/inspections');
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', "Erro ao gravar inspecção: " . $e->getMessage());
            Response::back();
        }
    }
}
