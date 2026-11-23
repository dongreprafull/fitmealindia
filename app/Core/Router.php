<?php
declare(strict_types=1);
namespace Fitmeal\Core;

final class Router {
    private array $routes = [];
    public function get(string $path, callable|array $handler): void { $this->add('GET', $path, $handler); }
    public function post(string $path, callable|array $handler): void { $this->add('POST', $path, $handler); }
    private function add(string $method, string $path, callable|array $handler): void { $this->routes[] = compact('method','path','handler'); }
    public function dispatch(string $method, string $uri): void {
        foreach ($this->routes as $route) {
            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $route['path']);
            if ($route['method'] !== $method || !preg_match('#^' . $pattern . '/?$#', $uri, $matches)) continue;
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $handler = $route['handler'];
            if (is_array($handler)) { [$class, $action] = $handler; (new $class())->$action($params); }
            else $handler($params);
            return;
        }
        http_response_code(404); View::render('public/404', ['title' => 'Page not found']);
    }
}
