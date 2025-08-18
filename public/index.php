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
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'WFN24 - World Football News 24',
        'status' => 'running',
        'version' => '1.0.0',
        'environment' => $_ENV['APP_ENV'] ?? 'production',
        'features' => [
            'news_articles' => 'Available',
            'live_matches' => 'Available',
            'league_tables' => 'Available',
            'team_profiles' => 'Available',
            'player_profiles' => 'Available',
            'search' => 'Available',
            'admin_dashboard' => 'Available'
        ],
        'database' => [
            'host' => $_ENV['DB_HOST'] ?? 'not_set',
            'database' => $_ENV['DB_NAME'] ?? 'not_set',
            'status' => isset($_ENV['DB_HOST']) ? 'configured' : 'not_configured'
        ]
    ]);
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
