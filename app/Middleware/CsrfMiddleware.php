<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware
{
    public function handle(Request $request): bool
    {
        $method = $request->getMethod();
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        $token = $request->input('_csrf_token') ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!Session::validateCsrf($token)) {
            if ($request->isAjax()) {
                Response::json(['error' => 'Token CSRF inválido ou expirado. Por favor, recarregue a página.'], 403);
            }
            Session::setFlash('error', 'Token de segurança expirado. Por favor, tente novamente.');
            Response::back();
            return false;
        }

        return true;
    }
}
