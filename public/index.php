<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Inertia\Inertia;
use Bramus\Router\Router;
use WFN24\Controllers\HomeController;
use WFN24\Controllers\NewsController;
use WFN24\Controllers\MatchController;
use WFN24\Controllers\TeamController;
use WFN24\Controllers\PlayerController;
use WFN24\Controllers\LeagueController;
use WFN24\Controllers\AuthController;
use WFN24\Controllers\AdminController;
use WFN24\Controllers\ApiController;
use WFN24\Middleware\AuthMiddleware;
use WFN24\Middleware\AdminMiddleware;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Start session
session_start();

// Configure Inertia
Inertia::share([
    'auth' => [
        'user' => $_SESSION['user'] ?? null,
    ],
    'flash' => [
        'message' => $_SESSION['flash_message'] ?? null,
        'error' => $_SESSION['flash_error'] ?? null,
    ],
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'WFN24',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
    ]
]);

// Clear flash messages
unset($_SESSION['flash_message'], $_SESSION['flash_error']);

// Create router
$router = new Router();

// Middleware
$authMiddleware = new AuthMiddleware();
$adminMiddleware = new AdminMiddleware();

// Public routes
$router->get('/', [HomeController::class, 'index']);
$router->get('/news', [NewsController::class, 'index']);
$router->get('/news/{slug}', [NewsController::class, 'show']);
$router->get('/news/category/{slug}', [NewsController::class, 'category']);
$router->get('/matches', [MatchController::class, 'index']);
$router->get('/match/{id}', [MatchController::class, 'show']);
$router->get('/teams', [TeamController::class, 'index']);
$router->get('/team/{id}', [TeamController::class, 'show']);
$router->get('/players', [PlayerController::class, 'index']);
$router->get('/player/{id}', [PlayerController::class, 'show']);
$router->get('/leagues', [LeagueController::class, 'index']);
$router->get('/league/{id}', [LeagueController::class, 'show']);
$router->get('/search', [HomeController::class, 'search']);

// Authentication routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
$router->before('GET|POST', '/profile', function() use ($authMiddleware) {
    $authMiddleware->handle();
});
$router->get('/profile', [AuthController::class, 'profile']);
$router->post('/profile', [AuthController::class, 'updateProfile']);

// Admin routes
$router->before('GET|POST', '/admin/*', function() use ($authMiddleware, $adminMiddleware) {
    $authMiddleware->handle();
    $adminMiddleware->handle();
});

$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/news', [AdminController::class, 'news']);
$router->get('/admin/news/create', [AdminController::class, 'createNews']);
$router->post('/admin/news', [AdminController::class, 'storeNews']);
$router->get('/admin/news/{id}/edit', [AdminController::class, 'editNews']);
$router->post('/admin/news/{id}', [AdminController::class, 'updateNews']);
$router->post('/admin/news/{id}/delete', [AdminController::class, 'deleteNews']);

$router->get('/admin/matches', [AdminController::class, 'matches']);
$router->get('/admin/teams', [AdminController::class, 'teams']);
$router->get('/admin/players', [AdminController::class, 'players']);
$router->get('/admin/leagues', [AdminController::class, 'leagues']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/settings', [AdminController::class, 'settings']);

// API routes
$router->get('/api/live-matches', [ApiController::class, 'liveMatches']);
$router->get('/api/upcoming-matches', [ApiController::class, 'upcomingMatches']);
$router->get('/api/league-standings/{id}', [ApiController::class, 'leagueStandings']);
$router->get('/api/team-matches/{id}', [ApiController::class, 'teamMatches']);
$router->get('/api/search', [ApiController::class, 'search']);

// WebSocket endpoint for live updates
$router->get('/ws', function() {
    // WebSocket upgrade will be handled by a separate WebSocket server
    header('HTTP/1.1 101 Switching Protocols');
    header('Upgrade: websocket');
    header('Connection: Upgrade');
    exit;
});

// 404 handler
$router->set404(function() {
    http_response_code(404);
    Inertia::render('Error', [
        'status' => 404,
        'message' => 'Page not found'
    ]);
});

// Run the router
$router->run();
