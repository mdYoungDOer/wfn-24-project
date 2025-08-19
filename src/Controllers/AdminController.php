<?php

namespace WFN24\Controllers;

use WFN24\Config\Database;
use WFN24\Models\NewsArticle;
use WFN24\Models\User;
use WFN24\Models\FootballMatch;
use WFN24\Models\Team;
use WFN24\Models\League;
use WFN24\Models\Player;

class AdminController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Admin Dashboard Overview
     */
    public function dashboard()
    {
        // Get analytics data
        $stats = $this->getDashboardStats();
        
        // Get recent activities
        $recentArticles = $this->getRecentArticles();
        $recentUsers = $this->getRecentUsers();
        $liveMatches = $this->getLiveMatches();
        
        return [
            'stats' => $stats,
            'recent_articles' => $recentArticles,
            'recent_users' => $recentUsers,
            'live_matches' => $liveMatches
        ];
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        $stats = [];
        
        // Total articles
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM news_articles");
        $stmt->execute();
        $stats['total_articles'] = $stmt->fetch()['count'];
        
        // Published articles
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM news_articles WHERE is_published = TRUE");
        $stmt->execute();
        $stats['published_articles'] = $stmt->fetch()['count'];
        
        // Total users
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch()['count'];
        
        // Active users (last 30 days)
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM users WHERE created_at >= NOW() - INTERVAL '30 days'");
        $stmt->execute();
        $stats['new_users_30_days'] = $stmt->fetch()['count'];
        
        // Live matches
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM matches WHERE is_live = TRUE");
        $stmt->execute();
        $stats['live_matches'] = $stmt->fetch()['count'];
        
        // Total teams
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM teams");
        $stmt->execute();
        $stats['total_teams'] = $stmt->fetch()['count'];
        
        // Total players
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM players");
        $stmt->execute();
        $stats['total_players'] = $stmt->fetch()['count'];
        
        return $stats;
    }

    /**
     * Get recent articles
     */
    private function getRecentArticles($limit = 5)
    {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT id, title, author_name, is_published, published_at, view_count 
             FROM news_articles 
             ORDER BY created_at DESC 
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get recent users
     */
    private function getRecentUsers($limit = 5)
    {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT id, username, email, first_name, last_name, is_admin, created_at 
             FROM users 
             ORDER BY created_at DESC 
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get live matches
     */
    private function getLiveMatches($limit = 5)
    {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT m.*, 
                    ht.name as home_team_name, ht.logo_url as home_team_logo,
                    at.name as away_team_name, at.logo_url as away_team_logo,
                    l.name as league_name
             FROM matches m
             JOIN teams ht ON m.home_team_id = ht.id
             JOIN teams at ON m.away_team_id = at.id
             JOIN leagues l ON m.league_id = l.id
             WHERE m.is_live = TRUE
             ORDER BY m.match_date DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Article Management
     */
    public function articles($page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        
        // Get articles with pagination
        $stmt = $this->db->getConnection()->prepare(
            "SELECT na.*, c.name as category_name
             FROM news_articles na
             LEFT JOIN categories c ON na.category_id = c.id
             ORDER BY na.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$perPage, $offset]);
        $articles = $stmt->fetchAll();
        
        // Get total count
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM news_articles");
        $stmt->execute();
        $total = $stmt->fetch()['count'];
        
        return [
            'articles' => $articles,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Create/Edit Article
     */
    public function saveArticle($data, $id = null)
    {
        try {
            if ($id) {
                // Update existing article
                $stmt = $this->db->getConnection()->prepare(
                    "UPDATE news_articles SET 
                     title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?,
                     category_id = ?, author_name = ?, is_featured = ?, is_published = ?,
                     published_at = ?, updated_at = CURRENT_TIMESTAMP
                     WHERE id = ?"
                );
                $stmt->execute([
                    $data['title'],
                    $data['slug'],
                    $data['excerpt'],
                    $data['content'],
                    $data['featured_image'],
                    $data['category_id'],
                    $data['author_name'],
                    $data['is_featured'] ? 'TRUE' : 'FALSE',
                    $data['is_published'] ? 'TRUE' : 'FALSE',
                    $data['published_at'],
                    $id
                ]);
                return ['success' => true, 'message' => 'Article updated successfully'];
            } else {
                // Create new article
                $stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO news_articles (title, slug, excerpt, content, featured_image,
                     category_id, author_name, is_featured, is_published, published_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $data['title'],
                    $data['slug'],
                    $data['excerpt'],
                    $data['content'],
                    $data['featured_image'],
                    $data['category_id'],
                    $data['author_name'],
                    $data['is_featured'] ? 'TRUE' : 'FALSE',
                    $data['is_published'] ? 'TRUE' : 'FALSE',
                    $data['published_at']
                ]);
                return ['success' => true, 'message' => 'Article created successfully'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Delete Article
     */
    public function deleteArticle($id)
    {
        try {
            $stmt = $this->db->getConnection()->prepare("DELETE FROM news_articles WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Article deleted successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * User Management
     */
    public function users($page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->db->getConnection()->prepare(
            "SELECT id, username, email, first_name, last_name, is_admin, is_active, created_at
             FROM users
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$perPage, $offset]);
        $users = $stmt->fetchAll();
        
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $total = $stmt->fetch()['count'];
        
        return [
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Update User Status
     */
    public function updateUserStatus($id, $isActive, $isAdmin = null)
    {
        try {
            if ($isAdmin !== null) {
                $stmt = $this->db->getConnection()->prepare(
                    "UPDATE users SET is_active = ?, is_admin = ? WHERE id = ?"
                );
                $stmt->execute([$isActive ? 'TRUE' : 'FALSE', $isAdmin ? 'TRUE' : 'FALSE', $id]);
            } else {
                $stmt = $this->db->getConnection()->prepare(
                    "UPDATE users SET is_active = ? WHERE id = ?"
                );
                $stmt->execute([$isActive ? 'TRUE' : 'FALSE', $id]);
            }
            return ['success' => true, 'message' => 'User status updated successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Match Management
     */
    public function matches($page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->db->getConnection()->prepare(
            "SELECT m.*, 
                    ht.name as home_team_name, ht.logo_url as home_team_logo,
                    at.name as away_team_name, at.logo_url as away_team_logo,
                    l.name as league_name
             FROM matches m
             JOIN teams ht ON m.home_team_id = ht.id
             JOIN teams at ON m.away_team_id = at.id
             JOIN leagues l ON m.league_id = l.id
             ORDER BY m.match_date DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$perPage, $offset]);
        $matches = $stmt->fetchAll();
        
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM matches");
        $stmt->execute();
        $total = $stmt->fetch()['count'];
        
        return [
            'matches' => $matches,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Get Categories
     */
    public function getCategories()
    {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT id, name, slug, description FROM categories WHERE is_active = TRUE ORDER BY name"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get Teams
     */
    public function getTeams()
    {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT id, name, logo_url, country FROM teams WHERE is_active = TRUE ORDER BY name"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get Leagues
     */
    public function getLeagues()
    {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT id, name, logo_url, country FROM leagues WHERE is_active = TRUE ORDER BY name"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
