<?php

namespace core;

/**
 * Simple Router Class for Time2Eat
 * Handles URL routing and dispatching to controllers
 */
class Router {
    private $routes = [];
    private $middlewares = [];
    
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    private function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    public function addMiddleware($middleware) {
        $this->middlewares[] = $middleware;
    }
    
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash except for root
        if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
            $requestUri = rtrim($requestUri, '/');
        }
        
        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod) {
                $pattern = $this->convertToRegex($route['path']);
                if (preg_match($pattern, $requestUri, $matches)) {
                    // Extract parameters
                    $params = array_slice($matches, 1);
                    
                    // Execute middlewares
                    foreach ($this->middlewares as $middleware) {
                        if (!$this->executeMiddleware($middleware)) {
                            return; // Middleware blocked the request
                        }
                    }
                    
                    // Execute handler
                    $this->executeHandler($route['handler'], $params);
                    return;
                }
            }
        }
        
        // No route found - 404
        $this->handle404();
    }
    
    private function convertToRegex($path) {
        // Convert {param} to regex capture groups
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    private function executeHandler($handler, $params = []) {
        if (is_string($handler) && strpos($handler, '@') !== false) {
            // Controller@method format
            list($controllerName, $method) = explode('@', $handler);
            
            // Convert namespace format to file path
            $controllerFile = SRC_PATH . '/' . str_replace('\\', '/', $controllerName) . '.php';
            
            if (!file_exists($controllerFile)) {
                throw new \Exception("Controller file not found: {$controllerFile}");
            }
            
            require_once $controllerFile;
            
            // Convert to full class name
            $controllerClass = str_replace('/', '\\', $controllerName);
            
            if (!class_exists($controllerClass)) {
                throw new \Exception("Controller class not found: {$controllerClass}");
            }
            
            $controller = new $controllerClass();
            
            if (!method_exists($controller, $method)) {
                throw new \Exception("Method {$method} not found in {$controllerClass}");
            }
            
            // Call the method with parameters
            call_user_func_array([$controller, $method], $params);
            
        } elseif (is_callable($handler)) {
            // Closure or function
            call_user_func_array($handler, $params);
        } else {
            throw new \Exception("Invalid handler format");
        }
    }
    
    private function executeMiddleware($middleware) {
        if (is_string($middleware)) {
            // Middleware class
            $middlewareFile = SRC_PATH . '/middleware/' . $middleware . '.php';
            if (file_exists($middlewareFile)) {
                require_once $middlewareFile;
                $middlewareClass = 'middleware\\' . $middleware;
                $middlewareInstance = new $middlewareClass();
                return $middlewareInstance->handle();
            }
        } elseif (is_callable($middleware)) {
            // Closure middleware
            return $middleware();
        }
        
        return true; // Continue if middleware not found
    }
    
    private function handle404() {
        http_response_code(404);
        $errorFile = SRC_PATH . '/views/errors/404.php';
        if (file_exists($errorFile)) {
            require_once $errorFile;
        } else {
            echo "<h1>404 - Page Not Found</h1>";
        }
    }
    
    // Helper method to generate URLs
    public static function url($path = '') {
        $baseUrl = rtrim(APP_URL, '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
    
    // Helper method to redirect
    public static function redirect($path, $statusCode = 302) {
        $url = self::url($path);
        header("Location: {$url}", true, $statusCode);
        exit;
    }
}
