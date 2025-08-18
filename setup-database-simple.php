<?php
/**
 * WFN24 Simple Database Setup Script
 * This script creates the essential tables for WFN24
 * 
 * Usage: 
 * 1. Set your database credentials as environment variables
 * 2. Run: php setup-database-simple.php
 */

// Database connection settings from environment variables
$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '5432';
$dbname = $_ENV['DB_NAME'] ?? 'defaultdb';
$username = $_ENV['DB_USER'] ?? 'postgres';
$password = $_ENV['DB_PASSWORD'] ?? '';

// Check if credentials are available
if (empty($password)) {
    echo "❌ Database password not found in environment variables.\n";
    echo "Please set the following environment variables:\n";
    echo "DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD\n\n";
    echo "For Digital Ocean, use these values:\n";
    echo "DB_HOST=db-postgresql-nyc3-94088-do-user-24545475-0.h.db.ondigitalocean.com\n";
    echo "DB_PORT=25060\n";
    echo "DB_NAME=defaultdb\n";
    echo "DB_USER=doadmin\n";
    echo "DB_PASSWORD=your_password_here\n\n";
    exit(1);
}

try {
    // Create connection
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database successfully!\n\n";
    
    // Essential SQL statements
    $sqlStatements = [
        // Enable extensions
        "CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\";",
        "CREATE EXTENSION IF NOT EXISTS \"pg_trgm\";",
        
        // Create users table
        "CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin', 'moderator')),
            avatar_url VARCHAR(500),
            bio TEXT,
            preferences JSONB DEFAULT '{}',
            email_verified BOOLEAN DEFAULT FALSE,
            last_login TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );",
        
        // Create categories table
        "CREATE TABLE IF NOT EXISTS categories (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            color VARCHAR(7) DEFAULT '#e41e5b',
            icon VARCHAR(50),
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );",
        
        // Create leagues table
        "CREATE TABLE IF NOT EXISTS leagues (
            id SERIAL PRIMARY KEY,
            api_league_id INTEGER UNIQUE,
            name VARCHAR(255) NOT NULL,
            country VARCHAR(100),
            logo_url VARCHAR(500),
            type VARCHAR(50) DEFAULT 'League',
            season VARCHAR(20),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );",
        
        // Create teams table
        "CREATE TABLE IF NOT EXISTS teams (
            id SERIAL PRIMARY KEY,
            api_team_id INTEGER UNIQUE,
            name VARCHAR(255) NOT NULL,
            short_name VARCHAR(50),
            country VARCHAR(100),
            founded_year INTEGER,
            logo_url VARCHAR(500),
            stadium VARCHAR(255),
            capacity INTEGER,
            league_id INTEGER REFERENCES leagues(id) ON DELETE SET NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );",
        
        // Create players table
        "CREATE TABLE IF NOT EXISTS players (
            id SERIAL PRIMARY KEY,
            api_player_id INTEGER UNIQUE,
            name VARCHAR(255) NOT NULL,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            age INTEGER,
            nationality VARCHAR(100),
            position VARCHAR(50),
            height INTEGER,
            weight INTEGER,
            photo_url VARCHAR(500),
            team_id INTEGER REFERENCES teams(id) ON DELETE SET NULL,
            jersey_number INTEGER,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );",
        
        // Create matches table
        "CREATE TABLE IF NOT EXISTS matches (
            id SERIAL PRIMARY KEY,
            api_match_id INTEGER UNIQUE,
            home_team_id INTEGER REFERENCES teams(id) ON DELETE SET NULL,
            away_team_id INTEGER REFERENCES teams(id) ON DELETE SET NULL,
            league_id INTEGER REFERENCES leagues(id) ON DELETE SET NULL,
            match_date TIMESTAMP NOT NULL,
            status VARCHAR(50) DEFAULT 'SCHEDULED',
            home_score INTEGER DEFAULT 0,
            away_score INTEGER DEFAULT 0,
            home_possession INTEGER,
            away_possession INTEGER,
            venue VARCHAR(255),
            referee VARCHAR(255),
            attendance INTEGER,
            match_events JSONB DEFAULT '[]',
            statistics JSONB DEFAULT '{}',
            is_live BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );",
        
        // Create news_articles table
        "CREATE TABLE IF NOT EXISTS news_articles (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            excerpt TEXT,
            content TEXT NOT NULL,
            featured_image VARCHAR(500),
            category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
            author_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
            author_name VARCHAR(255),
            tags TEXT[],
            meta_title VARCHAR(255),
            meta_description TEXT,
            is_featured BOOLEAN DEFAULT FALSE,
            is_published BOOLEAN DEFAULT FALSE,
            published_at TIMESTAMP,
            view_count INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );",
        
        // Insert default categories
        "INSERT INTO categories (name, slug, description, color, icon) VALUES 
        ('Breaking News', 'breaking-news', 'Latest breaking football news', '#e41e5b', 'newspaper'),
        ('Transfer News', 'transfer-news', 'Player transfers and rumors', '#9a0864', 'exchange'),
        ('Match Reports', 'match-reports', 'Detailed match analysis', '#746354', 'trophy'),
        ('Opinion', 'opinion', 'Editorial and opinion pieces', '#a67c00', 'comment'),
        ('Features', 'features', 'In-depth feature articles', '#2c2c2c', 'star')
        ON CONFLICT (slug) DO NOTHING;",
        
        // Insert default admin user
        "INSERT INTO users (username, email, password_hash, first_name, last_name, role, email_verified, is_active) VALUES 
        ('admin', 'admin@wfn24.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', TRUE, TRUE)
        ON CONFLICT (email) DO NOTHING;",
        
        // Insert major leagues
        "INSERT INTO leagues (name, country, type, season) 
         SELECT 'Premier League', 'England', 'League', '2024/25'
         WHERE NOT EXISTS (SELECT 1 FROM leagues WHERE name = 'Premier League' AND season = '2024/25');",
        
        "INSERT INTO leagues (name, country, type, season) 
         SELECT 'La Liga', 'Spain', 'League', '2024/25'
         WHERE NOT EXISTS (SELECT 1 FROM leagues WHERE name = 'La Liga' AND season = '2024/25');",
         
        "INSERT INTO leagues (name, country, type, season) 
         SELECT 'Bundesliga', 'Germany', 'League', '2024/25'
         WHERE NOT EXISTS (SELECT 1 FROM leagues WHERE name = 'Bundesliga' AND season = '2024/25');",
         
        "INSERT INTO leagues (name, country, type, season) 
         SELECT 'Serie A', 'Italy', 'League', '2024/25'
         WHERE NOT EXISTS (SELECT 1 FROM leagues WHERE name = 'Serie A' AND season = '2024/25');",
         
        "INSERT INTO leagues (name, country, type, season) 
         SELECT 'Ligue 1', 'France', 'League', '2024/25'
         WHERE NOT EXISTS (SELECT 1 FROM leagues WHERE name = 'Ligue 1' AND season = '2024/25');",
         
        "INSERT INTO leagues (name, country, type, season) 
         SELECT 'Champions League', 'Europe', 'Cup', '2024/25'
         WHERE NOT EXISTS (SELECT 1 FROM leagues WHERE name = 'Champions League' AND season = '2024/25');",
         
        "INSERT INTO leagues (name, country, type, season) 
         SELECT 'Europa League', 'Europe', 'Cup', '2024/25'
         WHERE NOT EXISTS (SELECT 1 FROM leagues WHERE name = 'Europa League' AND season = '2024/25');"
    ];
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($sqlStatements as $statement) {
        try {
            $pdo->exec($statement);
            $successCount++;
            echo "✅ Executed: " . substr($statement, 0, 50) . "...\n";
        } catch (PDOException $e) {
            $errorCount++;
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎉 Database setup completed!\n";
    echo "✅ Successful statements: $successCount\n";
    echo "❌ Errors: $errorCount\n\n";
    
    if ($errorCount == 0) {
        echo "🎯 Your WFN24 database is now ready!\n";
        echo "🔑 Default admin credentials:\n";
        echo "   Email: admin@wfn24.com\n";
        echo "   Password: admin123\n\n";
        echo "🌐 Visit your app: https://wfn24-project-qrml7.ondigitalocean.app\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database credentials and try again.\n";
}
?>
