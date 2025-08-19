<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    // Use platform environment variables
    echo "ℹ️  No .env file found, using environment variables from platform...\n";
}

// Check required environment variables
$requiredVars = [
    'DB_HOST' => 'Database Host',
    'DB_NAME' => 'Database Name', 
    'DB_USER' => 'Database User',
    'DB_PASSWORD' => 'Database Password'
];

$missingVars = [];
foreach ($requiredVars as $var => $description) {
    if (!isset($_ENV[$var]) || empty($_ENV[$var])) {
        $missingVars[] = $var;
    }
}

if (!empty($missingVars)) {
    echo "❌ Missing environment variables: " . implode(', ', $missingVars) . "\n";
    echo "Please configure these in your Digital Ocean App Platform environment variables.\n";
    exit(1);
}

echo "🔍 Checking Database Connection...\n";

try {
    $db = \WFN24\Config\Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ Database connection successful!\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Admin user details
$adminEmail = 'admin@wfn24.com';
$adminPassword = 'admin123456';
$adminUsername = 'admin';
$firstName = 'Admin';
$lastName = 'User';

echo "\n👤 Creating Admin User...\n";
echo "Email: $adminEmail\n";
echo "Password: $adminPassword\n";
echo "Username: $adminUsername\n";

try {
    // Check if admin user already exists
    $stmt = $connection->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        // Update existing user to be admin
        $stmt = $connection->prepare("UPDATE users SET is_admin = TRUE WHERE email = ?");
        $stmt->execute([$adminEmail]);
        echo "✅ Admin user already exists and has been updated with admin privileges!\n";
    } else {
        // Create new admin user
        $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        $stmt = $connection->prepare("
            INSERT INTO users (username, email, password_hash, first_name, last_name, is_admin, is_active, email_verified_at) 
            VALUES (?, ?, ?, ?, ?, TRUE, TRUE, NOW())
        ");
        
        $stmt->execute([$adminUsername, $adminEmail, $passwordHash, $firstName, $lastName]);
        echo "✅ Admin user created successfully!\n";
    }

    echo "\n🎉 Admin Access Setup Complete!\n";
    echo "================================\n";
    echo "Login Credentials:\n";
    echo "Email: $adminEmail\n";
    echo "Password: $adminPassword\n";
    echo "Username: $adminUsername\n";
    echo "\n📝 Next Steps:\n";
    echo "1. Go to your app URL\n";
    echo "2. Click 'Login' button\n";
    echo "3. Use the credentials above\n";
    echo "4. Once logged in, you'll see an 'Admin' button\n";
    echo "5. Click 'Admin' to access the dashboard\n";
    echo "\n🔗 Admin Dashboard URL: /admin\n";

} catch (Exception $e) {
    echo "❌ Failed to create admin user: " . $e->getMessage() . "\n";
    exit(1);
}
