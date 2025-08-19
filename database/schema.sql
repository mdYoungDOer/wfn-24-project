-- WFN24 Database Schema
-- PostgreSQL database for World Football News 24

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Create tables
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    is_admin BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS leagues (
    id SERIAL PRIMARY KEY,
    api_league_id INTEGER UNIQUE,
    name VARCHAR(255) NOT NULL,
    country VARCHAR(100),
    logo_url TEXT,
    type VARCHAR(50),
    season VARCHAR(10),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS teams (
    id SERIAL PRIMARY KEY,
    api_team_id INTEGER UNIQUE,
    name VARCHAR(255) NOT NULL,
    short_name VARCHAR(50),
    country VARCHAR(100),
    founded INTEGER,
    logo_url TEXT,
    stadium VARCHAR(255),
    capacity INTEGER,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS players (
    id SERIAL PRIMARY KEY,
    api_player_id INTEGER UNIQUE,
    name VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    age INTEGER,
    nationality VARCHAR(100),
    height INTEGER,
    weight INTEGER,
    photo_url TEXT,
    position VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS matches (
    id SERIAL PRIMARY KEY,
    api_match_id INTEGER UNIQUE,
    home_team_id INTEGER REFERENCES teams(id),
    away_team_id INTEGER REFERENCES teams(id),
    league_id INTEGER REFERENCES leagues(id),
    match_date TIMESTAMP,
    status VARCHAR(50),
    home_score INTEGER DEFAULT 0,
    away_score INTEGER DEFAULT 0,
    venue VARCHAR(255),
    is_live BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS news_articles (
    id SERIAL PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500) UNIQUE NOT NULL,
    excerpt TEXT,
    content TEXT,
    featured_image TEXT,
    category_id INTEGER REFERENCES categories(id),
    author_name VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP,
    view_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS api_cache (
    id SERIAL PRIMARY KEY,
    cache_key VARCHAR(255) UNIQUE NOT NULL,
    data JSONB,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_news_articles_published ON news_articles(is_published, published_at);
CREATE INDEX IF NOT EXISTS idx_news_articles_featured ON news_articles(is_featured);
CREATE INDEX IF NOT EXISTS idx_matches_live ON matches(is_live);
CREATE INDEX IF NOT EXISTS idx_matches_date ON matches(match_date);
CREATE INDEX IF NOT EXISTS idx_api_cache_expires ON api_cache(expires_at);
CREATE INDEX IF NOT EXISTS idx_teams_name ON teams(name);
CREATE INDEX IF NOT EXISTS idx_leagues_name ON leagues(name);
CREATE INDEX IF NOT EXISTS idx_players_name ON players(name);

-- Insert default categories
INSERT INTO categories (name, slug, description) VALUES
('Breaking News', 'breaking-news', 'Latest breaking football news and updates'),
('Transfer News', 'transfer-news', 'Player transfers and transfer rumors'),
('Match Reports', 'match-reports', 'Detailed match reports and analysis'),
('League News', 'league-news', 'League-specific news and updates'),
('Player News', 'player-news', 'Player-specific news and interviews'),
('Team News', 'team-news', 'Team-specific news and updates')
ON CONFLICT (slug) DO NOTHING;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name, is_admin) VALUES
('admin', 'admin@wfn24.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', TRUE)
ON CONFLICT (username) DO NOTHING;
