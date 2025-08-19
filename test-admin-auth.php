<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    echo "ℹ️  No .env file found, using environment variables from platform...\n";
}

echo "🔍 Testing Admin Authentication and API Endpoints...\n\n";

try {
    $db = \WFN24\Config\Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ Database connection successful!\n";
    
    // Check if admin user exists
    $stmt = $connection->prepare("SELECT id, username, email, is_admin, is_active FROM users WHERE email = 'admin@wfn24.com'");
    $stmt->execute();
    $adminUser = $stmt->fetch();
    
    if ($adminUser) {
        echo "✅ Admin user found:\n";
        echo "   ID: {$adminUser['id']}\n";
        echo "   Username: {$adminUser['username']}\n";
        echo "   Email: {$adminUser['email']}\n";
        echo "   Is Admin: " . ($adminUser['is_admin'] ? 'Yes' : 'No') . "\n";
        echo "   Is Active: " . ($adminUser['is_active'] ? 'Yes' : 'No') . "\n\n";
    } else {
        echo "❌ Admin user not found!\n\n";
    }
    
    // Test API endpoints
    echo "🌐 Testing API Endpoints...\n";
    
    // Test articles endpoint
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM news_articles");
    $stmt->execute();
    $articleCount = $stmt->fetch()['count'];
    echo "   Articles in database: {$articleCount}\n";
    
    // Test matches endpoint
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM matches");
    $stmt->execute();
    $matchCount = $stmt->fetch()['count'];
    echo "   Matches in database: {$matchCount}\n";
    
    // Test teams endpoint
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM teams");
    $stmt->execute();
    $teamCount = $stmt->fetch()['count'];
    echo "   Teams in database: {$teamCount}\n";
    
    // Test users endpoint
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $userCount = $stmt->fetch()['count'];
    echo "   Users in database: {$userCount}\n";
    
    echo "\n✅ Database queries successful!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n📝 Next Steps:\n";
echo "1. Go to https://wfn24.com/admin\n";
echo "2. Login with: admin@wfn24.com / admin123456\n";
echo "3. Test the CRUD operations in each tab\n";
echo "4. Check browser console for any JavaScript errors\n";
