<?php

declare(strict_types=1);

// Error reporting configuration
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Autoloader for App\ and Database\
spl_autoload_register(function ($class) {
    $prefixApp = 'App\\';
    $prefixDatabase = 'Database\\';
    $baseDir = dirname(__DIR__) . '/app/';

    if (strncmp($prefixApp, $class, strlen($prefixApp)) === 0) {
        $relativeClass = substr($class, strlen($prefixApp));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    if (strncmp($prefixDatabase, $class, strlen($prefixDatabase)) === 0) {
        $relativeClass = substr($class, strlen($prefixDatabase));
        $file = dirname(__DIR__) . '/database/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

use App\Core\Database;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;

try {
    // Start session
    Session::start();

    // Load routes
    require_once dirname(__DIR__) . '/routes/web.php';
    require_once dirname(__DIR__) . '/routes/admin.php';
    require_once dirname(__DIR__) . '/routes/api.php';

    // Dispatch request
    $request = new Request();
    Router::dispatch($request);
} catch (\Throwable $e) {
    Logger::error("Unhandled exception: " . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);

    http_response_code(500);
    $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

    if ($isAjax) {
        Response::json(['error' => 'Erro interno do servidor: ' . $e->getMessage()], 500);
    } else {
        echo "<!DOCTYPE html><html lang='pt'><head><meta charset='utf-8'><title>Erro no Sistema - JFS Properties</title><script src='https://cdn.tailwindcss.com'></script></head><body class='bg-slate-100 flex items-center justify-center min-h-screen p-4'><div class='bg-white p-8 rounded-2xl shadow-xl max-w-lg w-full text-center border border-slate-200'><div class='w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-2xl'>!</div><h1 class='text-2xl font-bold text-slate-800 mb-2'>Ocorreu um erro no servidor</h1><p class='text-slate-600 mb-6 text-sm'>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p><a href='/dashboard' class='inline-block px-5 py-2.5 bg-sky-600 text-white font-medium rounded-lg hover:bg-sky-700 transition'>Regressar ao Início</a></div></body></html>";
    }
}
