<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\MaintenanceService;
use PDO;

class MaintenanceController
{
    public function index(Request $request): void
    {
        $db = Database::getConnection();
        
        // Auto-check overdue tasks on load
        MaintenanceService::checkOverdueTasks();

        $plans = $db->query("
            SELECT mp.*, 
                   p.name as property_name, 
                   mc.name as category_name,
                   a.name as asset_name,
                   t.name as technician_name
            FROM maintenance_plans mp
            JOIN properties p ON mp.property_id = p.id
            LEFT JOIN maintenance_categories mc ON mp.category_id = mc.id
            LEFT JOIN assets a ON mp.asset_id = a.id
            LEFT JOIN technicians t ON mp.technician_id = t.id
            ORDER BY mp.next_execution_date ASC
        ")->fetchAll();

        $tasks = $db->query("
            SELECT mt.*, 
                   mp.title as plan_title, mp.reference as plan_ref,
                   p.name as property_name,
                   a.name as asset_name
            FROM maintenance_tasks mt
            JOIN maintenance_plans mp ON mt.plan_id = mp.id
            JOIN properties p ON mt.property_id = p.id
            LEFT JOIN assets a ON mt.asset_id = a.id
            ORDER BY mt.scheduled_date ASC
            LIMIT 30
        ")->fetchAll();

        $stats = [
            'total_plans' => (int)$db->query("SELECT count(*) FROM maintenance_plans WHERE status = 'active'")->fetchColumn(),
            'scheduled_tasks' => (int)$db->query("SELECT count(*) FROM maintenance_tasks WHERE status = 'scheduled'")->fetchColumn(),
            'overdue_tasks' => (int)$db->query("SELECT count(*) FROM maintenance_tasks WHERE status = 'overdue'")->fetchColumn(),
            'completed_tasks' => (int)$db->query("SELECT count(*) FROM maintenance_tasks WHERE status = 'completed'")->fetchColumn(),
        ];

        View::render('admin/maintenance/index', [
            'title' => 'Gestão de Manutenção Preventiva - JFS Properties',
            'plans' => $plans,
            'tasks' => $tasks,
            'stats' => $stats
        ]);
    }

    public function create(Request $request): void
    {
        $db = Database::getConnection();
        $properties = $db->query("SELECT id, name FROM properties ORDER BY name ASC")->fetchAll();
        $categories = $db->query("SELECT id, name FROM maintenance_categories ORDER BY name ASC")->fetchAll();
        $assets = $db->query("SELECT id, name, code, property_id FROM assets ORDER BY name ASC")->fetchAll();
        $technicians = $db->query("SELECT id, name, specialization FROM technicians WHERE status != 'inactive'")->fetchAll();

        View::render('admin/maintenance/create', [
            'title' => 'Novo Plano de Manutenção - JFS Properties',
            'properties' => $properties,
            'categories' => $categories,
            'assets' => $assets,
            'technicians' => $technicians
        ]);
    }

    public function store(Request $request): void
    {
        $db = Database::getConnection();

        $title = trim((string)$request->input('title'));
        $description = trim((string)$request->input('description'));
        $type = trim((string)$request->input('type', 'preventive'));
        $categoryId = (int)$request->input('category_id');
        $propertyId = (int)$request->input('property_id');
        $assetId = $request->input('asset_id') ? (int)$request->input('asset_id') : null;
        $technicianId = $request->input('technician_id') ? (int)$request->input('technician_id') : null;
        $frequency = trim((string)$request->input('frequency', 'monthly'));
        $frequencyDays = match ($frequency) {
            'daily' => 1,
            'weekly' => 7,
            'biweekly' => 14,
            'monthly' => 30,
            'quarterly' => 90,
            'semiannual' => 180,
            'annual' => 365,
            default => 30
        };
        $startDate = $request->input('start_date') ?: date('Y-m-d');
        $nextExecution = $request->input('next_execution_date') ?: $startDate;
        $estimatedCost = (float)$request->input('estimated_cost', 0);
        $durationHours = (float)$request->input('estimated_duration_hours', 2.0);

        if (empty($title) || !$propertyId) {
            Session::setFlash('error', 'Título e Imóvel são obrigatórios.');
            Response::back();
        }

        $year = date('Y');
        $count = (int)$db->query("SELECT count(*) FROM maintenance_plans")->fetchColumn() + 1;
        $ref = sprintf("PLN-%s-%03d", $year, $count);

        $stmt = $db->prepare("
            INSERT INTO maintenance_plans (
                reference, title, description, type, category_id, property_id, asset_id, technician_id,
                frequency, frequency_days, start_date, next_execution_date, estimated_duration_hours,
                estimated_cost, status, created_at, updated_at
            ) VALUES (
                :ref, :title, :desc, :type, :cat_id, :prop_id, :asset_id, :tech_id,
                :freq, :freq_days, :start_date, :next_date, :duration,
                :cost, 'active', datetime('now'), datetime('now')
            )
        ");
        $stmt->execute([
            ':ref' => $ref,
            ':title' => $title,
            ':desc' => $description,
            ':type' => $type,
            ':cat_id' => $categoryId ?: null,
            ':prop_id' => $propertyId,
            ':asset_id' => $assetId,
            ':tech_id' => $technicianId,
            ':freq' => $frequency,
            ':freq_days' => $frequencyDays,
            ':start_date' => $startDate,
            ':next_date' => $nextExecution,
            ':duration' => $durationHours,
            ':cost' => $estimatedCost
        ]);

        $planId = (int)$db->lastInsertId();
        Auth::audit('create', 'maintenance', 'maintenance_plans', $planId, "Plano de Manutenção {$ref} criado.");
        Session::setFlash('success', "Plano {$ref} criado com sucesso.");
        Response::redirect('/maintenance');
    }

    public function generateTasks(Request $request): void
    {
        $days = (int)$request->input('days_ahead', 30);
        $generated = MaintenanceService::generateUpcomingTasks($days);

        $count = count($generated);
        if ($count > 0) {
            Session::setFlash('success', "Foram geradas {$count} novas tarefas de manutenção preventiva de forma idempotente.");
        } else {
            Session::setFlash('info', 'Todas as tarefas para o período já se encontram devidamente agendadas (sem duplicações).');
        }

        Response::redirect('/maintenance');
    }
}
