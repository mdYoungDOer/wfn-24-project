<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

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
        'features' => [
            'news_articles' => 'Available',
            'live_matches' => 'Available',
            'league_tables' => 'Available',
            'team_profiles' => 'Available',
            'player_profiles' => 'Available',
            'search' => 'Available',
            'admin_dashboard' => 'Available'
        ]
    ]);
});

$router->get('/health', function() {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'environment' => $_ENV['APP_ENV'] ?? 'production'
    ]);
});

$router->get('/api/news', function() {
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'News API endpoint - Coming soon',
        'status' => 'development'
    ]);
});

$router->get('/api/matches', function() {
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'Matches API endpoint - Coming soon',
        'status' => 'development'
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
