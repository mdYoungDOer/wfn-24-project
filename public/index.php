<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables (only if .env file exists)
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    error_log('No .env file found, using environment variables from platform');
}

// Start session
session_start();

// Initialize services
$apiService = new \WFN24\Services\FootballApiService();
$db = \WFN24\Config\Database::getInstance();
$authController = new \WFN24\Controllers\AuthController();
$adminController = new \WFN24\Controllers\AdminController();
$matchController = new \WFN24\Controllers\MatchController();
$leagueController = new \WFN24\Controllers\LeagueController();

// Fetch real data
$liveMatches = [];
$upcomingMatches = [];
$leagues = [];
$newsArticles = [];

try {
    // Fetch live matches
    $liveMatches = $apiService->getLiveMatches();
    
    // Fetch upcoming matches
    $upcomingMatches = $apiService->getUpcomingMatches(5);
    
    // Fetch major leagues
    $leagues = $apiService->getMajorLeagues();
    
    // Fetch news articles from database
    $stmt = $db->getConnection()->prepare(
        "SELECT * FROM news_articles WHERE is_published = TRUE ORDER BY published_at DESC LIMIT 6"
    );
    $stmt->execute();
    $newsArticles = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log('Error fetching data: ' . $e->getMessage());
}

// Simple routing
$router = new \Bramus\Router\Router();

// Public routes
$router->get('/', function() use ($liveMatches, $upcomingMatches, $leagues, $newsArticles) {
    // Generate dynamic HTML with real data
    $html = generateDynamicHTML($liveMatches, $upcomingMatches, $leagues, $newsArticles);
    
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
});

// Authentication routes
$router->post('/auth/register', function() use ($authController) {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $result = $authController->register($input);
    
    echo json_encode($result);
});

$router->post('/auth/login', function() use ($authController) {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $result = $authController->login($input['email'], $input['password']);
    
    echo json_encode($result);
});

$router->post('/auth/logout', function() use ($authController) {
    header('Content-Type: application/json');
    
    $result = $authController->logout();
    echo json_encode($result);
});

$router->get('/auth/user', function() use ($authController) {
    header('Content-Type: application/json');
    
    $user = $authController->getCurrentUser();
    echo json_encode(['user' => $user]);
});

