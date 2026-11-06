<?php

use App\Controllers\Auth\AuthController;
use App\Controllers\Frontend\PortalController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

// Authentication
Router::get('/', [AuthController::class, 'showLogin']);
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
Router::post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);
Router::get('/forgot-password', [AuthController::class, 'showForgotPassword']);
Router::post('/forgot-password', [AuthController::class, 'forgotPassword'], [CsrfMiddleware::class]);

// Occupant Portal
Router::get('/portal', [PortalController::class, 'dashboard'], [AuthMiddleware::class]);
Router::post('/portal/incidents', [PortalController::class, 'submitIncident'], [AuthMiddleware::class, CsrfMiddleware::class]);
Router::get('/portal/incidents/{id}', [PortalController::class, 'showIncident'], [AuthMiddleware::class]);
Router::post('/portal/incidents/{id}/comment', [PortalController::class, 'addComment'], [AuthMiddleware::class, CsrfMiddleware::class]);
