<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

class Router
{
    private array $routes = [];
    private array $globalMiddlewares = [];

    public function addGlobalMiddleware(callable $middleware): void
    {
        $this->globalMiddlewares[] = $middleware;
    }

    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, $handler, array $middlewares): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch(Request $request, string $basePath = ''): void
    {
        $this->applyCorsHeaders();

        if ($request->getMethod() === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $requestPath = $request->getPath();

        if (!empty($basePath) && strpos($requestPath, $basePath) === 0) {
            $requestPath = substr($requestPath, strlen($basePath));
        }

        if (empty($requestPath)) {
            $requestPath = '/';
        }

        foreach ($this->globalMiddlewares as $middleware) {
            if ($middleware($request) === false) {
                return;
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->getMethod()) {
                continue;
            }

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $requestPath, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                foreach ($route['middlewares'] as $middleware) {
                    if ($middleware($request) === false) {
                        return;
                    }
                }

                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$controller, $method] = $handler;
                    $controller->$method($request, $params);
                } else if (is_callable($handler)) {
                    $handler($request, $params);
                }
                return;
            }
        }

        Response::notFound('Route ' . $request->getMethod() . ' ' . $requestPath . ' not found');
    }

    private function applyCorsHeaders(): void
    {
        $configPath = __DIR__ . '/../../../config/app.php';
        $appConfig = file_exists($configPath) ? require $configPath : [];
        $cors = $appConfig['cors'] ?? [];

        $origin = $cors['allowed_origins'] ?? '*';
        $methods = $cors['allowed_methods'] ?? 'GET, POST, PUT, DELETE, OPTIONS';
        $headers = $cors['allowed_headers'] ?? 'Content-Type, X-API-Key, Authorization';

        header("Access-Control-Allow-Origin: {$origin}");
        header("Access-Control-Allow-Methods: {$methods}");
        header("Access-Control-Allow-Headers: {$headers}");
    }
}
