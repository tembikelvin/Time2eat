<?php

declare(strict_types=1);

namespace core;

/**
 * Enhanced Router Class
 * Advanced routing with middleware, parameter binding, and RESTful routes
 */
class EnhancedRouter
{
    private array $routes = [];
    private array $middleware = [];
    private array $routeGroups = [];
    private string $currentGroup = '';
    private array $namedRoutes = [];
    private ?string $currentRoute = null;
    private string $baseUrl = '';

    public function __construct(string $baseUrl = '')
    {
        $this->baseUrl = $baseUrl;
    }
    
    /**
     * Add GET route
     */
    public function get(string $path, callable|array|string $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    /**
     * Add POST route
     */
    public function post(string $path, callable|array|string $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    /**
     * Add PUT route
     */
    public function put(string $path, callable|array|string $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    /**
     * Add PATCH route
     */
    public function patch(string $path, callable|array|string $handler, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $path, $handler, $middleware);
    }
    
    /**
     * Add DELETE route
     */
    public function delete(string $path, callable|array|string $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    /**
     * Add route for multiple methods
     */
    public function match(array $methods, string $path, callable|array|string $handler, array $middleware = []): self
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $path, $handler, $middleware);
        }
        return $this;
    }
    
    /**
     * Add route for all methods
     */
    public function any(string $path, callable|array|string $handler, array $middleware = []): self
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        return $this->match($methods, $path, $handler, $middleware);
    }
    
    /**
     * Add RESTful resource routes
     */
    public function resource(string $name, string $controller, array $options = []): self
    {
        $only = $options['only'] ?? ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'];
        $except = $options['except'] ?? [];
        $middleware = $options['middleware'] ?? [];
        
        $actions = array_diff($only, $except);
        
        $routes = [
            'index' => ['GET', "/{$name}", 'index'],
            'create' => ['GET', "/{$name}/create", 'create'],
            'store' => ['POST', "/{$name}", 'store'],
            'show' => ['GET', "/{$name}/{id}", 'show'],
            'edit' => ['GET', "/{$name}/{id}/edit", 'edit'],
            'update' => ['PUT', "/{$name}/{id}", 'update'],
            'destroy' => ['DELETE', "/{$name}/{id}", 'destroy']
        ];
        
        foreach ($actions as $action) {
            if (isset($routes[$action])) {
                [$method, $path, $controllerMethod] = $routes[$action];
                $this->addRoute($method, $path, [$controller, $controllerMethod], $middleware)
                     ->name("{$name}.{$action}");
            }
        }
        
        return $this;
    }
    
    /**
     * Add API resource routes
     */
    public function apiResource(string $name, string $controller, array $options = []): self
    {
        $options['except'] = array_merge($options['except'] ?? [], ['create', 'edit']);
        return $this->resource($name, $controller, $options);
    }
    
    /**
     * Create route group
     */
    public function group(array $attributes, callable $callback): self
    {
        $previousGroup = $this->currentGroup;
        
        $prefix = $attributes['prefix'] ?? '';
        $middleware = $attributes['middleware'] ?? [];
        
        $this->currentGroup = $previousGroup . $prefix;
        
        if (!empty($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        }
        
        $callback($this);
        
        $this->currentGroup = $previousGroup;
        
        if (!empty($middleware)) {
            $this->middleware = array_slice($this->middleware, 0, -count($middleware));
        }
        
        return $this;
    }
    
    /**
     * Add named route
     */
    public function name(string $name): self
    {
        if ($this->currentRoute) {
            $this->namedRoutes[$name] = $this->currentRoute;
        }
        return $this;
    }
    
    /**
     * Add middleware to route
     */
    public function middleware(array|string $middleware): self
    {
        if ($this->currentRoute) {
            $middleware = is_string($middleware) ? [$middleware] : $middleware;
            $this->routes[$this->currentRoute]['middleware'] = array_merge(
                $this->routes[$this->currentRoute]['middleware'],
                $middleware
            );
        }
        return $this;
    }
    
    /**
     * Add route
     */
    private function addRoute(string $method, string $path, callable|array|string $handler, array $middleware = []): self
    {
        $path = $this->currentGroup . $path;
        $path = $this->normalizePath($path);
        
        $routeKey = $method . ':' . $path;
        $this->currentRoute = $routeKey;
        
        $this->routes[$routeKey] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => array_merge($this->middleware, $middleware),
            'pattern' => $this->compilePattern($path),
            'parameters' => $this->extractParameters($path)
        ];
        
        return $this;
    }
    
    /**
     * Dispatch request
     */
    public function dispatch(string $method, string $uri): mixed
    {
        $uri = $this->normalizePath($uri);
        $method = strtoupper($method);

        // Handle method spoofing for PUT, PATCH, DELETE via _method field
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // Handle base URL - remove base path from URI
        $basePath = parse_url($this->baseUrl, PHP_URL_PATH) ?? '';
        $basePath = rtrim($basePath, '/');

        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
            if (empty($uri)) {
                $uri = '/';
            }
        }

        // Also handle script name based detection for subdirectory installations
        if (empty($basePath) && isset($_SERVER['SCRIPT_NAME'])) {
            $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
            $scriptDir = rtrim($scriptDir, '/');

            if ($scriptDir !== '/' && $scriptDir !== '\\' && strpos($uri, $scriptDir) === 0) {
                $uri = substr($uri, strlen($scriptDir));
                if (empty($uri)) {
                    $uri = '/';
                }
            }
        }

        // Production environment debugging
        if (defined('APP_ENV') && APP_ENV === 'production') {
            error_log("Router dispatch - Method: $method, URI: $uri, BasePath: $basePath, ScriptDir: " . ($scriptDir ?? 'none'));
        }

        // Debug logging for vendor orders routes
        if (strpos($uri, '/vendor/orders') === 0) {
            file_put_contents(__DIR__ . '/../../logs/router_debug.log', date('Y-m-d H:i:s') . " - Dispatching: $method $uri\n", FILE_APPEND);
        }

        // Handle OPTIONS requests for CORS
        if ($method === 'OPTIONS') {
            $this->handleOptionsRequest();
            return null;
        }

        foreach ($this->routes as $routeKey => $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Debug logging for vendor orders routes
            if (strpos($uri, '/vendor/orders') === 0) {
                file_put_contents(__DIR__ . '/../../logs/router_debug.log', date('Y-m-d H:i:s') . " - Checking route: {$route['path']} with pattern: {$route['pattern']}\n", FILE_APPEND);
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches); // Remove full match

                $parameters = $this->bindParameters($route['parameters'], $matches);

                // Debug logging for vendor orders routes
                if (strpos($uri, '/vendor/orders') === 0) {
                    file_put_contents(__DIR__ . '/../../logs/router_debug.log', date('Y-m-d H:i:s') . " - Route matched! Parameters: " . json_encode($parameters) . "\n", FILE_APPEND);
                    file_put_contents(__DIR__ . '/../../logs/router_debug.log', date('Y-m-d H:i:s') . " - Handler: " . json_encode($route['handler']) . "\n", FILE_APPEND);
                }

                try {
                    return $this->executeRoute($route, $parameters);
                } catch (Exception $e) {
                    $this->handleException($e);
                    return null;
                }
            }
        }

        // Debug logging for vendor orders routes
        if (strpos($uri, '/vendor/orders') === 0) {
            file_put_contents(__DIR__ . '/../../logs/router_debug.log', date('Y-m-d H:i:s') . " - No route matched for: $method $uri\n", FILE_APPEND);
        }

        $this->handleNotFound();
        return null;
    }

    private function handleException(Exception $e): void
    {
        error_log($e->getMessage());
        http_response_code(500);

        echo "<h1>500 - Internal Server Error</h1>";
        echo "<p>Something went wrong. Please check the logs for more details.</p>";
        
        // In a development environment, you might want to show more details
        // if (defined('APP_ENV') && APP_ENV === 'development') {
            echo "<pre>";
            echo "<strong>Error:</strong> " . $e->getMessage() . "\n";
            echo "<strong>File:</strong> " . $e->getFile() . " on line " . $e->getLine() . "\n";
            echo "<strong>Stack trace:</strong>\n" . $e->getTraceAsString();
            echo "</pre>";
        // }
    }
    
    /**
     * Execute route with middleware
     */
    private function executeRoute(array $route, array $parameters): mixed
    {
        // Execute middleware
        foreach ($route['middleware'] as $middlewareClass) {
            if (!$this->executeMiddleware($middlewareClass, $parameters)) {
                return null;
            }
        }
        
        // Execute handler
        return $this->executeHandler($route['handler'], $parameters);
    }
    
    /**
     * Execute middleware
     */
    private function executeMiddleware(string $middlewareClass, array $parameters): bool
    {
        // Handle role-based middleware (e.g., "RoleMiddleware:customer")
        $middlewareParts = explode(':', $middlewareClass);
        $actualMiddlewareClass = $middlewareParts[0];
        $middlewareParams = isset($middlewareParts[1]) ? ['role' => $middlewareParts[1]] : [];

        // Merge with existing parameters
        $parameters = array_merge($parameters, $middlewareParams);

        // Try to load the middleware class
        if (!class_exists($actualMiddlewareClass)) {
            // Try loading from middleware directory
            $middlewareFile = __DIR__ . '/../middleware/' . $actualMiddlewareClass . '.php';
            if (file_exists($middlewareFile)) {
                require_once $middlewareFile;
            }
        }

        if (!class_exists($actualMiddlewareClass)) {
            error_log("Middleware class not found: {$actualMiddlewareClass}");
            return true; // Continue execution if middleware not found
        }

        $middleware = new $actualMiddlewareClass();

        if (method_exists($middleware, 'handle')) {
            return $middleware->handle($parameters);
        }

        return true;
    }
    
    /**
     * Execute route handler
     */
    private function executeHandler(callable|array|string $handler, array $parameters): mixed
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $parameters);
        }
        
        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;
            return $this->executeController($controllerClass, $method, $parameters);
        }
        
        if (is_string($handler)) {
            if (strpos($handler, '@') !== false) {
                [$controllerClass, $method] = explode('@', $handler, 2);
                return $this->executeController($controllerClass, $method, $parameters);
            }
            
            // Assume it's a controller class with default method
            return $this->executeController($handler, 'index', $parameters);
        }
        
        throw new \Exception("Invalid route handler");
    }
    
    /**
     * Execute controller method
     */
    private function executeController(string $controllerClass, string $method, array $parameters): mixed
    {
        // Add namespace if not present (only if it doesn't already have a namespace)
        if (strpos($controllerClass, '\\') === false) {
            $controllerClass = "controllers\\{$controllerClass}";
        }
        
        // Try to load the controller file if class doesn't exist
        if (!class_exists($controllerClass)) {
            // Handle Time2Eat\Controllers namespace mapping to actual file structure
            if (strpos($controllerClass, 'Time2Eat\\Controllers\\') === 0) {
                // Map Time2Eat\Controllers\Admin\UserManagementController to controllers/Admin/UserManagementController.php
                $relativePath = str_replace('Time2Eat\\Controllers\\', '', $controllerClass);
                $controllerFile = __DIR__ . '/../controllers/' . str_replace('\\', '/', $relativePath) . '.php';
            } else {
                // Default mapping for other namespaces
                $controllerFile = __DIR__ . '/../controllers/' . str_replace('\\', '/', $controllerClass) . '.php';
            }
            
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
            } else {
                // Production debugging
                if (defined('APP_ENV') && APP_ENV === 'production') {
                    error_log("Controller file not found: $controllerFile for class: $controllerClass");
                }
            }
        }
        
        if (!class_exists($controllerClass)) {
            // Production debugging
            if (defined('APP_ENV') && APP_ENV === 'production') {
                error_log("Controller class not found after loading: $controllerClass");
            }
            throw new \Exception("Controller class not found: {$controllerClass}");
        }
        
        try {
            $controller = new $controllerClass();
        } catch (\Exception $e) {
            if (defined('APP_ENV') && APP_ENV === 'production') {
                error_log("Error instantiating controller $controllerClass: " . $e->getMessage());
            }
            throw $e;
        }
        
        if (!method_exists($controller, $method)) {
            if (defined('APP_ENV') && APP_ENV === 'production') {
                error_log("Method $method not found in controller $controllerClass");
            }
            throw new \Exception("Method {$method} not found in controller {$controllerClass}");
        }

        // Execute middleware if controller extends BaseController
        if (method_exists($controller, 'executeMiddleware')) {
            if (!$controller->executeMiddleware($method)) {
                return null;
            }
        }

        // Convert associative array to numeric array for method parameters
        $methodParams = array_values($parameters);

        return call_user_func_array([$controller, $method], $methodParams);
    }
    
    /**
     * Normalize path
     */
    private function normalizePath(string $path): string
    {
        $path = trim($path, '/');
        return $path === '' ? '/' : '/' . $path;
    }
    
    /**
     * Compile path pattern for regex matching
     */
    private function compilePattern(string $path): string
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Extract parameter names from path
     */
    private function extractParameters(string $path): array
    {
        preg_match_all('/\{([^}]+)\}/', $path, $matches);
        return $matches[1];
    }
    
    /**
     * Bind parameters to values
     */
    private function bindParameters(array $parameterNames, array $values): array
    {
        $parameters = [];
        
        foreach ($parameterNames as $index => $name) {
            $value = $values[$index] ?? null;
            
            // Type casting based on parameter name patterns
            if (preg_match('/^(id|count|page|limit)$/', $name)) {
                $value = (int)$value;
            } elseif (preg_match('/^(price|amount|total)$/', $name)) {
                $value = (float)$value;
            }
            
            $parameters[$name] = $value;
        }
        
        return $parameters;
    }
    
    /**
     * Generate URL for named route
     */
    public function url(string $name, array $parameters = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Named route not found: {$name}");
        }

        $routeKey = $this->namedRoutes[$name];
        $route = $this->routes[$routeKey];
        $path = $route['path'];

        foreach ($parameters as $key => $value) {
            $path = str_replace("{{$key}}", (string)$value, $path);
        }

        // Add base URL if configured
        if (!empty($this->baseUrl)) {
            $basePath = parse_url($this->baseUrl, PHP_URL_PATH) ?? '';
            $basePath = rtrim($basePath, '/');
            if ($basePath) {
                $path = $basePath . $path;
            }
        }

        return $path;
    }
    
    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        
        if ($this->acceptsJson()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Route not found']);
        } else {
            echo "<h1>404 - Page Not Found</h1>";
        }
    }
    
    /**
     * Handle OPTIONS request for CORS
     */
    private function handleOptionsRequest(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
        http_response_code(200);
    }
    
    /**
     * Check if request accepts JSON
     */
    private function acceptsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($accept, 'application/json') !== false;
    }
    
    /**
     * Get all routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
