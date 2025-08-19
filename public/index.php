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

// Fetch real data with error handling and fallbacks
$liveMatches = [];
$upcomingMatches = [];
$leagues = [];
$newsArticles = [];

try {
    // Fetch live matches from database with timeout protection
    $stmt = $db->getConnection()->prepare(
        "SELECT m.*, 
                ht.name as home_team_name, ht.logo_url as home_team_logo,
                at.name as away_team_name, at.logo_url as away_team_logo,
                l.name as league_name, l.logo_url as league_logo
         FROM matches m
         LEFT JOIN teams ht ON m.home_team_id = ht.id
         LEFT JOIN teams at ON m.away_team_id = at.id
         LEFT JOIN leagues l ON m.league_id = l.id
         WHERE m.status = 'LIVE' OR m.status = 'HT' OR m.status = '2H'
         ORDER BY m.match_date DESC LIMIT 3"
    );
    $stmt->execute();
    $liveMatches = $stmt->fetchAll();
    
    // Fetch upcoming matches from database
    $stmt = $db->getConnection()->prepare(
        "SELECT m.*, 
                ht.name as home_team_name, ht.logo_url as home_team_logo,
                at.name as away_team_name, at.logo_url as away_team_logo,
                l.name as league_name, l.logo_url as league_logo
         FROM matches m
         LEFT JOIN teams ht ON m.home_team_id = ht.id
         LEFT JOIN teams at ON m.away_team_id = at.id
         LEFT JOIN leagues l ON m.league_id = l.id
         WHERE m.match_date > NOW() AND m.status = 'SCHEDULED'
         ORDER BY m.match_date ASC LIMIT 3"
    );
    $stmt->execute();
    $upcomingMatches = $stmt->fetchAll();
    
    // Fetch leagues from database
    $stmt = $db->getConnection()->prepare(
        "SELECT * FROM leagues WHERE is_active = TRUE ORDER BY name LIMIT 4"
    );
    $stmt->execute();
    $leagues = $stmt->fetchAll();
    
    // Fetch news articles from database
    $stmt = $db->getConnection()->prepare(
        "SELECT * FROM news_articles WHERE is_published = TRUE ORDER BY published_at DESC LIMIT 4"
    );
    $stmt->execute();
    $newsArticles = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log('Error fetching data: ' . $e->getMessage());
    // Use fallback data if database fails
    $liveMatches = [];
    $upcomingMatches = [];
    $leagues = [
        ['name' => 'Premier League', 'country' => 'England', 'logo_url' => null],
        ['name' => 'La Liga', 'country' => 'Spain', 'logo_url' => null],
        ['name' => 'Serie A', 'country' => 'Italy', 'logo_url' => null],
        ['name' => 'Bundesliga', 'country' => 'Germany', 'logo_url' => null]
    ];
    $newsArticles = [
        [
            'title' => 'Premier League Title Race Reaches Climax',
            'excerpt' => 'The Premier League title race is reaching its most dramatic conclusion in years.',
            'featured_image' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=1200&h=600&fit=crop',
            'author_name' => 'WFN24 Staff',
            'published_at' => date('Y-m-d H:i:s'),
            'view_count' => 15420
        ]
    ];
}

// Simple routing
$router = new \Bramus\Router\Router();

// Public routes
$router->get('/', function() use ($liveMatches, $upcomingMatches, $leagues, $newsArticles) {
    // Set timeout to prevent infinite loading
    set_time_limit(30);
    
    try {
        // Generate dynamic HTML with real data
        $html = generateDynamicHTML($liveMatches, $upcomingMatches, $leagues, $newsArticles);
        
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    } catch (Exception $e) {
        error_log('Error generating HTML: ' . $e->getMessage());
        // Fallback to simple HTML if generation fails
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>WFN24 - World Football News 24</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body>
            <h1>WFN24 - World Football News 24</h1>
            <p>Loading... Please wait.</p>
            <script>setTimeout(function(){ window.location.reload(); }, 5000);</script>
        </body>
        </html>';
    }
});

$router->get('/admin', function() {
    // Serve the admin HTML page
    $adminHtml = file_get_contents(__DIR__ . '/admin.html');
    header('Content-Type: text/html; charset=utf-8');
    echo $adminHtml;
});

