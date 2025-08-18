-- WFN24 Database Schema
-- PostgreSQL

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    avatar VARCHAR(255),
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin', 'editor')),
    is_active BOOLEAN DEFAULT true,
    email_verified_at TIMESTAMP,
    last_login_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#e41e5b',
    icon VARCHAR(50),
    is_active BOOLEAN DEFAULT true,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Leagues table
CREATE TABLE leagues (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    type VARCHAR(50) DEFAULT 'league',
    season VARCHAR(20),
    logo VARCHAR(255),
    api_league_id VARCHAR(50) UNIQUE,
    is_active BOOLEAN DEFAULT true,
    priority INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teams table
CREATE TABLE teams (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    short_name VARCHAR(20),
    country VARCHAR(100),
    city VARCHAR(100),
    stadium VARCHAR(100),
    capacity INTEGER,
    founded_year INTEGER,
    logo VARCHAR(255),
    website VARCHAR(255),
    api_team_id VARCHAR(50) UNIQUE,
    league_id INTEGER REFERENCES leagues(id) ON DELETE SET NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Players table
CREATE TABLE players (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    date_of_birth DATE,
    nationality VARCHAR(100),
    position VARCHAR(20) CHECK (position IN ('Goalkeeper', 'Defender', 'Midfielder', 'Forward')),
    shirt_number INTEGER,
    height INTEGER, -- in cm
    weight INTEGER, -- in kg
    preferred_foot VARCHAR(10) CHECK (preferred_foot IN ('Left', 'Right', 'Both')),
    photo VARCHAR(255),
    team_id INTEGER REFERENCES teams(id) ON DELETE SET NULL,
    api_player_id VARCHAR(50) UNIQUE,
    is_active BOOLEAN DEFAULT true,
    market_value DECIMAL(12,2),
    contract_until DATE,
    agent VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Matches table
CREATE TABLE matches (
    id SERIAL PRIMARY KEY,
    home_team_id INTEGER REFERENCES teams(id) ON DELETE CASCADE,
    away_team_id INTEGER REFERENCES teams(id) ON DELETE CASCADE,
    league_id INTEGER REFERENCES leagues(id) ON DELETE CASCADE,
    season_id VARCHAR(20),
    match_date DATE NOT NULL,
    kickoff_time TIME,
    status VARCHAR(20) DEFAULT 'scheduled' CHECK (status IN ('scheduled', 'live', 'finished', 'cancelled', 'postponed')),
    home_score INTEGER DEFAULT 0,
    away_score INTEGER DEFAULT 0,
    home_penalties INTEGER,
    away_penalties INTEGER,
    stadium VARCHAR(100),
    referee VARCHAR(100),
    attendance INTEGER,
    weather VARCHAR(50),
    home_possession INTEGER,
    away_possession INTEGER,
    home_shots INTEGER,
    away_shots INTEGER,
    home_shots_on_target INTEGER,
    away_shots_on_target INTEGER,
    home_corners INTEGER,
    away_corners INTEGER,
    home_fouls INTEGER,
    away_fouls INTEGER,
    home_yellow_cards INTEGER,
    away_yellow_cards INTEGER,
    home_red_cards INTEGER,
    away_red_cards INTEGER,
    api_match_id VARCHAR(50) UNIQUE,
    is_live BOOLEAN DEFAULT false,
    highlights_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- News Articles table
CREATE TABLE news_articles (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    author_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived')),
    published_at TIMESTAMP,
    meta_title VARCHAR(255),
    meta_description TEXT,
    tags TEXT,
    is_featured BOOLEAN DEFAULT false,
    view_count INTEGER DEFAULT 0,
    seo_score INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Match Events table (for live commentary)
CREATE TABLE match_events (
    id SERIAL PRIMARY KEY,
    match_id INTEGER REFERENCES matches(id) ON DELETE CASCADE,
    minute INTEGER,
    event_type VARCHAR(50) NOT NULL, -- goal, card, substitution, etc.
    description TEXT NOT NULL,
    player_id INTEGER REFERENCES players(id) ON DELETE SET NULL,
    team_id INTEGER REFERENCES teams(id) ON DELETE SET NULL,
    additional_data JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User Follows table (for personalized feeds)
CREATE TABLE user_follows (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    followable_type VARCHAR(20) NOT NULL, -- team, league, player
    followable_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, followable_type, followable_id)
);

-- User Notifications table
CREATE TABLE user_notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSONB,
    is_read BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- API Cache table
CREATE TABLE api_cache (
    id SERIAL PRIMARY KEY,
    cache_key VARCHAR(255) UNIQUE NOT NULL,
    cache_value TEXT NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_categories_slug ON categories(slug);
CREATE INDEX idx_categories_active ON categories(is_active);
CREATE INDEX idx_leagues_country ON leagues(country);
CREATE INDEX idx_leagues_active ON leagues(is_active);
CREATE INDEX idx_teams_league ON teams(league_id);
CREATE INDEX idx_teams_active ON teams(is_active);
CREATE INDEX idx_players_team ON players(team_id);
CREATE INDEX idx_players_position ON players(position);
CREATE INDEX idx_players_active ON players(is_active);
CREATE INDEX idx_matches_date ON matches(match_date);
CREATE INDEX idx_matches_status ON matches(status);
CREATE INDEX idx_matches_live ON matches(is_live);
CREATE INDEX idx_matches_league ON matches(league_id);
CREATE INDEX idx_matches_teams ON matches(home_team_id, away_team_id);
CREATE INDEX idx_news_articles_slug ON news_articles(slug);
CREATE INDEX idx_news_articles_status ON news_articles(status);
CREATE INDEX idx_news_articles_published ON news_articles(published_at);
CREATE INDEX idx_news_articles_category ON news_articles(category_id);
CREATE INDEX idx_news_articles_featured ON news_articles(is_featured);
CREATE INDEX idx_match_events_match ON match_events(match_id);
CREATE INDEX idx_match_events_minute ON match_events(minute);
CREATE INDEX idx_user_follows_user ON user_follows(user_id);
CREATE INDEX idx_user_follows_followable ON user_follows(followable_type, followable_id);
CREATE INDEX idx_user_notifications_user ON user_notifications(user_id);
CREATE INDEX idx_user_notifications_read ON user_notifications(is_read);
CREATE INDEX idx_api_cache_expires ON api_cache(expires_at);

-- Create full-text search indexes
CREATE INDEX idx_news_articles_search ON news_articles USING gin(to_tsvector('english', title || ' ' || COALESCE(excerpt, '') || ' ' || COALESCE(content, '')));
CREATE INDEX idx_teams_search ON teams USING gin(to_tsvector('english', name || ' ' || COALESCE(city, '') || ' ' || COALESCE(country, '')));
CREATE INDEX idx_players_search ON players USING gin(to_tsvector('english', name || ' ' || COALESCE(first_name, '') || ' ' || COALESCE(last_name, '')));

-- Insert default categories
INSERT INTO categories (name, slug, description, color, icon, sort_order) VALUES
('Breaking News', 'breaking-news', 'Latest breaking football news', '#e41e5b', 'bolt', 1),
('Transfer News', 'transfer-news', 'Player transfers and rumors', '#9a0864', 'exchange', 2),
('Match Reports', 'match-reports', 'Detailed match analysis and reports', '#2c2c2c', 'trophy', 3),
('Opinion', 'opinion', 'Editorial and opinion pieces', '#746354', 'comment', 4),
('Features', 'features', 'In-depth features and analysis', '#a67c00', 'star', 5);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, first_name, last_name, role, is_active) VALUES
('admin', 'admin@wfn24.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', true);

-- Insert some major leagues
INSERT INTO leagues (name, country, type, season, priority) VALUES
('Premier League', 'England', 'league', '2024/25', 1),
('La Liga', 'Spain', 'league', '2024/25', 2),
('Bundesliga', 'Germany', 'league', '2024/25', 3),
('Serie A', 'Italy', 'league', '2024/25', 4),
('Ligue 1', 'France', 'league', '2024/25', 5),
('Champions League', 'Europe', 'cup', '2024/25', 6),
('Europa League', 'Europe', 'cup', '2024/25', 7);
