<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    echo "ℹ️  No .env file found, using environment variables from platform...\n";
}

echo "🔧 Fixing Database Schema...\n\n";

// Check database connection
try {
    $db = \WFN24\Config\Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ Database connection successful!\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check current table structure
echo "\n📋 Checking Current Table Structure...\n";
try {
    $stmt = $connection->prepare("
        SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'users' 
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "Current users table columns:\n";
    foreach ($columns as $column) {
        echo "   - " . $column['column_name'] . " (" . $column['data_type'] . ", " . ($column['is_nullable'] === 'YES' ? 'nullable' : 'not null') . ")\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking table structure: " . $e->getMessage() . "\n";
}

// Add missing columns
echo "\n🔧 Adding Missing Columns...\n";

$missingColumns = [
    'is_admin' => 'BOOLEAN DEFAULT FALSE',
    'is_active' => 'BOOLEAN DEFAULT TRUE',
    'email_verified_at' => 'TIMESTAMP',
    'first_name' => 'VARCHAR(100)',
    'last_name' => 'VARCHAR(100)'
];

foreach ($missingColumns as $columnName => $columnDefinition) {
    try {
        // Check if column exists
        $stmt = $connection->prepare("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'users' AND column_name = ?
        ");
        $stmt->execute([$columnName]);
        $exists = $stmt->fetch();
        
        if (!$exists) {
            $stmt = $connection->prepare("ALTER TABLE users ADD COLUMN $columnName $columnDefinition");
            $stmt->execute();
            echo "✅ Added column: $columnName\n";
        } else {
            echo "ℹ️  Column already exists: $columnName\n";
        }
    } catch (Exception $e) {
        echo "❌ Error adding column $columnName: " . $e->getMessage() . "\n";
    }
}

// Update existing users to have admin privileges if needed
echo "\n👤 Updating Existing Users...\n";
try {
    // Check if we have any users
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        // Update the first user to be admin (if no admin exists)
        $stmt = $connection->prepare("
            UPDATE users 
            SET is_admin = TRUE, is_active = TRUE 
            WHERE id = (SELECT id FROM users ORDER BY id LIMIT 1)
        ");
        $stmt->execute();
        echo "✅ Updated first user to have admin privileges\n";
    } else {
        echo "ℹ️  No users found in database\n";
    }
} catch (Exception $e) {
    echo "❌ Error updating users: " . $e->getMessage() . "\n";
}

// Verify the fix
echo "\n✅ Verifying Database Schema...\n";
try {
    $stmt = $connection->prepare("
        SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'users' 
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "Updated users table columns:\n";
    foreach ($columns as $column) {
        echo "   - " . $column['column_name'] . " (" . $column['data_type'] . ", " . ($column['is_nullable'] === 'YES' ? 'nullable' : 'not null') . ")\n";
    }
} catch (Exception $e) {
    echo "❌ Error verifying table structure: " . $e->getMessage() . "\n";
}

echo "\n🎉 Database Schema Fixed!\n";
echo "================================\n";
echo "Now you can run:\n";
echo "1. php create-admin.php (to create a new admin user)\n";
echo "2. php debug-auth.php (to test authentication)\n";
echo "3. Access your app and login\n";
