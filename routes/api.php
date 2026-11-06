<?php

use App\Controllers\Api\ApiController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;

Router::get('/api/units', [ApiController::class, 'getUnits'], [AuthMiddleware::class]);
Router::get('/api/subcategories', [ApiController::class, 'getSubcategories'], [AuthMiddleware::class]);
Router::get('/api/notifications', [ApiController::class, 'getNotifications'], [AuthMiddleware::class]);
Router::post('/api/notifications/read-all', [ApiController::class, 'markNotificationsRead'], [AuthMiddleware::class]);