// Health check route
$router->get('/health', function() {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => 'WFN24 server is running'
    ]);
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
            $homeLogo = $match['home_team_logo'] ?: 'https://via.placeholder.com/48x48/4F46E5/FFFFFF?text=' . substr($match['home_team_name'], 0, 2);
            $awayLogo = $match['away_team_logo'] ?: 'https://via.placeholder.com/48x48/DC2626/FFFFFF?text=' . substr($match['away_team_name'], 0, 2);
            
            $liveMatchesHTML .= '
            <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-red-500 match-card hover-lift">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full live-indicator"></div>
                        <span class="text-sm font-bold text-red-600 bg-red-50 px-3 py-1 rounded-full">LIVE</span>
                    </div>
                    <span class="text-xs text-gray-500 font-medium truncate max-w-24">' . htmlspecialchars($match['league_name'] ?? 'Unknown League') . '</span>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3 text-center w-full">
                        <div class="flex flex-col items-center flex-1">
                            <div class="relative mb-2">
                                <img src="' . htmlspecialchars($homeLogo) . '" alt="' . htmlspecialchars($match['home_team_name']) . '" class="w-12 h-12 rounded-full shadow-md object-cover" onerror="this.src=\'https://via.placeholder.com/48x48/4F46E5/FFFFFF?text=' . substr($match['home_team_name'], 0, 2) . '\'">
                            </div>
                            <span class="font-bold text-sm text-gray-800 line-clamp-2 text-center">' . htmlspecialchars($match['home_team_name']) . '</span>
                        </div>
                        <div class="text-center mx-2 flex-shrink-0">
                            <div class="text-2xl md:text-3xl font-bold text-gray-800 score-animation">' . ($match['home_score'] ?? 0) . ' - ' . ($match['away_score'] ?? 0) . '</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">' . htmlspecialchars($match['status'] ?? 'LIVE') . '</div>
                        </div>
                        <div class="flex flex-col items-center flex-1">
                            <div class="relative mb-2">
                                <img src="' . htmlspecialchars($awayLogo) . '" alt="' . htmlspecialchars($match['away_team_name']) . '" class="w-12 h-12 rounded-full shadow-md object-cover" onerror="this.src=\'https://via.placeholder.com/48x48/DC2626/FFFFFF?text=' . substr($match['away_team_name'], 0, 2) . '\'">
                            </div>
                            <span class="font-bold text-sm text-gray-800 line-clamp-2 text-center">' . htmlspecialchars($match['away_team_name']) . '</span>
                        </div>
                    </div>
                </div>
                <button class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-bold py-3 px-4 rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-md hover:shadow-lg">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Follow Live
                </button>
            </div>';
        }
    } else {
        $liveMatchesHTML = '
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center hover-lift">
            <div class="text-gray-300 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-700 mb-3">No Live Matches</h3>
            <p class="text-gray-500 text-sm mb-4">Check back later for live football action!</p>
            <div class="w-16 h-1 bg-gradient-to-r from-primary to-secondary rounded-full mx-auto"></div>
        </div>';
    }
    
    // Generate upcoming matches HTML
    if (!empty($upcomingMatches)) {
        foreach (array_slice($upcomingMatches, 0, 3) as $match) {
            $matchTime = date('H:i', strtotime($match['match_date']));
            $matchDate = date('M j', strtotime($match['match_date']));
            $homeLogo = $match['home_team_logo'] ?: 'https://via.placeholder.com/32x32?text=' . substr($match['home_team_name'], 0, 2);
            $awayLogo = $match['away_team_logo'] ?: 'https://via.placeholder.com/32x32?text=' . substr($match['away_team_name'], 0, 2);
            
            $upcomingMatchesHTML .= '
            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-2 py-1 rounded">UPCOMING</span>
                    <span class="text-xs text-gray-500">' . htmlspecialchars($match['league_name'] ?? 'Unknown League') . '</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <img src="' . htmlspecialchars($homeLogo) . '" alt="' . htmlspecialchars($match['home_team_name']) . '" class="w-8 h-8 rounded">
                        <span class="font-semibold text-sm">' . htmlspecialchars($match['home_team_name']) . '</span>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-gray-800">' . $matchTime . '</div>
                        <div class="text-xs text-gray-500">' . $matchDate . '</div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="font-semibold text-sm">' . htmlspecialchars($match['away_team_name']) . '</span>
                        <img src="' . htmlspecialchars($awayLogo) . '" alt="' . htmlspecialchars($match['away_team_name']) . '" class="w-8 h-8 rounded">
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
            $logoUrl = $league['logo_url'] ?: 'https://via.placeholder.com/40x40/4F46E5/FFFFFF?text=' . substr($league['name'], 0, 2);
            $leaguesHTML .= '
            <div class="bg-white rounded-lg shadow-md p-3 md:p-4 hover:shadow-lg transition cursor-pointer">
                <div class="flex items-center space-x-2 md:space-x-3">
                    <img src="' . htmlspecialchars($logoUrl) . '" alt="' . htmlspecialchars($league['name']) . '" class="w-10 h-10 md:w-12 md:h-12 rounded object-cover" onerror="this.src=\'https://via.placeholder.com/40x40/4F46E5/FFFFFF?text=' . substr($league['name'], 0, 2) . '\'">
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-gray-800 text-sm md:text-base truncate">' . htmlspecialchars($league['name']) . '</h3>
                        <p class="text-xs md:text-sm text-gray-500 truncate">' . htmlspecialchars($league['country'] ?? 'Unknown') . '</p>
                    </div>
                </div>
            </div>';
        }
    }
    
    // Generate news HTML
    if (!empty($newsArticles)) {
        $featuredArticle = array_shift($newsArticles); // Get first article as featured
        
        foreach (array_slice($newsArticles, 0, 4) as $article) {
            $defaultImage = 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=600&h=400&fit=crop';
            $articleImage = $article['featured_image'] ?: $defaultImage;
            
            $newsHTML .= '
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden news-card hover-lift cursor-pointer">
                <div class="relative">
                    <img src="' . htmlspecialchars($articleImage) . '" alt="' . htmlspecialchars($article['title']) . '" class="w-full h-48 md:h-56 object-cover" onerror="this.src=\'' . $defaultImage . '\'">
                    <div class="absolute top-4 left-4">
                        <span class="text-xs font-bold text-white bg-gradient-to-r from-primary to-secondary px-3 py-1 rounded-full shadow-lg">BREAKING</span>
                    </div>
                    <div class="absolute top-4 right-4">
                        <div class="bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded-full backdrop-blur-sm">
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                            ' . number_format($article['view_count']) . '
                        </div>
                    </div>
                </div>
                <div class="p-4 md:p-6">
                    <h3 class="font-bold text-gray-800 mb-3 line-clamp-2 text-base md:text-lg leading-tight">' . htmlspecialchars($article['title']) . '</h3>
                    <p class="text-sm text-gray-600 line-clamp-3 mb-4 leading-relaxed">' . htmlspecialchars($article['excerpt']) . '</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 bg-gradient-to-r from-primary to-secondary rounded-full flex items-center justify-center">
                                <span class="text-white text-xs font-bold">' . substr($article['author_name'], 0, 1) . '</span>
                            </div>
                            <span class="text-xs text-gray-600 font-medium truncate">' . htmlspecialchars($article['author_name']) . '</span>
                        </div>
                        <span class="text-xs text-gray-500 font-medium">' . date('M j', strtotime($article['published_at'])) . '</span>
                    </div>
                </div>
            </div>';
        }
    }
    
    // Generate featured article HTML
    $featuredArticleHTML = '';
    if ($featuredArticle) {
        $defaultFeaturedImage = 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=1200&h=600&fit=crop';
        $featuredImage = $featuredArticle['featured_image'] ?: $defaultFeaturedImage;
        
        $featuredArticleHTML = '
        <div class="relative bg-gradient-to-r from-primary to-secondary rounded-3xl overflow-hidden shadow-2xl hover-lift">
            <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-black/40"></div>
            <img src="' . htmlspecialchars($featuredImage) . '" alt="' . htmlspecialchars($featuredArticle['title']) . '" class="w-full h-[400px] md:h-[500px] object-cover" onerror="this.src=\'' . $defaultFeaturedImage . '\'">
            <div class="absolute bottom-0 left-0 right-0 p-4 md:p-8 text-white">
                <div class="flex flex-wrap items-center gap-2 md:gap-3 mb-4">
                    <span class="text-sm font-bold bg-gradient-to-r from-primary to-secondary px-3 md:px-4 py-2 rounded-full shadow-lg">FEATURED</span>
                    <span class="text-sm bg-white/20 backdrop-blur-sm px-3 md:px-4 py-2 rounded-full">BREAKING NEWS</span>
                    <div class="bg-black/50 backdrop-blur-sm px-2 md:px-3 py-2 rounded-full">
                        <svg class="w-3 h-3 md:w-4 md:h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                        ' . number_format($featuredArticle['view_count']) . ' views
                    </div>
                </div>
                <h1 class="text-2xl md:text-4xl font-bold mb-4 leading-tight">' . htmlspecialchars($featuredArticle['title']) . '</h1>
                <p class="text-base md:text-xl mb-6 opacity-90 leading-relaxed max-w-3xl">' . htmlspecialchars($featuredArticle['excerpt']) . '</p>
                <div class="flex flex-col md:flex-row md:items-center md:space-x-6 space-y-2 md:space-y-0">
                    <div class="flex items-center space-x-2">
                        <div class="w-6 h-6 md:w-8 md:h-8 bg-gradient-to-r from-primary to-secondary rounded-full flex items-center justify-center">
                            <span class="text-white text-xs md:text-sm font-bold">' . substr($featuredArticle['author_name'], 0, 1) . '</span>
                        </div>
                        <span class="text-sm opacity-90 font-medium">By ' . htmlspecialchars($featuredArticle['author_name']) . '</span>
                    </div>
                    <span class="text-sm opacity-75">' . date('M j, Y', strtotime($featuredArticle['published_at'])) . '</span>
                </div>
                <button class="mt-6 bg-white text-primary px-4 md:px-6 py-3 rounded-xl font-bold hover:bg-gray-100 transition-all duration-200 shadow-lg hover:shadow-xl">
                    Read Full Story
                </button>
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
    <link rel="apple-touch-icon" href="/favicon.ico">
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
                    },
                    fontFamily: {
                        "inter": ["Inter", "sans-serif"]
                    },
                    animation: {
                        "pulse-slow": "pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite",
                        "bounce-slow": "bounce 2s infinite"
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: "Inter", sans-serif; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .gradient-text { background: linear-gradient(135deg, #e41e5b, #9a0864); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .gradient-bg { background: linear-gradient(135deg, #e41e5b, #9a0864); }
        .glass-effect { backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.1); }
        .hover-lift { transition: all 0.3s ease; }
        .hover-lift:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .live-indicator { animation: pulse-slow 2s infinite; }
        .score-animation { animation: bounce-slow 1s; }
        .news-card { transition: all 0.3s ease; }
        .news-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); }
        .match-card { transition: all 0.3s ease; }
        .match-card:hover { transform: scale(1.02); }
        .league-card { transition: all 0.3s ease; }
        .league-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .hover-lift:hover { transform: none; }
            .news-card:hover { transform: none; }
            .match-card:hover { transform: none; }
            .league-card:hover { transform: none; }
        }
        
        /* Image loading improvements */
        img { 
            background: linear-gradient(45deg, #f3f4f6, #e5e7eb);
            background-size: 200% 200%;
            animation: shimmer 2s infinite;
        }
        
        img[src*="placeholder"] {
            animation: none;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        /* Better text truncation */
        .truncate { 
            overflow: hidden; 
            text-overflow: ellipsis; 
            white-space: nowrap; 
        }
        
        /* Improved focus states */
        input:focus, button:focus {
            outline: none;
            ring: 2px;
            ring-color: #e41e5b;
        }
        
        /* Better mobile touch targets */
        @media (max-width: 768px) {
            button, a {
                min-height: 44px;
                min-width: 44px;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-lg border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-primary rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="12" r="8" fill="url(#gradient)"/>
                                <path d="M12 4c-4.4 0-8 3.6-8 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6z" fill="white" opacity="0.3"/>
                                <ellipse cx="12" cy="12" rx="10" ry="2" fill="white" opacity="0.2" transform="rotate(45 12 12)"/>
                            </svg>
                        </div>
                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full live-indicator"></div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold gradient-text tracking-tight">WFN24</h1>
                        <p class="text-sm text-gray-600 font-medium">World Football News 24</p>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="hidden lg:flex space-x-8">
                    <a href="#" class="text-gray-700 hover:text-primary font-semibold transition-colors duration-200 flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Home</span>
                    </a>
                    <a href="#" class="text-gray-700 hover:text-primary font-semibold transition-colors duration-200 flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                        <span>News</span>
                    </a>
                    <a href="#" class="text-gray-700 hover:text-primary font-semibold transition-colors duration-200 flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>Live Matches</span>
                    </a>
                    <a href="#" class="text-gray-700 hover:text-primary font-semibold transition-colors duration-200 flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Leagues</span>
                    </a>
                    <a href="#" class="text-gray-700 hover:text-primary font-semibold transition-colors duration-200 flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Teams</span>
                    </a>
                </nav>
                
                <!-- Search & Actions -->
                <div class="hidden lg:flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" placeholder="Search news, teams, players..." class="w-72 pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-gray-50 hover:bg-white transition-colors">
                        <svg class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <button id="loginBtn" class="bg-gradient-to-r from-primary to-secondary text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-200 font-semibold hover-lift">Login</button>
                    <button id="signupBtn" class="bg-white text-primary border-2 border-primary px-6 py-3 rounded-xl hover:bg-primary hover:text-white transition-all duration-200 font-semibold hover-lift">Sign Up</button>
                    <button id="adminBtn" class="bg-neutral text-white px-6 py-3 rounded-xl hover:bg-gray-700 transition-all duration-200 font-semibold hover-lift hidden">Admin</button>
                </div>
                
                <!-- Mobile menu button -->
                <button class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Hero Section -->
        <section class="mb-16">
            ' . $featuredArticleHTML . '
        </section>

        <!-- Live Matches Section -->
        <section class="mb-16">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-3">
                    <div class="w-1 h-8 bg-gradient-to-r from-primary to-secondary rounded-full"></div>
                    <h2 class="text-3xl font-bold text-gray-800">Live Matches</h2>
                    <div class="w-3 h-3 bg-red-500 rounded-full live-indicator"></div>
                </div>
                <a href="#" class="text-primary hover:text-secondary font-semibold flex items-center space-x-2 transition-colors">
                    <span>View All</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                ' . $liveMatchesHTML . '
            </div>
        </section>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Latest News -->
                <section class="mb-12">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center space-x-3">
                            <div class="w-1 h-8 bg-gradient-to-r from-primary to-secondary rounded-full"></div>
                            <h2 class="text-3xl font-bold text-gray-800">Latest News</h2>
                        </div>
                        <a href="#" class="text-primary hover:text-secondary font-semibold flex items-center space-x-2 transition-colors">
                            <span>View All</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        ' . $newsHTML . '
                    </div>
                </section>

                <!-- Transfer News Section -->
                <section class="mb-12">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center space-x-3">
                            <div class="w-1 h-8 bg-gradient-to-r from-primary to-secondary rounded-full"></div>
                            <h2 class="text-3xl font-bold text-gray-800">Transfer News</h2>
                        </div>
                        <a href="#" class="text-primary hover:text-secondary font-semibold flex items-center space-x-2 transition-colors">
                            <span>View All</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden news-card hover-lift">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600&h=300&fit=crop" alt="Transfer News" class="w-full h-48 object-cover">
                                <div class="absolute top-4 left-4">
                                    <span class="text-xs font-bold text-white bg-gradient-to-r from-green-500 to-green-600 px-3 py-1 rounded-full shadow-lg">TRANSFER</span>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="font-bold text-gray-800 mb-3 text-lg leading-tight">Haaland Set for Real Madrid Move in Summer 2024</h3>
                                <p class="text-sm text-gray-600 mb-4 leading-relaxed">Manchester City striker Erling Haaland is reportedly considering a move to Real Madrid next summer, with the Spanish giants ready to activate his release clause.</p>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-xs font-bold">T</span>
                                        </div>
                                        <span class="text-xs text-gray-600 font-medium">Transfer Desk</span>
                                    </div>
                                    <span class="text-xs text-gray-500 font-medium">2 hours ago</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden news-card hover-lift">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1552318965-6e6be7484ada?w=600&h=300&fit=crop" alt="Transfer News" class="w-full h-48 object-cover">
                                <div class="absolute top-4 left-4">
                                    <span class="text-xs font-bold text-white bg-gradient-to-r from-blue-500 to-blue-600 px-3 py-1 rounded-full shadow-lg">RUMOR</span>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="font-bold text-gray-800 mb-3 text-lg leading-tight">Mbappé Contract Extension Talks Intensify</h3>
                                <p class="text-sm text-gray-600 mb-4 leading-relaxed">PSG and Kylian Mbappé are in advanced talks for a contract extension, with the French forward reportedly close to signing a new long-term deal.</p>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-xs font-bold">F</span>
                                        </div>
                                        <span class="text-xs text-gray-600 font-medium">French Football</span>
                                    </div>
                                    <span class="text-xs text-gray-500 font-medium">4 hours ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Upcoming Matches -->
                <section class="mb-12">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center space-x-3">
                            <div class="w-1 h-8 bg-gradient-to-r from-primary to-secondary rounded-full"></div>
                            <h2 class="text-3xl font-bold text-gray-800">Upcoming Matches</h2>
                        </div>
                        <a href="#" class="text-primary hover:text-secondary font-semibold flex items-center space-x-2 transition-colors">
                            <span>View All</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        ' . $upcomingMatchesHTML . '
                    </div>
                </section>

                <!-- League Standings Preview -->
                <section class="mb-12">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center space-x-3">
                            <div class="w-1 h-8 bg-gradient-to-r from-primary to-secondary rounded-full"></div>
                            <h2 class="text-3xl font-bold text-gray-800">League Standings</h2>
                        </div>
                        <a href="#" class="text-primary hover:text-secondary font-semibold flex items-center space-x-2 transition-colors">
                            <span>View All</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <img src="https://via.placeholder.com/32x32?text=PL" alt="Premier League" class="w-8 h-8 rounded">
                                <h3 class="font-bold text-gray-800">Premier League</h3>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pos</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GD</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pts</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">1</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <img src="https://via.placeholder.com/24x24?text=MC" alt="Man City" class="w-6 h-6 rounded">
                                                <span class="text-sm font-medium text-gray-900">Manchester City</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">20</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">+32</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">45</td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">2</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <img src="https://via.placeholder.com/24x24?text=AR" alt="Arsenal" class="w-6 h-6 rounded">
                                                <span class="text-sm font-medium text-gray-900">Arsenal</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">20</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">+25</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">43</td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">3</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <img src="https://via.placeholder.com/24x24?text=AV" alt="Aston Villa" class="w-6 h-6 rounded">
                                                <span class="text-sm font-medium text-gray-900">Aston Villa</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">20</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">+16</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">42</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-8">
                <!-- Live Matches Widget -->
                <section class="bg-white rounded-2xl shadow-lg p-4 md:p-6 hover-lift">
                    <div class="flex items-center space-x-3 mb-4 md:mb-6">
                        <div class="w-1 h-6 bg-gradient-to-r from-primary to-secondary rounded-full"></div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-800">Live Now</h3>
                        <div class="w-3 h-3 bg-red-500 rounded-full live-indicator"></div>
                    </div>
                    <div class="space-y-4">
                        ' . (!empty($liveMatches) ? '
                        <div class="border-b border-gray-100 pb-4">
                            <div class="flex items-center justify-between text-sm mb-3">
                                <span class="text-red-600 font-bold bg-red-50 px-2 py-1 rounded-full text-xs">LIVE</span>
                                <span class="text-gray-500 font-medium text-xs truncate max-w-20">' . htmlspecialchars($liveMatches[0]['league_name'] ?? 'Unknown League') . '</span>
                            </div>
                            <div class="flex items-center justify-between space-x-2">
                                <div class="flex items-center space-x-2 flex-1">
                                    <img src="' . htmlspecialchars($liveMatches[0]['home_team_logo'] ?? 'https://via.placeholder.com/20x20/4F46E5/FFFFFF?text=' . substr($liveMatches[0]['home_team_name'], 0, 2)) . '" alt="' . htmlspecialchars($liveMatches[0]['home_team_name']) . '" class="w-5 h-5 md:w-6 md:h-6 rounded-full object-cover" onerror="this.src=\'https://via.placeholder.com/20x20/4F46E5/FFFFFF?text=' . substr($liveMatches[0]['home_team_name'], 0, 2) . '\'">
                                    <span class="text-xs md:text-sm font-bold text-gray-800 truncate">' . htmlspecialchars($liveMatches[0]['home_team_name']) . '</span>
                                </div>
                                <span class="font-bold text-base md:text-lg text-gray-800 score-animation flex-shrink-0">' . ($liveMatches[0]['home_score'] ?? 0) . ' - ' . ($liveMatches[0]['away_score'] ?? 0) . '</span>
                                <div class="flex items-center space-x-2 flex-1 justify-end">
                                    <span class="text-xs md:text-sm font-bold text-gray-800 truncate">' . htmlspecialchars($liveMatches[0]['away_team_name']) . '</span>
                                    <img src="' . htmlspecialchars($liveMatches[0]['away_team_logo'] ?? 'https://via.placeholder.com/20x20/DC2626/FFFFFF?text=' . substr($liveMatches[0]['away_team_name'], 0, 2)) . '" alt="' . htmlspecialchars($liveMatches[0]['away_team_name']) . '" class="w-5 h-5 md:w-6 md:h-6 rounded-full object-cover" onerror="this.src=\'https://via.placeholder.com/20x20/DC2626/FFFFFF?text=' . substr($liveMatches[0]['away_team_name'], 0, 2) . '\'">
                                </div>
                            </div>
                        </div>' : '<p class="text-gray-500 text-sm text-center py-4">No live matches at the moment</p>') . '
                    </div>
                </section>

                <!-- Top Scorers Widget -->
                <section class="bg-white rounded-2xl shadow-lg p-4 md:p-6 hover-lift">
                    <div class="flex items-center space-x-3 mb-4 md:mb-6">
                        <div class="w-1 h-6 bg-gradient-to-r from-primary to-secondary rounded-full"></div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-800">Top Scorers</h3>
                    </div>
                    <div class="space-y-3 md:space-y-4">
                        <div class="flex items-center space-x-2 md:space-x-3 p-2 md:p-3 bg-gray-50 rounded-xl">
                            <div class="w-6 h-6 md:w-8 md:h-8 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-bold">1</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <img src="https://via.placeholder.com/16x16/4F46E5/FFFFFF?text=MC" alt="Man City" class="w-4 h-4 md:w-5 md:h-5 rounded object-cover">
                                    <span class="text-xs md:text-sm font-bold text-gray-800 truncate">Erling Haaland</span>
                                </div>
                                <span class="text-xs text-gray-500 truncate">Manchester City</span>
                            </div>
                            <span class="text-base md:text-lg font-bold text-gray-900 flex-shrink-0">18</span>
                        </div>
                        <div class="flex items-center space-x-2 md:space-x-3 p-2 md:p-3 bg-gray-50 rounded-xl">
                            <div class="w-6 h-6 md:w-8 md:h-8 bg-gradient-to-r from-gray-400 to-gray-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-bold">2</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <img src="https://via.placeholder.com/16x16/DC2626/FFFFFF?text=TH" alt="Tottenham" class="w-4 h-4 md:w-5 md:h-5 rounded object-cover">
                                    <span class="text-xs md:text-sm font-bold text-gray-800 truncate">Son Heung-min</span>
                                </div>
                                <span class="text-xs text-gray-500 truncate">Tottenham</span>
                            </div>
                            <span class="text-base md:text-lg font-bold text-gray-900 flex-shrink-0">15</span>
                        </div>
                        <div class="flex items-center space-x-2 md:space-x-3 p-2 md:p-3 bg-gray-50 rounded-xl">
                            <div class="w-6 h-6 md:w-8 md:h-8 bg-gradient-to-r from-orange-400 to-orange-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xs font-bold">3</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <img src="https://via.placeholder.com/16x16/059669/FFFFFF?text=LI" alt="Liverpool" class="w-4 h-4 md:w-5 md:h-5 rounded object-cover">
                                    <span class="text-xs md:text-sm font-bold text-gray-800 truncate">Mohamed Salah</span>
                                </div>
                                <span class="text-xs text-gray-500 truncate">Liverpool</span>
                            </div>
                            <span class="text-base md:text-lg font-bold text-gray-900 flex-shrink-0">14</span>
                        </div>
                    </div>
                </section>

                <!-- Major Leagues -->
                <section class="bg-white rounded-2xl shadow-lg p-4 md:p-6 hover-lift">
                    <div class="flex items-center space-x-3 mb-4 md:mb-6">
                        <div class="w-1 h-6 bg-gradient-to-r from-primary to-secondary rounded-full"></div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-800">Top Leagues</h3>
                    </div>
                    <div class="space-y-3 md:space-y-4">
                        ' . $leaguesHTML . '
                    </div>
                </section>

                <!-- Live Stats -->
                <section class="bg-gradient-to-r from-primary to-secondary rounded-2xl p-4 md:p-6 text-white shadow-lg hover-lift">
                    <div class="flex items-center space-x-3 mb-4 md:mb-6">
                        <div class="w-1 h-6 bg-white rounded-full"></div>
                        <h3 class="text-lg md:text-xl font-bold">Live Stats</h3>
                    </div>
                    <div class="space-y-3 md:space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs md:text-sm">Live Matches</span>
                            <span class="font-bold text-base md:text-lg">' . count($liveMatches) . '</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-1">
                            <div class="bg-white h-1 rounded-full" style="width: ' . min(100, (count($liveMatches) / 10) * 100) . '%"></div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs md:text-sm">Upcoming</span>
                            <span class="font-bold text-base md:text-lg">' . count($upcomingMatches) . '</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-1">
                            <div class="bg-white h-1 rounded-full" style="width: ' . min(100, (count($upcomingMatches) / 20) * 100) . '%"></div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs md:text-sm">Leagues</span>
                            <span class="font-bold text-base md:text-lg">' . count($leagues) . '</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-1">
                            <div class="bg-white h-1 rounded-full" style="width: ' . min(100, (count($leagues) / 15) * 100) . '%"></div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs md:text-sm">News Articles</span>
                            <span class="font-bold text-base md:text-lg">' . (count($newsArticles) + ($featuredArticle ? 1 : 0)) . '</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-1">
                            <div class="bg-white h-1 rounded-full" style="width: ' . min(100, ((count($newsArticles) + ($featuredArticle ? 1 : 0)) / 50) * 100) . '%"></div>
                        </div>
                    </div>
                </section>

                <!-- Trending Topics -->
                <section class="bg-white rounded-2xl shadow-lg p-4 md:p-6 hover-lift">
                    <div class="flex items-center space-x-3 mb-4 md:mb-6">
                        <div class="w-1 h-6 bg-gradient-to-r from-primary to-secondary rounded-full"></div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-800">Trending</h3>
                    </div>
                    <div class="space-y-3 md:space-y-4">
                        <div class="flex items-center space-x-2 md:space-x-3 p-2 md:p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
                            <div class="w-2 h-2 bg-primary rounded-full flex-shrink-0"></div>
                            <span class="text-xs md:text-sm font-medium text-gray-700 truncate">#ChampionsLeague</span>
                            <span class="text-xs text-gray-500 ml-auto flex-shrink-0">2.1K</span>
                        </div>
                        <div class="flex items-center space-x-2 md:space-x-3 p-2 md:p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
                            <div class="w-2 h-2 bg-secondary rounded-full flex-shrink-0"></div>
                            <span class="text-xs md:text-sm font-medium text-gray-700 truncate">#PremierLeague</span>
                            <span class="text-xs text-gray-500 ml-auto flex-shrink-0">1.8K</span>
                        </div>
                        <div class="flex items-center space-x-2 md:space-x-3 p-2 md:p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
                            <div class="w-2 h-2 bg-accent rounded-full flex-shrink-0"></div>
                            <span class="text-xs md:text-sm font-medium text-gray-700 truncate">#TransferNews</span>
                            <span class="text-xs text-gray-500 ml-auto flex-shrink-0">1.5K</span>
                        </div>
                        <div class="flex items-center space-x-2 md:space-x-3 p-2 md:p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
                            <div class="w-2 h-2 bg-highlight rounded-full flex-shrink-0"></div>
                            <span class="text-xs md:text-sm font-medium text-gray-700 truncate">#WorldCup</span>
                            <span class="text-xs text-gray-500 ml-auto flex-shrink-0">1.2K</span>
                        </div>
                    </div>
                </section>

                <!-- Newsletter Signup -->
                <section class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-4 md:p-6 text-white shadow-lg hover-lift">
                    <div class="text-center">
                        <h3 class="text-lg md:text-xl font-bold mb-2 md:mb-3">Stay Updated</h3>
                        <p class="text-xs md:text-sm opacity-90 mb-3 md:mb-4">Get the latest football news and updates delivered to your inbox</p>
                        <div class="space-y-2 md:space-y-3">
                            <input type="email" placeholder="Enter your email" class="w-full px-3 md:px-4 py-2 md:py-3 rounded-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-white text-sm md:text-base">
                            <button class="w-full bg-white text-blue-600 px-3 md:px-4 py-2 md:py-3 rounded-xl font-semibold hover:bg-gray-100 transition-colors text-sm md:text-base">
                                Subscribe
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-neutral text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="relative">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-primary rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="12" r="8" fill="url(#gradient)"/>
                                    <path d="M12 4c-4.4 0-8 3.6-8 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6z" fill="white" opacity="0.3"/>
                                    <ellipse cx="12" cy="12" rx="10" ry="2" fill="white" opacity="0.2" transform="rotate(45 12 12)"/>
                                </svg>
                            </div>
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full"></div>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold gradient-text">WFN24</h3>
                            <p class="text-sm text-gray-400">World Football News 24</p>
                        </div>
                    </div>
                    <p class="text-gray-300 text-base leading-relaxed mb-6">Your ultimate destination for world football news, live scores, and comprehensive coverage of the beautiful game. Stay updated with the latest transfer news, match results, and expert analysis.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-primary transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-primary transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-primary transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-primary transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-6">Quick Links</h4>
                    <ul class="space-y-3 text-gray-300">
                        <li><a href="#" class="hover:text-white transition-colors duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>Home</span>
                        </a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                            <span>News</span>
                        </a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span>Live Matches</span>
                        </a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span>Leagues</span>
                        </a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>Teams</span>
                        </a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-6">Top Leagues</h4>
                    <ul class="space-y-3 text-gray-300">
                        <li><a href="#" class="hover:text-white transition-colors duration-200 flex items-center space-x-2">
                            <div class="w-2 h-2 bg-primary rounded-full"></div>
                            <span>Premier League</span>
                        </a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-200 flex items-center space-x-2">
                            <div class="w-2 h-2 bg-secondary rounded-full"></div>
                            <span>La Liga</span>
                        </a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-200 flex items-center space-x-2">
                            <div class="w-2 h-2 bg-accent rounded-full"></div>
                            <span>Serie A</span>
                        </a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-200 flex items-center space-x-2">
                            <div class="w-2 h-2 bg-highlight rounded-full"></div>
                            <span>Bundesliga</span>
                        </a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-200 flex items-center space-x-2">
                            <div class="w-2 h-2 bg-primary rounded-full"></div>
                            <span>Champions League</span>
                        </a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-12 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm">&copy; 2024 WFN24. All rights reserved. | Powered by real-time football data from API-Football</p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Privacy Policy</a>
                        <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Terms of Service</a>
                        <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Contact Us</a>
                    </div>
                </div>
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
