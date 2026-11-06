<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AdminMiddleware
{
    public function handle(Request $request): bool
    {
        if (!Auth::check()) {
            Response::redirect('/login');
            return false;
        }

        $user = Auth::user();
        if ($user['role_slug'] === 'occupant') {
            Response::redirect('/portal');
            return false;
        }

        return true;
    }
}
