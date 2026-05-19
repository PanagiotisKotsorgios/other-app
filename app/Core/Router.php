<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = strtok($uri, '?');

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$controller, $action] = explode('@', $handler);
                $controllerClass = "App\\Controllers\\{$controller}";

                if (!class_exists($controllerClass)) {
                    $this->abort(404, "Controller {$controller} not found.");
                }

                $obj = new $controllerClass();
                if (!method_exists($obj, $action)) {
                    $this->abort(404, "Action {$action} not found.");
                }

                call_user_func_array([$obj, $action], $matches);
                return;
            }
        }

        $this->abort(404);
    }

    private function abort(int $code, string $message = 'Not Found'): void
    {
        http_response_code($code);
        echo "<h1>{$code}</h1><p>{$message}</p>";
        exit;
    }
}
