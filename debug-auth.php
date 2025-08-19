<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    echo "ℹ️  No .env file found, using environment variables from platform...\n";
}

echo "🔍 Debugging Authentication System...\n\n";

// Check database connection
try {
    $db = \WFN24\Config\Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ Database connection successful!\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if admin user exists
echo "\n👤 Checking Admin User...\n";
try {
    $stmt = $connection->prepare("SELECT id, username, email, is_admin, is_active FROM users WHERE email = ?");
    $stmt->execute(['admin@wfn24.com']);
    $adminUser = $stmt->fetch();
    
    if ($adminUser) {
        echo "✅ Admin user found:\n";
        echo "   ID: " . $adminUser['id'] . "\n";
        echo "   Username: " . $adminUser['username'] . "\n";
        echo "   Email: " . $adminUser['email'] . "\n";
        echo "   Is Admin: " . ($adminUser['is_admin'] ? 'YES' : 'NO') . "\n";
        echo "   Is Active: " . ($adminUser['is_active'] ? 'YES' : 'NO') . "\n";
    } else {
        echo "❌ Admin user not found!\n";
        echo "   Run: php create-admin.php\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking admin user: " . $e->getMessage() . "\n";
}

// Check all users
echo "\n👥 All Users in Database:\n";
try {
    $stmt = $connection->prepare("SELECT id, username, email, is_admin, is_active FROM users ORDER BY id");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "   No users found in database\n";
    } else {
        foreach ($users as $user) {
            echo "   ID: " . $user['id'] . " | " . $user['username'] . " | " . $user['email'] . " | Admin: " . ($user['is_admin'] ? 'YES' : 'NO') . " | Active: " . ($user['is_active'] ? 'YES' : 'NO') . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error fetching users: " . $e->getMessage() . "\n";
}

// Test authentication
echo "\n🔐 Testing Authentication...\n";
try {
    $authController = new \WFN24\Controllers\AuthController();
    
    // Test login
    $result = $authController->login('admin@wfn24.com', 'admin123456');
    
    if ($result['success']) {
        echo "✅ Login successful!\n";
        echo "   User: " . $result['user']['username'] . "\n";
        echo "   Is Admin: " . ($result['user']['is_admin'] ? 'YES' : 'NO') . "\n";
        
        // Test admin check
        $isAdmin = $authController->isAdmin();
        echo "   Admin Check: " . ($isAdmin ? 'YES' : 'NO') . "\n";
        
    } else {
        echo "❌ Login failed: " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error testing authentication: " . $e->getMessage() . "\n";
}

echo "\n📝 Next Steps:\n";
echo "1. If admin user doesn't exist, run: php create-admin.php\n";
echo "2. If login fails, check the password in create-admin.php\n";
echo "3. Try accessing: /admin after successful login\n";
echo "4. Check browser console for any JavaScript errors\n";