// Admin routes (require authentication and admin privileges)
$router->get('/admin', function() use ($authController, $adminController) {
    if (!$authController->isAuthenticated()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    if (!$authController->isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden - Admin access required']);
        return;
    }
    
    header('Content-Type: application/json');
    $dashboard = $adminController->dashboard();
    echo json_encode($dashboard);
});

$router->get('/admin/articles', function() use ($authController, $adminController) {
    if (!$authController->isAuthenticated() || !$authController->isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    $page = $_GET['page'] ?? 1;
    header('Content-Type: application/json');
    $articles = $adminController->articles($page);
    echo json_encode($articles);
});

$router->post('/admin/articles', function() use ($authController, $adminController) {
    if (!$authController->isAuthenticated() || !$authController->isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    header('Content-Type: application/json');
    $result = $adminController->saveArticle($input);
    echo json_encode($result);
});

$router->put('/admin/articles/{id}', function($id) use ($authController, $adminController) {
    if (!$authController->isAuthenticated() || !$authController->isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    header('Content-Type: application/json');
    $result = $adminController->saveArticle($input, $id);
    echo json_encode($result);
});

$router->delete('/admin/articles/{id}', function($id) use ($authController, $adminController) {
    if (!$authController->isAuthenticated() || !$authController->isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    header('Content-Type: application/json');
    $result = $adminController->deleteArticle($id);
    echo json_encode($result);
});

$router->get('/admin/users', function() use ($authController, $adminController) {
    if (!$authController->isAuthenticated() || !$authController->isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    $page = $_GET['page'] ?? 1;
    header('Content-Type: application/json');
    $users = $adminController->users($page);
    echo json_encode($users);
});

$router->get('/admin/matches', function() use ($authController, $adminController) {
    if (!$authController->isAuthenticated() || !$authController->isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    $page = $_GET['page'] ?? 1;
    header('Content-Type: application/json');
    $matches = $adminController->matches($page);
    echo json_encode($matches);
});

$router->get('/admin/categories', function() use ($authController, $adminController) {
    if (!$authController->isAuthenticated() || !$authController->isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    header('Content-Type: application/json');
    $categories = $adminController->getCategories();
    echo json_encode($categories);
});

$router->get('/admin/teams', function() use ($authController, $adminController) {
    if (!$authController->isAuthenticated() || !$authController->isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    header('Content-Type: application/json');
    $teams = $adminController->getTeams();
    echo json_encode($teams);
});

$router->get('/admin/leagues', function() use ($authController, $adminController) {
    if (!$authController->isAuthenticated() || !$authController->isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    header('Content-Type: application/json');
    $leagues = $adminController->getLeagues();
    echo json_encode($leagues);
});

$router->put('/admin/users/{id}/status', function($id) use ($authController, $adminController) {
    if (!$authController->isAuthenticated() || !$authController->isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    header('Content-Type: application/json');
    $result = $adminController->updateUserStatus($id, $input['is_active'], $input['is_admin'] ?? null);
    echo json_encode($result);
});

$router->put('/admin/users/{id}/admin', function($id) use ($authController, $adminController) {
    if (!$authController->isAuthenticated() || !$authController->isAdmin()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    header('Content-Type: application/json');
    $result = $adminController->updateUserStatus($id, null, $input['is_admin']);
    echo json_encode($result);
});

// API routes
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

$router->get('/api/news', function() use ($newsArticles) {
    header('Content-Type: application/json');
    echo json_encode([
        'articles' => $newsArticles,
        'count' => count($newsArticles)
    ]);
});

$router->get('/api/matches', function() use ($liveMatches, $upcomingMatches) {
    header('Content-Type: application/json');
    echo json_encode([
        'live_matches' => $liveMatches,
        'upcoming_matches' => $upcomingMatches,
        'live_count' => count($liveMatches),
        'upcoming_count' => count($upcomingMatches)
    ]);
});

$router->get('/api/leagues', function() use ($leagues) {
    header('Content-Type: application/json');
    echo json_encode([
        'leagues' => $leagues,
        'count' => count($leagues)
    ]);
});

// Match routes
$router->get('/api/matches/{id}', function($id) use ($matchController) {
    header('Content-Type: application/json');
    $matchDetails = $matchController->getMatchDetails($id);
    echo json_encode($matchDetails);
});

$router->get('/api/matches/live', function() use ($matchController) {
    header('Content-Type: application/json');
    $liveMatches = $matchController->getLiveMatches();
    echo json_encode(['matches' => $liveMatches]);
});

$router->get('/api/matches/upcoming', function() use ($matchController) {
    header('Content-Type: application/json');
    $upcomingMatches = $matchController->getUpcomingMatches();
    echo json_encode(['matches' => $upcomingMatches]);
});

// League routes
$router->get('/api/leagues/{id}', function($id) use ($leagueController) {
    header('Content-Type: application/json');
    $leagueDetails = $leagueController->getLeagueDetails($id);
    echo json_encode($leagueDetails);
});

$router->get('/api/leagues/{id}/standings', function($id) use ($leagueController) {
    header('Content-Type: application/json');
    $standings = $leagueController->getLeagueStandings($id);
    echo json_encode(['standings' => $standings]);
});

$router->get('/api/leagues/{id}/top-scorers', function($id) use ($leagueController) {
    header('Content-Type: application/json');
    $topScorers = $leagueController->getTopScorers($id);
    echo json_encode(['top_scorers' => $topScorers]);
});

$router->get('/api/leagues/{id}/fixtures', function($id) use ($leagueController) {
    header('Content-Type: application/json');
    $page = $_GET['page'] ?? 1;
    $fixtures = $leagueController->getLeagueFixtures($id, $page);
    echo json_encode($fixtures);
});

$router->get('/api/leagues/{id}/recent-matches', function($id) use ($leagueController) {
    header('Content-Type: application/json');
    $recentMatches = $leagueController->getRecentMatches($id);
    echo json_encode(['matches' => $recentMatches]);
});

$router->get('/api/leagues/{id}/upcoming-matches', function($id) use ($leagueController) {
    header('Content-Type: application/json');
    $upcomingMatches = $leagueController->getUpcomingMatches($id);
    echo json_encode(['matches' => $upcomingMatches]);
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

function generateDynamicHTML($liveMatches, $upcomingMatches, $leagues, $newsArticles) {
    $liveMatchesHTML = '';
    $upcomingMatchesHTML = '';
    $leaguesHTML = '';
    $newsHTML = '';
    $featuredArticle = null;
    
    // Generate live matches HTML
    if (!empty($liveMatches)) {
        foreach (array_slice($liveMatches, 0, 3) as $match) {
            $liveMatchesHTML .= '
            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-red-500">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-red-600 bg-red-100 px-2 py-1 rounded">LIVE</span>
                    <span class="text-xs text-gray-500">' . $match['league']['name'] . '</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <img src="' . $match['home_team']['logo'] . '" alt="' . $match['home_team']['name'] . '" class="w-8 h-8">
                        <span class="font-semibold text-sm">' . $match['home_team']['name'] . '</span>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-gray-800">' . $match['home_score'] . ' - ' . $match['away_score'] . '</div>
                        <div class="text-xs text-gray-500">' . $match['status'] . '</div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="font-semibold text-sm">' . $match['away_team']['name'] . '</span>
                        <img src="' . $match['away_team']['logo'] . '" alt="' . $match['away_team']['name'] . '" class="w-8 h-8">
                    </div>
                </div>
                <button class="w-full mt-3 bg-red-600 text-white text-sm font-semibold py-2 px-4 rounded hover:bg-red-700 transition">
                    Follow Live
                </button>
            </div>';
        }
    } else {
        $liveMatchesHTML = '
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <div class="text-gray-400 mb-2">
                <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">No Live Matches</h3>
            <p class="text-gray-500 text-sm">Check back later for live football action!</p>
        </div>';
    }
    
    // Generate upcoming matches HTML
    if (!empty($upcomingMatches)) {
        foreach (array_slice($upcomingMatches, 0, 3) as $match) {
            $matchTime = date('H:i', strtotime($match['match_date']));
            $matchDate = date('M j', strtotime($match['match_date']));
            
            $upcomingMatchesHTML .= '
            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-2 py-1 rounded">UPCOMING</span>
                    <span class="text-xs text-gray-500">' . $match['league']['name'] . '</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <img src="' . $match['home_team']['logo'] . '" alt="' . $match['home_team']['name'] . '" class="w-8 h-8">
                        <span class="font-semibold text-sm">' . $match['home_team']['name'] . '</span>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-gray-800">' . $matchTime . '</div>
                        <div class="text-xs text-gray-500">' . $matchDate . '</div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="font-semibold text-sm">' . $match['away_team']['name'] . '</span>
                        <img src="' . $match['away_team']['logo'] . '" alt="' . $match['away_team']['name'] . '" class="w-8 h-8">
                    </div>
                </div>
                <button class="w-full mt-3 bg-blue-600 text-white text-sm font-semibold py-2 px-4 rounded hover:bg-blue-700 transition">
                    Set Reminder
                </button>
            </div>';
        }
    }
    
    // Generate leagues HTML
    if (!empty($leagues)) {
        foreach (array_slice($leagues, 0, 4) as $league) {
            $leaguesHTML .= '
            <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition cursor-pointer">
                <div class="flex items-center space-x-3">
                    <img src="' . $league['logo_url'] . '" alt="' . $league['name'] . '" class="w-12 h-12 rounded">
                    <div>
                        <h3 class="font-semibold text-gray-800">' . $league['name'] . '</h3>
                        <p class="text-sm text-gray-500">' . $league['country'] . '</p>
                    </div>
                </div>
            </div>';
        }
    }
    
    // Generate news HTML
    if (!empty($newsArticles)) {
        $featuredArticle = array_shift($newsArticles); // Get first article as featured
        
        foreach (array_slice($newsArticles, 0, 4) as $article) {
            $newsHTML .= '
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition cursor-pointer">
                <img src="' . $article['featured_image'] . '" alt="' . $article['title'] . '" class="w-full h-48 object-cover">
                <div class="p-4">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-xs font-semibold text-red-600 bg-red-100 px-2 py-1 rounded">BREAKING</span>
                        <span class="text-xs text-gray-500">' . number_format($article['view_count']) . ' views</span>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">' . $article['title'] . '</h3>
                    <p class="text-sm text-gray-600 line-clamp-2">' . $article['excerpt'] . '</p>
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-xs text-gray-500">' . $article['author_name'] . '</span>
                        <span class="text-xs text-gray-500">' . date('M j', strtotime($article['published_at'])) . '</span>
                    </div>
                </div>
            </div>';
        }
    }
    
    // Generate featured article HTML
    $featuredArticleHTML = '';
    if ($featuredArticle) {
        $featuredArticleHTML = '
        <div class="relative bg-gradient-to-r from-red-600 to-red-800 rounded-lg overflow-hidden">
            <div class="absolute inset-0 bg-black bg-opacity-40"></div>
            <img src="' . $featuredArticle['featured_image'] . '" alt="' . $featuredArticle['title'] . '" class="w-full h-96 object-cover">
            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                <div class="flex items-center space-x-2 mb-3">
                    <span class="text-xs font-semibold bg-red-600 px-3 py-1 rounded-full">FEATURED</span>
                    <span class="text-xs bg-white bg-opacity-20 px-3 py-1 rounded-full">BREAKING</span>
                </div>
                <h1 class="text-3xl font-bold mb-3">' . $featuredArticle['title'] . '</h1>
                <p class="text-lg mb-4 opacity-90">' . $featuredArticle['excerpt'] . '</p>
                <div class="flex items-center space-x-4">
                    <span class="text-sm opacity-75">By ' . $featuredArticle['author_name'] . '</span>
                    <span class="text-sm opacity-75">' . date('M j, Y', strtotime($featuredArticle['published_at'])) . '</span>
                    <span class="text-sm opacity-75">' . number_format($featuredArticle['view_count']) . ' views</span>
                </div>
            </div>
        </div>';
    }
    
    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WFN24 - World Football News 24 | Live Scores, News & Analysis</title>
    <meta name="description" content="Get the latest football news, live scores, match updates, and analysis from around the world. WFN24 brings you comprehensive coverage of Premier League, La Liga, Serie A, Bundesliga, and more.">
    <meta name="keywords" content="football, soccer, live scores, premier league, la liga, champions league, transfer news, football news">
    <meta name="author" content="WFN24">
    <meta property="og:title" content="WFN24 - World Football News 24">
    <meta property="og:description" content="Get the latest football news, live scores, and analysis from around the world.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://wfn24-project-qrml7.ondigitalocean.app">
    <meta property="og:image" content="https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=1200">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#e41e5b",
                        secondary: "#9a0864",
                        neutral: "#2c2c2c",
                        accent: "#746354",
                        highlight: "#a67c00"
                    }
                }
            }
        }
    </script>
    <style>
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .gradient-text { background: linear-gradient(135deg, #e41e5b, #9a0864); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-primary to-secondary rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">W</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold gradient-text">WFN24</h1>
                        <p class="text-xs text-gray-500">World Football News 24</p>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="hidden md:flex space-x-8">
                    <a href="#" class="text-gray-700 hover:text-primary font-medium">Home</a>
                    <a href="#" class="text-gray-700 hover:text-primary font-medium">News</a>
                    <a href="#" class="text-gray-700 hover:text-primary font-medium">Matches</a>
                    <a href="#" class="text-gray-700 hover:text-primary font-medium">Leagues</a>
                    <a href="#" class="text-gray-700 hover:text-primary font-medium">Teams</a>
                    <a href="#" class="text-gray-700 hover:text-primary font-medium">Players</a>
                </nav>
                
                <!-- Search -->
                <div class="hidden md:flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" placeholder="Search..." class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <button id="loginBtn" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Login</button>
                    <button id="signupBtn" class="bg-secondary text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">Sign Up</button>
                    <button id="adminBtn" class="bg-neutral text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition hidden">Admin</button>
                </div>
                
                <!-- Mobile menu button -->
                <button class="md:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Hero Section -->
        <section class="mb-12">
            ' . $featuredArticleHTML . '
        </section>

        <!-- Live Matches Section -->
        <section class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Live Matches</h2>
                <a href="#" class="text-primary hover:text-red-700 font-semibold">View All</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ' . $liveMatchesHTML . '
            </div>
        </section>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Latest News -->
                <section class="mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Latest News</h2>
                        <a href="#" class="text-primary hover:text-red-700 font-semibold">View All</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        ' . $newsHTML . '
                    </div>
                </section>

                <!-- Upcoming Matches -->
                <section class="mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Upcoming Matches</h2>
                        <a href="#" class="text-primary hover:text-red-700 font-semibold">View All</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        ' . $upcomingMatchesHTML . '
                    </div>
                </section>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Live Matches Widget -->
                <section class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Live Now</h3>
                    <div class="space-y-4">
                        ' . (!empty($liveMatches) ? '
                        <div class="border-b border-gray-200 pb-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-red-600 font-semibold">LIVE</span>
                                <span class="text-gray-500">' . $liveMatches[0]['league']['name'] . '</span>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center space-x-2">
                                    <img src="' . $liveMatches[0]['home_team']['logo'] . '" alt="' . $liveMatches[0]['home_team']['name'] . '" class="w-6 h-6">
                                    <span class="text-sm font-medium">' . $liveMatches[0]['home_team']['name'] . '</span>
                                </div>
                                <span class="font-bold">' . $liveMatches[0]['home_score'] . ' - ' . $liveMatches[0]['away_score'] . '</span>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium">' . $liveMatches[0]['away_team']['name'] . '</span>
                                    <img src="' . $liveMatches[0]['away_team']['logo'] . '" alt="' . $liveMatches[0]['away_team']['name'] . '" class="w-6 h-6">
                                </div>
                            </div>
                        </div>' : '<p class="text-gray-500 text-sm">No live matches at the moment</p>') . '
                    </div>
                </section>

                <!-- Major Leagues -->
                <section class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Major Leagues</h3>
                    <div class="space-y-4">
                        ' . $leaguesHTML . '
                    </div>
                </section>

                <!-- Stats -->
                <section class="bg-gradient-to-r from-primary to-secondary rounded-lg p-6 text-white">
                    <h3 class="text-lg font-semibold mb-4">WFN24 Stats</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span>Live Matches</span>
                            <span class="font-semibold">' . count($liveMatches) . '</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Upcoming</span>
                            <span class="font-semibold">' . count($upcomingMatches) . '</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Leagues</span>
                            <span class="font-semibold">' . count($leagues) . '</span>
                        </div>
                        <div class="flex justify-between">
                            <span>News Articles</span>
                            <span class="font-semibold">' . (count($newsArticles) + ($featuredArticle ? 1 : 0)) . '</span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-neutral text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-r from-primary to-secondary rounded flex items-center justify-center">
                            <span class="text-white font-bold">W</span>
                        </div>
                        <span class="text-xl font-bold">WFN24</span>
                    </div>
                    <p class="text-gray-300 text-sm">Your ultimate destination for world football news, live scores, and comprehensive coverage of the beautiful game.</p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li><a href="#" class="hover:text-white transition">Home</a></li>
                        <li><a href="#" class="hover:text-white transition">News</a></li>
                        <li><a href="#" class="hover:text-white transition">Matches</a></li>
                        <li><a href="#" class="hover:text-white transition">Leagues</a></li>
                        <li><a href="#" class="hover:text-white transition">Teams</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Leagues</h4>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li><a href="#" class="hover:text-white transition">Premier League</a></li>
                        <li><a href="#" class="hover:text-white transition">La Liga</a></li>
                        <li><a href="#" class="hover:text-white transition">Serie A</a></li>
                        <li><a href="#" class="hover:text-white transition">Bundesliga</a></li>
                        <li><a href="#" class="hover:text-white transition">Ligue 1</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Connect</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-white transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-300 text-sm">&copy; 2025 WFN24. All rights reserved. | Powered by real-time football data from API-Football</p>
            </div>
        </div>
    </footer>

    <script>
        // Check authentication status on page load
        fetch("/auth/user")
            .then(response => response.json())
            .then(data => {
                if (data.user) {
                    // User is logged in
                    document.getElementById("loginBtn").style.display = "none";
                    document.getElementById("signupBtn").style.display = "none";
                    
                    if (data.user.is_admin) {
                        document.getElementById("adminBtn").classList.remove("hidden");
                    }
                }
            })
            .catch(error => console.error("Error checking auth status:", error));

        // Auto-refresh live data every 30 seconds
        setInterval(function() {
            fetch("/api/matches")
                .then(response => response.json())
                .then(data => {
                    console.log("Live data updated:", data);
                })
                .catch(error => console.error("Error updating live data:", error));
        }, 30000);
    </script>
</body>
</html>';
}
