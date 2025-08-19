<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    echo "ℹ️  No .env file found, using environment variables from platform...\n";
}

echo "🔍 Testing Leagues and Categories Endpoints...\n\n";

try {
    $db = \WFN24\Config\Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ Database connection successful!\n\n";
    
    // Test leagues table
    echo "📊 Testing Leagues Table:\n";
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM leagues");
    $stmt->execute();
    $leagueCount = $stmt->fetch()['count'];
    echo "   Total leagues in database: {$leagueCount}\n";
    
    if ($leagueCount > 0) {
        $stmt = $connection->prepare("SELECT id, name, country FROM leagues LIMIT 5");
        $stmt->execute();
        $leagues = $stmt->fetchAll();
        echo "   Sample leagues:\n";
        foreach ($leagues as $league) {
            echo "     - {$league['name']} ({$league['country']})\n";
        }
    }
    
    // Test categories table
    echo "\n📂 Testing Categories Table:\n";
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM categories");
    $stmt->execute();
    $categoryCount = $stmt->fetch()['count'];
    echo "   Total categories in database: {$categoryCount}\n";
    
    if ($categoryCount > 0) {
        $stmt = $connection->prepare("SELECT id, name, slug FROM categories LIMIT 5");
        $stmt->execute();
        $categories = $stmt->fetchAll();
        echo "   Sample categories:\n";
        foreach ($categories as $category) {
            echo "     - {$category['name']} (slug: {$category['slug']})\n";
        }
    }
    
    // Test AdminController methods directly
    echo "\n🧪 Testing AdminController Methods:\n";
    $adminController = new \WFN24\Controllers\AdminController();
    
    // Test getLeagues method
    echo "\n1. Testing getLeagues() method:\n";
    $leaguesResult = $adminController->getLeagues();
    $leaguesData = json_decode($leaguesResult, true);
    if ($leaguesData && isset($leaguesData['success'])) {
        echo "   ✅ getLeagues method works\n";
        echo "   Leagues count: " . count($leaguesData['data']) . "\n";
        if (count($leaguesData['data']) > 0) {
            echo "   First league: " . $leaguesData['data'][0]['name'] . "\n";
        }
    } else {
        echo "   ❌ getLeagues method failed\n";
        echo "   Response: " . $leaguesResult . "\n";
    }
    
    // Test getCategories method
    echo "\n2. Testing getCategories() method:\n";
    $categoriesResult = $adminController->getCategories();
    $categoriesData = json_decode($categoriesResult, true);
    if ($categoriesData && isset($categoriesData['success'])) {
        echo "   ✅ getCategories method works\n";
        echo "   Categories count: " . count($categoriesData['data']) . "\n";
        if (count($categoriesData['data']) > 0) {
            echo "   First category: " . $categoriesData['data'][0]['name'] . "\n";
        }
    } else {
        echo "   ❌ getCategories method failed\n";
        echo "   Response: " . $categoriesResult . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n📝 Summary:\n";
echo "If both methods show ✅, the endpoints should work correctly.\n";
echo "If any show ❌, there are issues that need to be fixed.\n";
echo "\n🔗 Test the admin dashboard at: https://wfn24.com/admin\n";
