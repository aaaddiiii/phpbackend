<?php

// Simple autoloader for our classes
spl_autoload_register(function ($class) {
    // Remove namespace prefix
    $class = str_replace('App\\', '', $class);
    
    // Convert namespace separators to directory separators
    $class = str_replace('\\', '/', $class);
    
    // Build the file path
    $file = __DIR__ . '/src/' . $class . '.php';
    
    // If the file exists, include it
    if (file_exists($file)) {
        require_once $file;
    }
});

// Simple environment variable loader
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue; // Skip comments
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Load environment variables
// In production, Docker/Render will provide environment variables
// In development, load from .env file
if (file_exists(__DIR__ . '/.env')) {
    loadEnv(__DIR__ . '/.env');
}

// Set error reporting
if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set('UTC');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Route handling
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string and normalize path
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove the base directory path (adjust for your setup)
// In production (Render), the app runs from root, so no base path needed
$basePath = $_ENV['BASE_PATH'] ?? '';
if ($basePath && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Remove index.php from path if present
if (strpos($path, '/index.php') === 0) {
    $path = substr($path, strlen('/index.php'));
}

// Ensure path starts with /
$path = '/' . ltrim($path, '/');

// Debug output (remove in production)
if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
    error_log("Request Debug - URI: {$requestUri}, Method: {$requestMethod}, Parsed Path: {$path}");
}

// Import router
require_once __DIR__ . '/src/Router.php';

$router = new App\Router();
$router->handleRequest($requestMethod, $path);
