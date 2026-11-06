<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request): bool
    {
        if (!Auth::check()) {
            if ($request->isAjax()) {
                Response::json(['error' => 'Não autenticado'], 401);
            }
            Session::setFlash('error', 'Por favor, inicie sessão para aceder a esta página.');
            Response::redirect('/login');
            return false;
        }
        return true;
    }
}
