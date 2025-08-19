<?php
/**
 * Environment Variables Checker for Digital Ocean App Platform
 */

echo "🔍 Checking Environment Variables on Digital Ocean App Platform...\n\n";

// Check required environment variables
$requiredVars = [
    'FOOTBALL_API_KEY' => 'API-Football API Key',
    'DB_HOST' => 'Database Host',
    'DB_NAME' => 'Database Name', 
    'DB_USER' => 'Database User',
    'DB_PASS' => 'Database Password'
];

$missingVars = [];

foreach ($requiredVars as $var => $description) {
    if (isset($_ENV[$var])) {
        $value = $_ENV[$var];
        $displayValue = $var === 'FOOTBALL_API_KEY' ? substr($value, 0, 10) . '...' : $value;
        echo "✅ {$description} ({$var}): {$displayValue}\n";
    } else {
        echo "❌ {$description} ({$var}): NOT SET\n";
        $missingVars[] = $var;
    }
}

echo "\n";

if (!empty($missingVars)) {
    echo "❌ Missing environment variables: " . implode(', ', $missingVars) . "\n";
    echo "Please configure these in your Digital Ocean App Platform environment variables.\n";
    exit(1);
} else {
    echo "✅ All required environment variables are set!\n";
    echo "You can now run: php seed-football-data.php\n";
}
