<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    echo "ℹ️  No .env file found, using environment variables from platform...\n";
}

echo "🔐 Resetting Admin Password...\n\n";

// Check database connection
try {
    $db = \WFN24\Config\Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ Database connection successful!\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Reset admin password
$adminEmail = 'admin@wfn24.com';
$newPassword = 'admin123456';

echo "👤 Resetting password for: $adminEmail\n";
echo "🔑 New password: $newPassword\n\n";

try {
    // Hash the new password
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update the admin user's password
    $stmt = $connection->prepare("
        UPDATE users 
        SET password_hash = ?, is_admin = TRUE, is_active = TRUE 
        WHERE email = ?
    ");
    $stmt->execute([$passwordHash, $adminEmail]);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Admin password reset successfully!\n";
    } else {
        echo "❌ No admin user found with email: $adminEmail\n";
        exit(1);
    }
    
    // Verify the password works
    echo "\n🔍 Testing new password...\n";
    $stmt = $connection->prepare("SELECT password_hash FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($newPassword, $user['password_hash'])) {
        echo "✅ Password verification successful!\n";
    } else {
        echo "❌ Password verification failed!\n";
        exit(1);
    }
    
    echo "\n🎉 Admin Password Reset Complete!\n";
    echo "================================\n";
    echo "Login Credentials:\n";
    echo "Email: $adminEmail\n";
    echo "Password: $newPassword\n";
    echo "\n📝 Next Steps:\n";
    echo "1. Go to your app URL\n";
    echo "2. Click 'Login' button\n";
    echo "3. Use the credentials above\n";
    echo "4. Once logged in, you'll see an 'Admin' button\n";
    echo "5. Click 'Admin' to access the dashboard\n";
    
} catch (Exception $e) {
    echo "❌ Failed to reset admin password: " . $e->getMessage() . "\n";
    exit(1);
}
