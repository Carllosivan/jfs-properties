<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use PDO;

class ReportController
{
    public function index(Request $request): void
    {
        $db = Database::getConnection();
        $type = $request->query('report_type', 'incidents');
        $propertyId = $request->query('property_id');
        $startDate = $request->query('start_date') ?: date('Y-m-01');
        $endDate = $request->query('end_date') ?: date('Y-m-d');
        $export = $request->query('export');

        $data = [];
        $columns = [];

        if ($type === 'incidents') {
            $query = "
                SELECT i.reference, i.title, c.name as category, i.priority, i.status, 
                       p.name as property, t.name as technician, i.created_at, i.resolved_at
                FROM incidents i
                JOIN properties p ON i.property_id = p.id
                JOIN incident_categories c ON i.category_id = c.id
                LEFT JOIN technicians t ON i.assigned_technician_id = t.id
                WHERE date(i.created_at) BETWEEN :start AND :end
            ";
            $params = [':start' => $startDate, ':end' => $endDate];
            if ($propertyId) {
                $query .= " AND i.property_id = :prop_id";
                $params[':prop_id'] = (int)$propertyId;
            }
            $query .= " ORDER BY i.created_at DESC";
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll();
            $columns = ['Referência', 'Título', 'Categoria', 'Prioridade', 'Estado', 'Imóvel', 'Técnico', 'Data Criação', 'Data Resolução'];
        } elseif ($type === 'work_orders') {
            $query = "
                SELECT w.reference, w.title, w.origin, w.priority, w.status,
                       p.name as property, t.name as technician, w.actual_total_cost as total_cost, w.created_at
                FROM work_orders w
                JOIN properties p ON w.property_id = p.id
                LEFT JOIN technicians t ON w.technician_id = t.id
                WHERE date(w.created_at) BETWEEN :start AND :end
            ";
            $params = [':start' => $startDate, ':end' => $endDate];
            if ($propertyId) {
                $query .= " AND w.property_id = :prop_id";
                $params[':prop_id'] = (int)$propertyId;
            }
            $query .= " ORDER BY w.created_at DESC";
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll();
            $columns = ['Referência', 'Título', 'Origem', 'Prioridade', 'Estado', 'Imóvel', 'Técnico', 'Custo Total (MZN)', 'Data'];
        } elseif ($type === 'expenses') {
            $query = "
                SELECT e.reference, e.description, c.name as category, e.amount, e.status,
                       p.name as property, s.name as supplier, e.expense_date
                FROM expenses e
                JOIN properties p ON e.property_id = p.id
                JOIN expense_categories c ON e.category_id = c.id
                LEFT JOIN suppliers s ON e.supplier_id = s.id
                WHERE e.expense_date BETWEEN :start AND :end
            ";
            $params = [':start' => $startDate, ':end' => $endDate];
            if ($propertyId) {
                $query .= " AND e.property_id = :prop_id";
                $params[':prop_id'] = (int)$propertyId;
            }
            $query .= " ORDER BY e.expense_date DESC";
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll();
            $columns = ['Referência', 'Descrição', 'Categoria', 'Valor (MZN)', 'Estado', 'Imóvel', 'Fornecedor', 'Data'];
        }

        // CSV Export handling
        if ($export === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="relatorio_' . $type . '_' . date('Ymd_His') . '.csv"');
            
            $output = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, $columns, ';');

            foreach ($data as $row) {
                fputcsv($output, array_values($row), ';');
            }
            fclose($output);
            exit;
        }

        $properties = $db->query("SELECT id, name FROM properties ORDER BY name ASC")->fetchAll();

        View::render('admin/reports/index', [
            'title' => 'Relatórios Operacionais e Financeiros - JFS Properties',
            'type' => $type,
            'properties' => $properties,
            'data' => $data,
            'columns' => $columns,
            'filters' => [
                'report_type' => $type,
                'property_id' => $propertyId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }
}
