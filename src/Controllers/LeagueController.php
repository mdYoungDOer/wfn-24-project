<?php

namespace WFN24\Controllers;

use WFN24\Config\Database;
use WFN24\Services\FootballApiService;
use Exception;

class LeagueController
{
    private $db;
    private $apiService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->apiService = new FootballApiService();
    }

    public function getLeagueDetails($leagueId)
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT * FROM leagues WHERE id = ?"
            );
            $stmt->execute([$leagueId]);
            $league = $stmt->fetch();

            if (!$league) {
                return ['error' => 'League not found'];
            }

            return [
                'league' => $league,
                'standings' => $this->getLeagueStandings($leagueId),
                'top_scorers' => $this->getTopScorers($leagueId),
                'recent_matches' => $this->getRecentMatches($leagueId),
                'upcoming_matches' => $this->getUpcomingMatches($leagueId)
            ];

        } catch (Exception $e) {
            return ['error' => 'Failed to fetch league details: ' . $e->getMessage()];
        }
    }

    public function getLeagueStandings($leagueId)
    {
        try {
            // For now, return a mock standings structure
            // In a real implementation, this would calculate based on match results
            $stmt = $this->db->getConnection()->prepare(
                "SELECT t.id, t.name, t.logo_url,
                        COALESCE(SUM(CASE WHEN m.home_team_id = t.id AND m.home_score > m.away_score THEN 3
                                         WHEN m.away_team_id = t.id AND m.away_score > m.home_score THEN 3
                                         WHEN m.home_score = m.away_score THEN 1
                                         ELSE 0 END), 0) as points,
                        COALESCE(COUNT(CASE WHEN m.home_team_id = t.id OR m.away_team_id = t.id THEN 1 END), 0) as played,
                        COALESCE(SUM(CASE WHEN m.home_team_id = t.id AND m.home_score > m.away_score THEN 1
                                         WHEN m.away_team_id = t.id AND m.away_score > m.home_score THEN 1
                                         ELSE 0 END), 0) as won,
                        COALESCE(SUM(CASE WHEN m.home_score = m.away_score THEN 1 ELSE 0 END), 0) as drawn,
                        COALESCE(SUM(CASE WHEN m.home_team_id = t.id AND m.home_score < m.away_score THEN 1
                                         WHEN m.away_team_id = t.id AND m.away_score < m.home_score THEN 1
                                         ELSE 0 END), 0) as lost,
                        COALESCE(SUM(CASE WHEN m.home_team_id = t.id THEN m.home_score ELSE m.away_score END), 0) as goals_for,
                        COALESCE(SUM(CASE WHEN m.home_team_id = t.id THEN m.away_score ELSE m.home_score END), 0) as goals_against
                 FROM teams t
                 LEFT JOIN matches m ON (m.home_team_id = t.id OR m.away_team_id = t.id) 
                    AND m.league_id = ? AND m.status = 'FINISHED'
                 WHERE t.is_active = TRUE
                 GROUP BY t.id, t.name, t.logo_url
                 ORDER BY points DESC, (goals_for - goals_against) DESC, goals_for DESC"
            );
            $stmt->execute([$leagueId]);
            $standings = $stmt->fetchAll();

            // Add position
            foreach ($standings as $index => $team) {
                $standings[$index]['position'] = $index + 1;
                $standings[$index]['goal_difference'] = $team['goals_for'] - $team['goals_against'];
            }

            return $standings;

        } catch (Exception $e) {
            return [];
        }
    }

    public function getTopScorers($leagueId, $limit = 20)
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT p.id, p.name, p.position, p.team_id, t.name as team_name, t.logo_url as team_logo,
                        COALESCE(SUM(CASE WHEN me.event_type = 'goal' THEN 1 ELSE 0 END), 0) as goals,
                        COALESCE(SUM(CASE WHEN me.event_type = 'assist' THEN 1 ELSE 0 END), 0) as assists
                 FROM players p
                 LEFT JOIN teams t ON p.team_id = t.id
                 LEFT JOIN match_events me ON p.id = me.player_id
                 LEFT JOIN matches m ON me.match_id = m.id AND m.league_id = ?
                 WHERE p.is_active = TRUE
                 GROUP BY p.id, p.name, p.position, p.team_id, t.name, t.logo_url
                 HAVING goals > 0 OR assists > 0
                 ORDER BY goals DESC, assists DESC
                 LIMIT ?"
            );
            $stmt->execute([$leagueId, $limit]);
            return $stmt->fetchAll();

        } catch (Exception $e) {
            return [];
        }
    }

    public function getRecentMatches($leagueId, $limit = 10)
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT m.*, 
                        ht.name as home_team_name, ht.logo_url as home_team_logo,
                        at.name as away_team_name, at.logo_url as away_team_logo
                 FROM matches m
                 LEFT JOIN teams ht ON m.home_team_id = ht.id
                 LEFT JOIN teams at ON m.away_team_id = at.id
                 WHERE m.league_id = ? AND m.status = 'FINISHED'
                 ORDER BY m.kickoff_time DESC
                 LIMIT ?"
            );
            $stmt->execute([$leagueId, $limit]);
            return $stmt->fetchAll();

        } catch (Exception $e) {
            return [];
        }
    }

    public function getUpcomingMatches($leagueId, $limit = 10)
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT m.*, 
                        ht.name as home_team_name, ht.logo_url as home_team_logo,
                        at.name as away_team_name, at.logo_url as away_team_logo
                 FROM matches m
                 LEFT JOIN teams ht ON m.home_team_id = ht.id
                 LEFT JOIN teams at ON m.away_team_id = at.id
                 WHERE m.league_id = ? AND m.kickoff_time > NOW()
                 ORDER BY m.kickoff_time ASC
                 LIMIT ?"
            );
            $stmt->execute([$leagueId, $limit]);
            return $stmt->fetchAll();

        } catch (Exception $e) {
            return [];
        }
    }

    public function getAllLeagues()
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT * FROM leagues WHERE is_active = TRUE ORDER BY name"
            );
            $stmt->execute();
            return $stmt->fetchAll();

        } catch (Exception $e) {
            return [];
        }
    }

    public function getLeagueFixtures($leagueId, $page = 1, $perPage = 20)
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $stmt = $this->db->getConnection()->prepare(
                "SELECT m.*, 
                        ht.name as home_team_name, ht.logo_url as home_team_logo,
                        at.name as away_team_name, at.logo_url as away_team_logo
                 FROM matches m
                 LEFT JOIN teams ht ON m.home_team_id = ht.id
                 LEFT JOIN teams at ON m.away_team_id = at.id
                 WHERE m.league_id = ?
                 ORDER BY m.kickoff_time DESC
                 LIMIT ? OFFSET ?"
            );
            $stmt->execute([$leagueId, $perPage, $offset]);
            $matches = $stmt->fetchAll();

            // Get total count
            $stmt = $this->db->getConnection()->prepare(
                "SELECT COUNT(*) as total FROM matches WHERE league_id = ?"
            );
            $stmt->execute([$leagueId]);
            $total = $stmt->fetch()['total'];

            return [
                'matches' => $matches,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ];

        } catch (Exception $e) {
            return ['matches' => [], 'pagination' => []];
        }
    }
}
