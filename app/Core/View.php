<?php

namespace App\Core;

class View
{
    private static string $viewsPath = __DIR__ . '/../Views';

    public static function render(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        extract($data);

        // Include global helper data
        $currentUser = Auth::user();
        $appName = 'JFS Properties';
        $csrfToken = Session::csrfToken();
        $flashSuccess = Session::getFlash('success');
        $flashError = Session::getFlash('error');
        $flashWarning = Session::getFlash('warning');
        $flashInfo = Session::getFlash('info');

        $viewFile = self::$viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Ficheiro de vista não encontrado: {$viewFile}");
        }

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = self::$viewsPath . '/' . str_replace('.', '/', $layout) . '.php';
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Ficheiro de layout não encontrado: {$layoutFile}");
            }
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function component(string $component, array $data = []): void
    {
        extract($data);
        $componentFile = self::$viewsPath . '/components/' . str_replace('.', '/', $component) . '.php';
        if (file_exists($componentFile)) {
            include $componentFile;
        }
    }
}

// Global View Helper Functions
if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Core\Session::csrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('format_money')) {
    function format_money(float|int|null $amount, string $currency = 'MT'): string
    {
        $val = (float)($amount ?? 0);
        return number_format($val, 2, ',', '.') . ' ' . $currency;
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $dateTime, string $format = 'd/m/Y H:i'): string
    {
        if (!$dateTime) {
            return '-';
        }
        $ts = strtotime($dateTime);
        return $ts ? date($format, $ts) : '-';
    }
}
