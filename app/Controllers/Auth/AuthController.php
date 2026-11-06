<?php

namespace App\Controllers\Auth;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

class AuthController
{
    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user['role_slug'] === 'occupant') {
                Response::redirect('/portal');
            }
            Response::redirect('/dashboard');
        }

        View::render('auth/login', [
            'title' => 'Iniciar Sessão - JFS Properties'
        ], 'layouts/auth');
    }

    public function login(Request $request): void
    {
        $email = trim((string)$request->input('email'));
        $password = (string)$request->input('password');

        if (empty($email) || empty($password)) {
            Session::setFlash('error', 'Por favor, preencha o seu e-mail e palavra-passe.');
            Response::redirect('/login');
        }

        if (Auth::attempt($email, $password)) {
            $user = Auth::user();
            Session::setFlash('success', "Bem-vindo de volta, {$user['name']}!");
            
            if ($user['role_slug'] === 'occupant') {
                Response::redirect('/portal');
            } else {
                Response::redirect('/dashboard');
            }
        }

        Session::setFlash('error', 'Credenciais inválidas. Verifique o seu e-mail e palavra-passe.');
        Response::redirect('/login');
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        Session::setFlash('info', 'Sessão terminada com segurança.');
        Response::redirect('/login');
    }

    public function showForgotPassword(Request $request): void
    {
        View::render('auth/forgot-password', [
            'title' => 'Recuperar Palavra-passe - JFS Properties'
        ], 'layouts/auth');
    }

    public function forgotPassword(Request $request): void
    {
        $email = trim((string)$request->input('email'));
        if (empty($email)) {
            Session::setFlash('error', 'Por favor, indique o seu e-mail.');
            Response::redirect('/forgot-password');
        }

        // Inform user that if email exists, reset instructions have been dispatched
        Session::setFlash('success', 'Se o e-mail estiver registado no sistema, receberá instruções para redefinir a palavra-passe.');
        Response::redirect('/login');
    }
}
