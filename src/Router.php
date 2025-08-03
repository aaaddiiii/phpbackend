<?php

namespace App;

class Router {
    private $routes = [];

    public function __construct() {
        $this->setupRoutes();
    }

    private function setupRoutes() {
        // Debug endpoint
        $this->routes['GET']['/debug'] = [Controllers\AuthController::class, 'debug'];
        
        // Authentication routes
        $this->routes['POST']['/api/register'] = [Controllers\AuthController::class, 'register'];
        $this->routes['POST']['/api/login'] = [Controllers\AuthController::class, 'login'];
        $this->routes['POST']['/api/google-login'] = [Controllers\AuthController::class, 'googleLogin'];
        $this->routes['POST']['/api/logout'] = [Controllers\AuthController::class, 'logout'];
        $this->routes['GET']['/api/user'] = [Controllers\AuthController::class, 'getUser'];
        $this->routes['POST']['/api/refresh'] = [Controllers\AuthController::class, 'refreshToken'];

        // Property routes
        $this->routes['GET']['/api/properties'] = [Controllers\PropertyController::class, 'index'];
        $this->routes['GET']['/api/properties/{id}'] = [Controllers\PropertyController::class, 'show'];
        $this->routes['POST']['/api/properties'] = [Controllers\PropertyController::class, 'store'];
        $this->routes['PUT']['/api/properties/{id}'] = [Controllers\PropertyController::class, 'update'];
        $this->routes['DELETE']['/api/properties/{id}'] = [Controllers\PropertyController::class, 'destroy'];
        $this->routes['POST']['/api/properties/{id}/images'] = [Controllers\PropertyController::class, 'uploadImages'];
    }

    public function handleRequest($method, $path) {
        try {
            // Clean and normalize the path
            $path = '/' . trim($path, '/');
            
            // Debug info (remove in production)
            if ($_ENV['APP_DEBUG'] === 'true') {
                error_log("Router Debug - Method: {$method}, Path: {$path}");
                error_log("Available routes for {$method}: " . json_encode(array_keys($this->routes[$method] ?? [])));
            }
            
            // Check if exact route exists
            if (isset($this->routes[$method][$path])) {
                $this->executeRoute($this->routes[$method][$path], []);
                return;
            }

            // Check for parameterized routes
            foreach ($this->routes[$method] ?? [] as $route => $handler) {
                $params = $this->matchRoute($route, $path);
                if ($params !== false) {
                    $this->executeRoute($handler, $params);
                    return;
                }
            }

            // Route not found
            http_response_code(404);
            echo json_encode([
                'error' => 'Route not found',
                'debug' => $_ENV['APP_DEBUG'] === 'true' ? [
                    'method' => $method,
                    'path' => $path,
                    'available_routes' => array_keys($this->routes[$method] ?? [])
                ] : null
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Internal server error', 
                'message' => $e->getMessage(),
                'debug' => $_ENV['APP_DEBUG'] === 'true' ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ]);
        }
    }

    private function matchRoute($route, $path) {
        $routeParts = explode('/', trim($route, '/'));
        $pathParts = explode('/', trim($path, '/'));

        if (count($routeParts) !== count($pathParts)) {
            return false;
        }

        $params = [];
        for ($i = 0; $i < count($routeParts); $i++) {
            if (strpos($routeParts[$i], '{') === 0) {
                $paramName = str_replace(['{', '}'], '', $routeParts[$i]);
                $params[$paramName] = $pathParts[$i];
            } elseif ($routeParts[$i] !== $pathParts[$i]) {
                return false;
            }
        }

        return $params;
    }

    private function executeRoute($handler, $params) {
        [$controllerClass, $method] = $handler;
        
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller {$controllerClass} not found");
        }

        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new Exception("Method {$method} not found in {$controllerClass}");
        }

        $controller->$method($params);
    }
}
