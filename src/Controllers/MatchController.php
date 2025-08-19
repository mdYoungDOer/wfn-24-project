<?php

namespace WFN24\Controllers;

use WFN24\Config\Database;
use WFN24\Services\FootballApiService;
use Exception;

class MatchController
{
    private $db;
    private $apiService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->apiService = new FootballApiService();
    }

    public function getMatchDetails($matchId)
    {
        try {
            // First try to get from database
            $stmt = $this->db->getConnection()->prepare(
                "SELECT m.*, 
                        ht.name as home_team_name, ht.logo_url as home_team_logo,
                        at.name as away_team_name, at.logo_url as away_team_logo,
                        l.name as league_name, l.logo_url as league_logo
                 FROM matches m
                 LEFT JOIN teams ht ON m.home_team_id = ht.id
                 LEFT JOIN teams at ON m.away_team_id = at.id
                 LEFT JOIN leagues l ON m.league_id = l.id
                 WHERE m.id = ?"
            );
            $stmt->execute([$matchId]);
            $match = $stmt->fetch();

            if (!$match) {
                return ['error' => 'Match not found'];
            }

            // Get match statistics
            $stats = $this->getMatchStatistics($matchId);
            
            // Get lineups
            $lineups = $this->getMatchLineups($matchId);
            
            // Get match events
            $events = $this->getMatchEvents($matchId);

            return [
                'match' => $match,
                'statistics' => $stats,
                'lineups' => $lineups,
                'events' => $events
            ];

        } catch (Exception $e) {
            return ['error' => 'Failed to fetch match details: ' . $e->getMessage()];
        }
    }

    public function getMatchStatistics($matchId)
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT * FROM match_statistics WHERE match_id = ?"
            );
            $stmt->execute([$matchId]);
            $stats = $stmt->fetch();

            if (!$stats) {
                // Return default statistics structure
                return [
                    'possession_home' => 50,
                    'possession_away' => 50,
                    'shots_home' => 0,
                    'shots_away' => 0,
                    'shots_on_target_home' => 0,
                    'shots_on_target_away' => 0,
                    'corners_home' => 0,
                    'corners_away' => 0,
                    'fouls_home' => 0,
                    'fouls_away' => 0,
                    'yellow_cards_home' => 0,
                    'yellow_cards_away' => 0,
                    'red_cards_home' => 0,
                    'red_cards_away' => 0
                ];
            }

            return $stats;

        } catch (Exception $e) {
            return [];
        }
    }

    public function getMatchLineups($matchId)
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT ml.*, p.name as player_name, p.position, p.number
                 FROM match_lineups ml
                 LEFT JOIN players p ON ml.player_id = p.id
                 WHERE ml.match_id = ?
                 ORDER BY ml.team_id, ml.is_starting DESC, p.number"
            );
            $stmt->execute([$matchId]);
            $lineups = $stmt->fetchAll();

            // Group by team
            $homeLineup = [];
            $awayLineup = [];
            $homeSubs = [];
            $awaySubs = [];

            foreach ($lineups as $player) {
                $playerData = [
                    'id' => $player['player_id'],
                    'name' => $player['player_name'],
                    'position' => $player['position'],
                    'number' => $player['number'],
                    'is_starting' => $player['is_starting']
                ];

                if ($player['team_id'] == 1) { // Assuming 1 is home team
                    if ($player['is_starting']) {
                        $homeLineup[] = $playerData;
                    } else {
                        $homeSubs[] = $playerData;
                    }
                } else {
                    if ($player['is_starting']) {
                        $awayLineup[] = $playerData;
                    } else {
                        $awaySubs[] = $playerData;
                    }
                }
            }

            return [
                'home' => [
                    'starting' => $homeLineup,
                    'substitutes' => $homeSubs
                ],
                'away' => [
                    'starting' => $awayLineup,
                    'substitutes' => $awaySubs
                ]
            ];

        } catch (Exception $e) {
            return ['home' => ['starting' => [], 'substitutes' => []], 'away' => ['starting' => [], 'substitutes' => []]];
        }
    }

    public function getMatchEvents($matchId)
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT me.*, p.name as player_name
                 FROM match_events me
                 LEFT JOIN players p ON me.player_id = p.id
                 WHERE me.match_id = ?
                 ORDER BY me.minute, me.id"
            );
            $stmt->execute([$matchId]);
            return $stmt->fetchAll();

        } catch (Exception $e) {
            return [];
        }
    }

    public function getLiveMatches()
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT m.*, 
                        ht.name as home_team_name, ht.logo_url as home_team_logo,
                        at.name as away_team_name, at.logo_url as away_team_logo,
                        l.name as league_name
                 FROM matches m
                 LEFT JOIN teams ht ON m.home_team_id = ht.id
                 LEFT JOIN teams at ON m.away_team_id = at.id
                 LEFT JOIN leagues l ON m.league_id = l.id
                 WHERE m.status = 'LIVE'
                 ORDER BY m.kickoff_time DESC
                 LIMIT 10"
            );
            $stmt->execute();
            return $stmt->fetchAll();

        } catch (Exception $e) {
            return [];
        }
    }

    public function getUpcomingMatches($limit = 10)
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT m.*, 
                        ht.name as home_team_name, ht.logo_url as home_team_logo,
                        at.name as away_team_name, at.logo_url as away_team_logo,
                        l.name as league_name
                 FROM matches m
                 LEFT JOIN teams ht ON m.home_team_id = ht.id
                 LEFT JOIN teams at ON m.away_team_id = at.id
                 LEFT JOIN leagues l ON m.league_id = l.id
                 WHERE m.kickoff_time > NOW()
                 ORDER BY m.kickoff_time ASC
                 LIMIT ?"
            );
            $stmt->execute([$limit]);
            return $stmt->fetchAll();

        } catch (Exception $e) {
            return [];
        }
    }
}
