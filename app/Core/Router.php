<?php

namespace App\Core;

class Router
{
    private static array $routes = [];
    private static array $namedRoutes = [];

    public static function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::addRoute('GET', $path, $handler, $middlewares);
    }

    public static function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::addRoute('POST', $path, $handler, $middlewares);
    }

    public static function put(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::addRoute('PUT', $path, $handler, $middlewares);
    }

    public static function delete(string $path, array|callable $handler, array $middlewares = []): void
    {
        self::addRoute('DELETE', $path, $handler, $middlewares);
    }

    private static function addRoute(string $method, string $path, array|callable $handler, array $middlewares = []): void
    {
        $path = '/' . trim($path, '/');
        if ($path === '//') {
            $path = '/';
        }

        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public static function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Convert route path to regex (e.g., /properties/{id} => ^/properties/(?P<id>[^/]+)$)
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = [];
                foreach ($matches as $key => $val) {
                    if (is_string($key)) {
                        $params[$key] = $val;
                    }
                }
                $request->setRouteParams($params);

                // Execute middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $mw = new $middleware();
                    $result = $mw->handle($request);
                    if ($result === false) {
                        return;
                    }
                }

                // Call handler
                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func($handler, $request);
                    return;
                }

                if (is_array($handler)) {
                    [$controllerClass, $methodName] = $handler;
                    $controller = new $controllerClass();
                    $controller->$methodName($request);
                    return;
                }
            }
        }

        // Not Found
        http_response_code(404);
        if ($request->isAjax()) {
            Response::json(['error' => 'Rota não encontrada (404)'], 404);
        } else {
            View::render('errors/404', ['title' => 'Página não encontrada']);
        }
    }
}
