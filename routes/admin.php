<?php

use App\Controllers\Admin\AdminController;
use App\Controllers\Admin\AssetController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\FinancialController;
use App\Controllers\Admin\IncidentController;
use App\Controllers\Admin\InspectionController;
use App\Controllers\Admin\MaintenanceController;
use App\Controllers\Admin\OccupantController;
use App\Controllers\Admin\OperationController;
use App\Controllers\Admin\PropertyController;
use App\Controllers\Admin\ReportController;
use App\Controllers\Admin\WorkOrderController;
use App\Core\Router;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

$adminMiddlewares = [AuthMiddleware::class, AdminMiddleware::class];
$adminPostMiddlewares = [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class];

// 1. Dashboard
Router::get('/dashboard', [DashboardController::class, 'index'], $adminMiddlewares);

// 2. Properties
Router::get('/properties', [PropertyController::class, 'index'], $adminMiddlewares);
Router::get('/properties/create', [PropertyController::class, 'create'], $adminMiddlewares);
Router::post('/properties', [PropertyController::class, 'store'], $adminPostMiddlewares);
Router::get('/properties/{id}', [PropertyController::class, 'show'], $adminMiddlewares);
Router::post('/properties/{id}/archive', [PropertyController::class, 'archive'], $adminPostMiddlewares);

// 3. Occupants
Router::get('/occupants', [OccupantController::class, 'index'], $adminMiddlewares);
Router::get('/occupants/create', [OccupantController::class, 'create'], $adminMiddlewares);
Router::post('/occupants', [OccupantController::class, 'store'], $adminPostMiddlewares);

// 4. Incidents
Router::get('/incidents', [IncidentController::class, 'index'], $adminMiddlewares);
Router::get('/incidents/create', [IncidentController::class, 'create'], $adminMiddlewares);
Router::post('/incidents', [IncidentController::class, 'store'], $adminPostMiddlewares);
Router::get('/incidents/{id}', [IncidentController::class, 'show'], $adminMiddlewares);
Router::post('/incidents/{id}/status', [IncidentController::class, 'updateStatus'], $adminPostMiddlewares);
Router::post('/incidents/{id}/assign', [IncidentController::class, 'assign'], $adminPostMiddlewares);
Router::post('/incidents/{id}/comment', [IncidentController::class, 'addComment'], $adminPostMiddlewares);

// 5. Work Orders
Router::get('/work-orders', [WorkOrderController::class, 'index'], $adminMiddlewares);
Router::get('/work-orders/create', [WorkOrderController::class, 'create'], $adminMiddlewares);
Router::post('/work-orders', [WorkOrderController::class, 'store'], $adminPostMiddlewares);
Router::get('/work-orders/{id}', [WorkOrderController::class, 'show'], $adminMiddlewares);
Router::post('/work-orders/{id}/intervention', [WorkOrderController::class, 'recordIntervention'], $adminPostMiddlewares);
Router::post('/work-orders/{id}/material', [WorkOrderController::class, 'addMaterial'], $adminPostMiddlewares);
Router::get('/work-orders/{id}/report', [WorkOrderController::class, 'report'], $adminMiddlewares);

// 6. Maintenance
Router::get('/maintenance', [MaintenanceController::class, 'index'], $adminMiddlewares);
Router::get('/maintenance/create', [MaintenanceController::class, 'create'], $adminMiddlewares);
Router::post('/maintenance', [MaintenanceController::class, 'store'], $adminPostMiddlewares);
Router::post('/maintenance/generate-tasks', [MaintenanceController::class, 'generateTasks'], $adminPostMiddlewares);

// 7. Assets
Router::get('/assets', [AssetController::class, 'index'], $adminMiddlewares);
Router::get('/assets/create', [AssetController::class, 'create'], $adminMiddlewares);
Router::post('/assets', [AssetController::class, 'store'], $adminPostMiddlewares);
Router::get('/assets/{id}', [AssetController::class, 'show'], $adminMiddlewares);

// 8. Inspections
Router::get('/inspections', [InspectionController::class, 'index'], $adminMiddlewares);
Router::get('/inspections/create', [InspectionController::class, 'create'], $adminMiddlewares);
Router::post('/inspections', [InspectionController::class, 'store'], $adminPostMiddlewares);
Router::get('/inspections/{id}/execute', [InspectionController::class, 'execute'], $adminMiddlewares);
Router::post('/inspections/{id}/submit', [InspectionController::class, 'submitResults'], $adminPostMiddlewares);

// 9. Operations
Router::get('/operations/technicians', [OperationController::class, 'technicians'], $adminMiddlewares);
Router::post('/operations/technicians', [OperationController::class, 'storeTechnician'], $adminPostMiddlewares);
Router::get('/operations/suppliers', [OperationController::class, 'suppliers'], $adminMiddlewares);
Router::post('/operations/suppliers', [OperationController::class, 'storeSupplier'], $adminPostMiddlewares);
Router::get('/operations/stock', [OperationController::class, 'stock'], $adminMiddlewares);
Router::post('/operations/stock/movement', [OperationController::class, 'recordStockMovement'], $adminPostMiddlewares);

// 10. Financial
Router::get('/financial', [FinancialController::class, 'index'], $adminMiddlewares);
Router::post('/financial/expenses', [FinancialController::class, 'storeExpense'], $adminPostMiddlewares);

// 11. Reports
Router::get('/reports', [ReportController::class, 'index'], $adminMiddlewares);

// 12. Administration & Settings
Router::get('/admin/users', [AdminController::class, 'users'], $adminMiddlewares);
Router::post('/admin/users', [AdminController::class, 'storeUser'], $adminPostMiddlewares);
Router::get('/admin/audit', [AdminController::class, 'audit'], $adminMiddlewares);
Router::get('/admin/settings', [AdminController::class, 'settings'], $adminMiddlewares);
Router::post('/admin/settings', [AdminController::class, 'updateSettings'], $adminPostMiddlewares);
