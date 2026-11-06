<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use PDO;

class FinancialController
{
    public function index(Request $request): void
    {
        $db = Database::getConnection();
        $propertyId = $request->query('property_id');
        $categoryId = $request->query('category_id');
        $year = (int)$request->query('year', date('Y'));

        $query = "
            SELECT e.*, 
                   p.name as property_name,
                   c.name as category_name,
                   s.name as supplier_name,
                   w.reference as work_order_ref
            FROM expenses e
            JOIN properties p ON e.property_id = p.id
            JOIN expense_categories c ON e.category_id = c.id
            LEFT JOIN suppliers s ON e.supplier_id = s.id
            LEFT JOIN work_orders w ON e.work_order_id = w.id
            WHERE strftime('%Y', e.expense_date) = :year
        ";
        $params = [':year' => (string)$year];

        if ($propertyId) {
            $query .= " AND e.property_id = :prop_id";
            $params[':prop_id'] = (int)$propertyId;
        }
        if ($categoryId) {
            $query .= " AND e.category_id = :cat_id";
            $params[':cat_id'] = (int)$categoryId;
        }

        $query .= " ORDER BY e.expense_date DESC";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $expenses = $stmt->fetchAll();

        // Totals
        $totalExpenses = 0.0;
        foreach ($expenses as $exp) {
            $totalExpenses += (float)$exp['amount'];
        }

        // Budget vs Execution comparison
        $budgetQuery = "
            SELECT b.property_id, p.name as property_name,
                   sum(b.budgeted_amount) as total_budgeted,
                   COALESCE((SELECT sum(e.amount) FROM expenses e WHERE e.property_id = b.property_id AND strftime('%Y', e.expense_date) = :year), 0) as total_actual
            FROM budgets b
            JOIN properties p ON b.property_id = p.id
            WHERE b.year = :year
            GROUP BY b.property_id
        ";
        $bStmt = $db->prepare($budgetQuery);
        $bStmt->execute([':year' => (string)$year]);
        $budgetComparison = $bStmt->fetchAll();

        $properties = $db->query("SELECT id, name FROM properties ORDER BY name ASC")->fetchAll();
        $categories = $db->query("SELECT id, name FROM expense_categories ORDER BY name ASC")->fetchAll();
        $suppliers = $db->query("SELECT id, name FROM suppliers WHERE status = 'active' ORDER BY name ASC")->fetchAll();

        View::render('admin/financial/index', [
            'title' => 'Controlo Financeiro e Custos Operacionais - JFS Properties',
            'expenses' => $expenses,
            'totalExpenses' => $totalExpenses,
            'budgetComparison' => $budgetComparison,
            'properties' => $properties,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'filters' => [
                'property_id' => $propertyId,
                'category_id' => $categoryId,
                'year' => $year
            ]
        ]);
    }

    public function storeExpense(Request $request): void
    {
        $db = Database::getConnection();

        $description = trim((string)$request->input('description'));
        $categoryId = (int)$request->input('category_id');
        $propertyId = (int)$request->input('property_id');
        $supplierId = $request->input('supplier_id') ? (int)$request->input('supplier_id') : null;
        $workOrderId = $request->input('work_order_id') ? (int)$request->input('work_order_id') : null;
        $amount = (float)$request->input('amount', 0);
        $date = $request->input('expense_date') ?: date('Y-m-d');
        $status = trim((string)$request->input('status', 'paid'));

        if (empty($description) || !$categoryId || !$propertyId || $amount <= 0) {
            Session::setFlash('error', 'Descrição, Categoria, Imóvel e Valor válido são obrigatórios.');
            Response::back();
        }

        $year = date('Y');
        $count = (int)$db->query("SELECT count(*) FROM expenses")->fetchColumn() + 1;
        $ref = sprintf("EXP-%s-%03d", $year, $count);

        $stmt = $db->prepare("
            INSERT INTO expenses (
                reference, description, category_id, property_id, supplier_id, work_order_id,
                expense_date, amount, status, registered_by, created_at
            ) VALUES (
                :ref, :desc, :cat_id, :prop_id, :sup_id, :wo_id,
                :exp_date, :amount, :status, :user_id, datetime('now')
            )
        ");
        $stmt->execute([
            ':ref' => $ref,
            ':desc' => $description,
            ':cat_id' => $categoryId,
            ':prop_id' => $propertyId,
            ':sup_id' => $supplierId,
            ':wo_id' => $workOrderId,
            ':exp_date' => $date,
            ':amount' => $amount,
            ':status' => $status,
            ':user_id' => Auth::id()
        ]);

        $expId = (int)$db->lastInsertId();
        Auth::audit('create', 'financial', 'expenses', $expId, "Despesa {$ref} no valor de {$amount} MZN registada.");
        Session::setFlash('success', "Despesa {$ref} registada com sucesso.");
        Response::redirect('/financial');
    }
}
