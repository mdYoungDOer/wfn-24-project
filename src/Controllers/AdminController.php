<?php

namespace WFN24\Controllers;

use WFN24\Models\NewsArticle;
use WFN24\Models\FootballMatch;
use WFN24\Models\Team;
use WFN24\Models\User;
use WFN24\Models\Category;
use WFN24\Models\League;
use WFN24\Models\Player;
use WFN24\Config\Database;

class AdminController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Dashboard Overview
    public function dashboard()
    {
        try {
            // Get statistics
            $stats = [
                'total_articles' => $this->getTotalArticles(),
                'live_matches' => $this->getLiveMatchesCount(),
                'total_users' => $this->getTotalUsers(),
                'total_teams' => $this->getTotalTeams(),
                'recent_articles' => $this->getRecentArticles(5),
                'recent_activity' => $this->getRecentActivity()
            ];

            return json_encode([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Articles Management
    public function getArticles($page = 1, $limit = 10, $search = '')
    {
        try {
            $articleModel = new NewsArticle();
            $articles = $articleModel->getAllWithPagination($page, $limit, $search);
            $total = $articleModel->getTotalCount($search);

            return json_encode([
                'success' => true,
                'data' => [
                    'articles' => $articles,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function createArticle($data)
    {
        try {
            $articleModel = new NewsArticle();
            
            // Validate required fields
            if (empty($data['title']) || empty($data['content'])) {
                throw new \Exception('Title and content are required');
            }

            $articleData = [
                'title' => $data['title'],
                'content' => $data['content'],
                'excerpt' => $data['excerpt'] ?? substr($data['content'], 0, 200),
                'category_id' => $data['category_id'] ?? 1,
                'author_id' => $data['author_id'] ?? 1,
                'featured_image' => $data['featured_image'] ?? '',
                'is_published' => $data['is_published'] ?? false,
                'is_featured' => $data['is_featured'] ?? false,
                'meta_title' => $data['meta_title'] ?? $data['title'],
                'meta_description' => $data['meta_description'] ?? $data['excerpt'] ?? substr($data['content'], 0, 160)
            ];

            $articleId = $articleModel->create($articleData);

            return json_encode([
                'success' => true,
                'data' => ['id' => $articleId],
                'message' => 'Article created successfully'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateArticle($id, $data)
    {
        try {
            $articleModel = new NewsArticle();
            
            if (empty($data['title']) || empty($data['content'])) {
                throw new \Exception('Title and content are required');
            }

            $articleData = [
                'title' => $data['title'],
                'content' => $data['content'],
                'excerpt' => $data['excerpt'] ?? substr($data['content'], 0, 200),
                'category_id' => $data['category_id'] ?? 1,
                'featured_image' => $data['featured_image'] ?? '',
                'is_published' => $data['is_published'] ?? false,
                'is_featured' => $data['is_featured'] ?? false,
                'meta_title' => $data['meta_title'] ?? $data['title'],
                'meta_description' => $data['meta_description'] ?? $data['excerpt'] ?? substr($data['content'], 0, 160)
            ];

            $articleModel->update($id, $articleData);

            return json_encode([
                'success' => true,
                'message' => 'Article updated successfully'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteArticle($id)
    {
        try {
            $articleModel = new NewsArticle();
            $articleModel->delete($id);

            return json_encode([
                'success' => true,
                'message' => 'Article deleted successfully'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getArticle($id)
    {
        try {
            $articleModel = new NewsArticle();
            $article = $articleModel->findById($id);

            if (!$article) {
                throw new \Exception('Article not found');
            }

            return json_encode([
                'success' => true,
                'data' => $article
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Matches Management
    public function getMatches($page = 1, $limit = 10, $status = '')
    {
        try {
            $matchModel = new FootballMatch();
            $matches = $matchModel->getAllWithPagination($page, $limit, $status);
            $total = $matchModel->getTotalCount($status);

            return json_encode([
                'success' => true,
                'data' => [
                    'matches' => $matches,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function createMatch($data)
    {
        try {
            $matchModel = new FootballMatch();
            
            if (empty($data['home_team_id']) || empty($data['away_team_id']) || empty($data['match_date'])) {
                throw new \Exception('Home team, away team, and match date are required');
            }

            $matchData = [
                'home_team_id' => $data['home_team_id'],
                'away_team_id' => $data['away_team_id'],
                'league_id' => $data['league_id'] ?? 1,
                'match_date' => $data['match_date'],
                'status' => $data['status'] ?? 'scheduled',
                'home_score' => $data['home_score'] ?? null,
                'away_score' => $data['away_score'] ?? null,
                'venue' => $data['venue'] ?? '',
                'referee' => $data['referee'] ?? '',
                'attendance' => $data['attendance'] ?? null
            ];

            $matchId = $matchModel->create($matchData);

            return json_encode([
                'success' => true,
                'data' => ['id' => $matchId],
                'message' => 'Match created successfully'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateMatch($id, $data)
    {
        try {
            $matchModel = new FootballMatch();
            
            $matchData = [
                'home_team_id' => $data['home_team_id'] ?? null,
                'away_team_id' => $data['away_team_id'] ?? null,
                'league_id' => $data['league_id'] ?? null,
                'match_date' => $data['match_date'] ?? null,
                'status' => $data['status'] ?? null,
                'home_score' => $data['home_score'] ?? null,
                'away_score' => $data['away_score'] ?? null,
                'venue' => $data['venue'] ?? null,
                'referee' => $data['referee'] ?? null,
                'attendance' => $data['attendance'] ?? null
            ];

            // Remove null values
            $matchData = array_filter($matchData, function($value) {
                return $value !== null;
            });

            $matchModel->update($id, $matchData);

            return json_encode([
                'success' => true,
                'message' => 'Match updated successfully'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteMatch($id)
    {
        try {
            $matchModel = new FootballMatch();
            $matchModel->delete($id);

            return json_encode([
                'success' => true,
                'message' => 'Match deleted successfully'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Teams Management
    public function getTeams($page = 1, $limit = 10, $search = '')
    {
        try {
            $teamModel = new Team();
            $teams = $teamModel->getAllWithPagination($page, $limit, $search);
            $total = $teamModel->getTotalCount($search);

            return json_encode([
                'success' => true,
                'data' => [
                    'teams' => $teams,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function createTeam($data)
    {
        try {
            $teamModel = new Team();
            
            if (empty($data['name'])) {
                throw new \Exception('Team name is required');
            }

            $teamData = [
                'name' => $data['name'],
                'short_name' => $data['short_name'] ?? substr($data['name'], 0, 3),
                'league_id' => $data['league_id'] ?? 1,
                'country' => $data['country'] ?? '',
                'city' => $data['city'] ?? '',
                'stadium' => $data['stadium'] ?? '',
                'founded_year' => $data['founded_year'] ?? null,
                'logo_url' => $data['logo_url'] ?? '',
                'website' => $data['website'] ?? '',
                'description' => $data['description'] ?? ''
            ];

            $teamId = $teamModel->create($teamData);

            return json_encode([
                'success' => true,
                'data' => ['id' => $teamId],
                'message' => 'Team created successfully'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateTeam($id, $data)
    {
        try {
            $teamModel = new Team();
            
            $teamData = [
                'name' => $data['name'] ?? null,
                'short_name' => $data['short_name'] ?? null,
                'league_id' => $data['league_id'] ?? null,
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'stadium' => $data['stadium'] ?? null,
                'founded_year' => $data['founded_year'] ?? null,
                'logo_url' => $data['logo_url'] ?? null,
                'website' => $data['website'] ?? null,
                'description' => $data['description'] ?? null
            ];

            // Remove null values
            $teamData = array_filter($teamData, function($value) {
                return $value !== null;
            });

            $teamModel->update($id, $teamData);

            return json_encode([
                'success' => true,
                'message' => 'Team updated successfully'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteTeam($id)
    {
        try {
            $teamModel = new Team();
            $teamModel->delete($id);

            return json_encode([
                'success' => true,
                'message' => 'Team deleted successfully'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Users Management
    public function getUsers($page = 1, $limit = 10, $search = '')
    {
        try {
            $userModel = new User();
            $users = $userModel->getAllWithPagination($page, $limit, $search);
            $total = $userModel->getTotalCount($search);

            return json_encode([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateUserStatus($id, $status)
    {
        try {
            $userModel = new User();
            $userModel->update($id, ['is_active' => $status === 'active']);

            return json_encode([
                'success' => true,
                'message' => 'User status updated successfully'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteUser($id)
    {
        try {
            $userModel = new User();
            $userModel->delete($id);

            return json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Helper methods for dashboard
    private function getTotalArticles()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM news_articles");
        $result = $stmt->fetch();
        return $result['count'];
    }

    private function getLiveMatchesCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM matches WHERE status = 'live'");
        $result = $stmt->fetch();
        return $result['count'];
    }

    private function getTotalUsers()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        return $result['count'];
    }

    private function getTotalTeams()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM teams");
        $result = $stmt->fetch();
        return $result['count'];
    }

    private function getRecentArticles($limit = 5)
    {
        $stmt = $this->db->query(
            "SELECT na.*, u.username as author_name, c.name as category_name 
             FROM news_articles na 
             LEFT JOIN users u ON na.author_id = u.id 
             LEFT JOIN categories c ON na.category_id = c.id 
             ORDER BY na.created_at DESC 
             LIMIT ?",
            [$limit]
        );
        return $stmt->fetchAll();
    }

    private function getRecentActivity()
    {
        // This would typically come from an activity log table
        // For now, we'll return recent articles and matches
        $activities = [];
        
        // Recent articles
        $stmt = $this->db->query(
            "SELECT 'article' as type, title as description, created_at, 'New article published' as action 
             FROM news_articles 
             ORDER BY created_at DESC 
             LIMIT 3"
        );
        $articles = $stmt->fetchAll();
        
        // Recent matches
        $stmt = $this->db->query(
            "SELECT 'match' as type, CONCAT(ht.name, ' vs ', at.name) as description, match_date as created_at, 'Match scheduled' as action 
             FROM matches m 
             LEFT JOIN teams ht ON m.home_team_id = ht.id 
             LEFT JOIN teams at ON m.away_team_id = at.id 
             ORDER BY match_date DESC 
             LIMIT 3"
        );
        $matches = $stmt->fetchAll();
        
        $activities = array_merge($articles, $matches);
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 5);
    }

    // Get categories for article form
    public function getCategories()
    {
        try {
            $categoryModel = new Category();
            $categories = $categoryModel->getAll();

            return json_encode([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Get leagues for match/team forms
    public function getLeagues()
    {
        try {
            $leagueModel = new League();
            $leagues = $leagueModel->getAll();

            return json_encode([
                'success' => true,
                'data' => $leagues
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
