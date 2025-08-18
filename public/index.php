<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables (only if .env file exists)
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    // .env file doesn't exist, which is normal in production
    // Environment variables are set via Digital Ocean App Platform
    error_log('No .env file found, using environment variables from platform');
}

// Start session
session_start();

// Simple routing
$router = new \Bramus\Router\Router();

// Public routes
$router->get('/', function() {
    // Serve the static HTML file for now
    $htmlFile = __DIR__ . '/index.html';
    if (file_exists($htmlFile)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($htmlFile);
    } else {
        // Fallback to basic HTML
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>WFN24 - World Football News 24</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-50">
            <div class="min-h-screen flex items-center justify-center">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-gray-800 mb-4">WFN24</h1>
                    <p class="text-xl text-gray-600 mb-8">World Football News 24</p>
                    <p class="text-gray-500">Loading the world-class football platform...</p>
                </div>
            </div>
        </body>
        </html>';
    }
});

$router->get('/health', function() {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'environment' => $_ENV['APP_ENV'] ?? 'production',
        'database_configured' => isset($_ENV['DB_HOST']),
        'football_api_configured' => isset($_ENV['FOOTBALL_API_KEY'])
    ]);
});

$router->get('/api/news', function() {
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'News API endpoint - Coming soon',
        'status' => 'development',
        'database_ready' => isset($_ENV['DB_HOST'])
    ]);
});

$router->get('/api/matches', function() {
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'Matches API endpoint - Coming soon',
        'status' => 'development',
        'database_ready' => isset($_ENV['DB_HOST'])
    ]);
});

// 404 handler
$router->set404(function() {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode([
        'error' => 'Not Found',
        'message' => 'The requested resource was not found',
        'status' => 404
    ]);
});

// Run the router
$router->run();
