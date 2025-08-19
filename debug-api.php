<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    echo "ℹ️  No .env file found, using environment variables from platform...\n";
}

echo "🔍 Debugging API Endpoints...\n\n";

// Test database connection
try {
    $db = \WFN24\Config\Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ Database connection successful!\n\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test AdminController methods directly
try {
    $adminController = new \WFN24\Controllers\AdminController();
    
    echo "🧪 Testing AdminController Methods...\n\n";
    
    // Test dashboard method
    echo "1. Testing dashboard() method:\n";
    $dashboardResult = $adminController->dashboard();
    $dashboardData = json_decode($dashboardResult, true);
    if ($dashboardData && isset($dashboardData['success'])) {
        echo "   ✅ Dashboard method works\n";
        echo "   Stats: " . json_encode($dashboardData['stats']) . "\n";
    } else {
        echo "   ❌ Dashboard method failed\n";
        echo "   Response: " . $dashboardResult . "\n";
    }
    
    // Test getArticles method
    echo "\n2. Testing getArticles() method:\n";
    $articlesResult = $adminController->getArticles(1, 5);
    $articlesData = json_decode($articlesResult, true);
    if ($articlesData && isset($articlesData['success'])) {
        echo "   ✅ getArticles method works\n";
        echo "   Articles count: " . count($articlesData['articles']) . "\n";
    } else {
        echo "   ❌ getArticles method failed\n";
        echo "   Response: " . $articlesResult . "\n";
    }
    
    // Test getMatches method
    echo "\n3. Testing getMatches() method:\n";
    $matchesResult = $adminController->getMatches(1, 5);
    $matchesData = json_decode($matchesResult, true);
    if ($matchesData && isset($matchesData['success'])) {
        echo "   ✅ getMatches method works\n";
        echo "   Matches count: " . count($matchesData['matches']) . "\n";
    } else {
        echo "   ❌ getMatches method failed\n";
        echo "   Response: " . $matchesResult . "\n";
    }
    
    // Test getTeams method
    echo "\n4. Testing getTeams() method:\n";
    $teamsResult = $adminController->getTeams(1, 5);
    $teamsData = json_decode($teamsResult, true);
    if ($teamsData && isset($teamsData['success'])) {
        echo "   ✅ getTeams method works\n";
        echo "   Teams count: " . count($teamsData['teams']) . "\n";
    } else {
        echo "   ❌ getTeams method failed\n";
        echo "   Response: " . $teamsResult . "\n";
    }
    
    // Test getUsers method
    echo "\n5. Testing getUsers() method:\n";
    $usersResult = $adminController->getUsers(1, 5);
    $usersData = json_decode($usersResult, true);
    if ($usersData && isset($usersData['success'])) {
        echo "   ✅ getUsers method works\n";
        echo "   Users count: " . count($usersData['users']) . "\n";
    } else {
        echo "   ❌ getUsers method failed\n";
        echo "   Response: " . $usersResult . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing AdminController: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n📝 Summary:\n";
echo "If all methods show ✅, the backend API is working correctly.\n";
echo "If any show ❌, there are issues that need to be fixed.\n";
echo "\n🔗 Test the admin dashboard at: https://wfn24.com/admin\n";
