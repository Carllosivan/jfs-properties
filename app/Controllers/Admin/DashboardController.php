<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\View;
use PDO;

class DashboardController
{
    public function index(Request $request): void
    {
        $db = Database::getConnection();
        $propertyFilter = $request->query('property_id');
        $params = [];
        $propWhere = '';

        if (!empty($propertyFilter)) {
            $propWhere = ' WHERE property_id = :prop_id ';
            $params[':prop_id'] = (int)$propertyFilter;
        }

        // 1. KPI Counts
        $kpi = [];

        // Properties
        $kpi['properties_count'] = (int)$db->query("SELECT count(*) FROM properties WHERE status = 'active'")->fetchColumn();
        $kpi['buildings_count'] = (int)$db->query("SELECT count(*) FROM buildings")->fetchColumn();
        $kpi['units_count'] = (int)$db->query("SELECT count(*) FROM units")->fetchColumn();
        $kpi['occupants_count'] = (int)$db->query("SELECT count(*) FROM occupants WHERE status = 'active'")->fetchColumn();

        // Incidents
        $incQuery = "SELECT 
            count(*) as total,
            sum(CASE WHEN status IN ('new', 'under_review', 'pending_approval') THEN 1 ELSE 0 END) as pending,
            sum(CASE WHEN priority IN ('critical', 'urgent') AND status NOT IN ('resolved', 'closed', 'cancelled') THEN 1 ELSE 0 END) as critical,
            sum(CASE WHEN status IN ('in_progress', 'assigned') THEN 1 ELSE 0 END) as in_progress,
            sum(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved
            FROM incidents" . $propWhere;
        $incStmt = $db->prepare($incQuery);
        $incStmt->execute($params);
        $kpi['incidents'] = $incStmt->fetch();

        // Work Orders
        $woQuery = "SELECT 
            count(*) as total,
            sum(CASE WHEN status IN ('new', 'assigned', 'in_progress', 'scheduled') THEN 1 ELSE 0 END) as open,
            sum(CASE WHEN status IN ('completed', 'validated') THEN 1 ELSE 0 END) as completed
            FROM work_orders" . $propWhere;
        $woStmt = $db->prepare($woQuery);
        $woStmt->execute($params);
        $kpi['work_orders'] = $woStmt->fetch();

        // Assets
        $kpi['assets_count'] = (int)$db->query("SELECT count(*) FROM assets WHERE status = 'operational'")->fetchColumn();

        // Maintenance Tasks
        $maintQuery = "SELECT 
            sum(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
            sum(CASE WHEN status = 'overdue' OR (status = 'scheduled' AND scheduled_date < date('now')) THEN 1 ELSE 0 END) as overdue
            FROM maintenance_tasks" . $propWhere;
        $maintStmt = $db->prepare($maintQuery);
        $maintStmt->execute($params);
        $kpi['maintenance'] = $maintStmt->fetch();

        // Financials (Current Month & Total in MZN)
        $expQuery = "SELECT 
            sum(CASE WHEN strftime('%Y-%m', expense_date) = strftime('%Y-%m', 'now') THEN amount ELSE 0 END) as month_expenses,
            sum(amount) as total_expenses
            FROM expenses" . $propWhere;
        $expStmt = $db->prepare($expQuery);
        $expStmt->execute($params);
        $kpi['financial'] = $expStmt->fetch();

        // Properties dropdown list for filtering
        $properties = $db->query("SELECT id, name, reference FROM properties ORDER BY name ASC")->fetchAll();

        // 2. Chart Data: Incidents by Priority
        $prioStmt = $db->prepare("
            SELECT priority, count(*) as count 
            FROM incidents " . $propWhere . " 
            GROUP BY priority
        ");
        $prioStmt->execute($params);
        $incidentsByPriority = $prioStmt->fetchAll();

        // Chart Data: Incidents by Category
        $catStmt = $db->prepare("
            SELECT ic.name, ic.color, count(i.id) as count 
            FROM incident_categories ic
            LEFT JOIN incidents i ON ic.id = i.category_id " . ($propertyFilter ? "AND i.property_id = :prop_id" : "") . "
            GROUP BY ic.id
        ");
        if ($propertyFilter) {
            $catStmt->execute([':prop_id' => (int)$propertyFilter]);
        } else {
            $catStmt->execute();
        }
        $incidentsByCategory = $catStmt->fetchAll();

        // Chart Data: Monthly Costs
        $costStmt = $db->prepare("
            SELECT strftime('%Y-%m', expense_date) as month, sum(amount) as total
            FROM expenses " . $propWhere . "
            GROUP BY strftime('%Y-%m', expense_date)
            ORDER BY month DESC LIMIT 6
        ");
        $costStmt->execute($params);
        $monthlyCosts = array_reverse($costStmt->fetchAll());

        // 3. Recent Activity Timeline
        $recentIncidents = $db->query("
            SELECT i.*, p.name as property_name, c.name as category_name
            FROM incidents i
            JOIN properties p ON i.property_id = p.id
            JOIN incident_categories c ON i.category_id = c.id
            ORDER BY i.created_at DESC LIMIT 5
        ")->fetchAll();

        $recentWorkOrders = $db->query("
            SELECT w.*, p.name as property_name, t.name as technician_name
            FROM work_orders w
            JOIN properties p ON w.property_id = p.id
            LEFT JOIN technicians t ON w.technician_id = t.id
            ORDER BY w.created_at DESC LIMIT 5
        ")->fetchAll();

        View::render('admin/dashboard/index', [
            'title' => 'Dashboard Operacional - JFS Properties',
            'kpi' => $kpi,
            'properties' => $properties,
            'selectedProperty' => $propertyFilter,
            'incidentsByPriority' => $incidentsByPriority,
            'incidentsByCategory' => $incidentsByCategory,
            'monthlyCosts' => $monthlyCosts,
            'recentIncidents' => $recentIncidents,
            'recentWorkOrders' => $recentWorkOrders,
        ]);
    }
}
