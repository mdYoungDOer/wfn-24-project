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

// Initialize Inertia
use WFN24\Inertia\Inertia;

// Simple routing
$router = new \Bramus\Router\Router();

// Public routes
$router->get('/', function() {
    // Get some sample data for the homepage
    $featuredArticles = [
        [
            'id' => 1,
            'title' => 'Premier League Title Race Heats Up',
            'excerpt' => 'The race for the Premier League title is reaching its climax with multiple teams in contention.',
            'featured_image' => '/images/placeholder-article.jpg',
            'category' => ['name' => 'Breaking News', 'color' => '#e41e5b'],
            'author_name' => 'WFN24 Staff',
            'published_at' => date('Y-m-d H:i:s'),
            'view_count' => 1250,
            'is_featured' => true
        ],
        [
            'id' => 2,
            'title' => 'Champions League Quarter-Finals Preview',
            'excerpt' => 'Eight teams remain in the hunt for European football\'s biggest prize.',
            'featured_image' => '/images/placeholder-article.jpg',
            'category' => ['name' => 'Match Reports', 'color' => '#746354'],
            'author_name' => 'WFN24 Staff',
            'published_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'view_count' => 890,
            'is_featured' => false
        ]
    ];

    $liveMatches = [
        [
            'id' => 1,
            'status' => 'LIVE',
            'home_team' => ['name' => 'Manchester United', 'logo' => '/images/placeholder-team.png'],
            'away_team' => ['name' => 'Liverpool', 'logo' => '/images/placeholder-team.png'],
            'home_score' => 2,
            'away_score' => 1,
            'league' => ['name' => 'Premier League'],
            'match_date' => date('Y-m-d H:i:s'),
            'is_live' => true
        ]
    ];

    $upcomingMatches = [
        [
            'id' => 2,
            'status' => 'SCHEDULED',
            'home_team' => ['name' => 'Arsenal', 'logo' => '/images/placeholder-team.png'],
            'away_team' => ['name' => 'Chelsea', 'logo' => '/images/placeholder-team.png'],
            'match_date' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'league' => ['name' => 'Premier League'],
            'venue' => 'Emirates Stadium'
        ],
        [
            'id' => 3,
            'status' => 'SCHEDULED',
            'home_team' => ['name' => 'Barcelona', 'logo' => '/images/placeholder-team.png'],
            'away_team' => ['name' => 'Real Madrid', 'logo' => '/images/placeholder-team.png'],
            'match_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'league' => ['name' => 'La Liga'],
            'venue' => 'Camp Nou'
        ]
    ];

    $majorLeagues = [
        ['id' => 1, 'name' => 'Premier League', 'country' => 'England', 'logo' => '/images/placeholder-league.png'],
        ['id' => 2, 'name' => 'La Liga', 'country' => 'Spain', 'logo' => '/images/placeholder-league.png'],
        ['id' => 3, 'name' => 'Bundesliga', 'country' => 'Germany', 'logo' => '/images/placeholder-league.png'],
        ['id' => 4, 'name' => 'Serie A', 'country' => 'Italy', 'logo' => '/images/placeholder-league.png'],
        ['id' => 5, 'name' => 'Ligue 1', 'country' => 'France', 'logo' => '/images/placeholder-league.png'],
        ['id' => 6, 'name' => 'Champions League', 'country' => 'Europe', 'logo' => '/images/placeholder-league.png']
    ];

    return Inertia::render('Home', [
        'featuredArticles' => $featuredArticles,
        'latestNews' => array_slice($featuredArticles, 0, 6),
        'liveMatches' => $liveMatches,
        'upcomingMatches' => $upcomingMatches,
        'majorLeagues' => $majorLeagues,
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
