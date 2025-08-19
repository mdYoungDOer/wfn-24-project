<?php

namespace WFN24\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class FootballApiService
{
    private $client;
    private $apiKey;
    private $baseUrl;
    private $logger;
    private $db;

    public function __construct()
    {
        $this->apiKey = $_ENV['FOOTBALL_API_KEY'] ?? '';
        $this->baseUrl = $_ENV['FOOTBALL_API_BASE_URL'] ?? 'https://v3.football.api-sports.io';
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'x-rapidapi-host' => 'v3.football.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
                'User-Agent' => 'WFN24/1.0'
            ],
            'timeout' => 30
        ]);

        $this->logger = new Logger('football_api');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/football_api.log', Logger::INFO));
        
        $this->db = \WFN24\Config\Database::getInstance();
    }

    /**
     * Get major leagues
     */
    public function getMajorLeagues()
    {
        $cacheKey = 'major_leagues';
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/leagues', [
                'query' => [
                    'country' => 'England,Spain,Germany,Italy,France',
                    'season' => date('Y')
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'])) {
                $leagues = array_map(function($league) {
                    return [
                        'api_league_id' => $league['league']['id'],
                        'name' => $league['league']['name'],
                        'country' => $league['country']['name'],
                        'logo_url' => $league['league']['logo'],
                        'type' => $league['league']['type'],
                        'season' => $league['seasons'][0]['year'] ?? date('Y')
                    ];
                }, $data['response']);

                $this->cacheData($cacheKey, $leagues, 3600); // Cache for 1 hour
                return $leagues;
            }
        } catch (RequestException $e) {
            $this->logger->error('Failed to fetch major leagues: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get live matches
     */
    public function getLiveMatches()
    {
        $cacheKey = 'live_matches';
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/fixtures', [
                'query' => [
                    'live' => 'all'
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'])) {
                $matches = array_map(function($fixture) {
                    return [
                        'api_match_id' => $fixture['fixture']['id'],
                        'status' => $fixture['fixture']['status']['short'],
                        'home_team' => [
                            'name' => $fixture['teams']['home']['name'],
                            'logo' => $fixture['teams']['home']['logo']
                        ],
                        'away_team' => [
                            'name' => $fixture['teams']['away']['name'],
                            'logo' => $fixture['teams']['away']['logo']
                        ],
                        'home_score' => $fixture['goals']['home'],
                        'away_score' => $fixture['goals']['away'],
                        'league' => [
                            'name' => $fixture['league']['name'],
                            'country' => $fixture['league']['country']
                        ],
                        'match_date' => $fixture['fixture']['date'],
                        'venue' => $fixture['fixture']['venue']['name'] ?? null,
                        'is_live' => true
                    ];
                }, $data['response']);

                $this->cacheData($cacheKey, $matches, 300); // Cache for 5 minutes
                return $matches;
            }
        } catch (RequestException $e) {
            $this->logger->error('Failed to fetch live matches: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get upcoming matches
     */
    public function getUpcomingMatches($limit = 10)
    {
        $cacheKey = 'upcoming_matches_' . $limit;
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/fixtures', [
                'query' => [
                    'date' => date('Y-m-d'),
                    'status' => 'NS',
                    'league' => '39,140,135,78,61', // Premier League, La Liga, Serie A, Bundesliga, Ligue 1
                    'season' => date('Y')
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'])) {
                $matches = array_slice(array_map(function($fixture) {
                    return [
                        'api_match_id' => $fixture['fixture']['id'],
                        'status' => $fixture['fixture']['status']['short'],
                        'home_team' => [
                            'name' => $fixture['teams']['home']['name'],
                            'logo' => $fixture['teams']['home']['logo']
                        ],
                        'away_team' => [
                            'name' => $fixture['teams']['away']['name'],
                            'logo' => $fixture['teams']['away']['logo']
                        ],
                        'league' => [
                            'name' => $fixture['league']['name'],
                            'country' => $fixture['league']['country']
                        ],
                        'match_date' => $fixture['fixture']['date'],
                        'venue' => $fixture['fixture']['venue']['name'] ?? null
                    ];
                }, $data['response']), 0, $limit);

                $this->cacheData($cacheKey, $matches, 1800); // Cache for 30 minutes
                return $matches;
            }
        } catch (RequestException $e) {
            $this->logger->error('Failed to fetch upcoming matches: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get top scorers for a league
     */
    public function getTopScorers($leagueId, $season = null)
    {
        if (!$season) {
            $season = date('Y');
        }

        $cacheKey = "top_scorers_{$leagueId}_{$season}";
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/players/topscorers', [
                'query' => [
                    'league' => $leagueId,
                    'season' => $season
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'])) {
                $scorers = array_map(function($player) {
                    return [
                        'player_id' => $player['player']['id'],
                        'name' => $player['player']['name'],
                        'photo' => $player['player']['photo'],
                        'team' => $player['statistics'][0]['team']['name'],
                        'team_logo' => $player['statistics'][0]['team']['logo'],
                        'goals' => $player['statistics'][0]['goals']['total'],
                        'assists' => $player['statistics'][0]['goals']['assists'] ?? 0,
                        'matches' => $player['statistics'][0]['games']['appearences']
                    ];
                }, $data['response']);

                $this->cacheData($cacheKey, $scorers, 3600); // Cache for 1 hour
                return $scorers;
            }
        } catch (RequestException $e) {
            $this->logger->error("Failed to fetch top scorers for league {$leagueId}: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Get league standings
     */
    public function getLeagueStandings($leagueId, $season = null)
    {
        if (!$season) {
            $season = date('Y');
        }

        $cacheKey = "standings_{$leagueId}_{$season}";
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/standings', [
                'query' => [
                    'league' => $leagueId,
                    'season' => $season
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'][0]['league']['standings'][0])) {
                $standings = array_map(function($team) {
                    return [
                        'position' => $team['rank'],
                        'team_id' => $team['team']['id'],
                        'team_name' => $team['team']['name'],
                        'team_logo' => $team['team']['logo'],
                        'played' => $team['all']['played'],
                        'won' => $team['all']['win'],
                        'drawn' => $team['all']['draw'],
                        'lost' => $team['all']['lose'],
                        'goals_for' => $team['all']['goals']['for'],
                        'goals_against' => $team['all']['goals']['against'],
                        'goal_difference' => $team['goalsDiff'],
                        'points' => $team['points']
                    ];
                }, $data['response'][0]['league']['standings'][0]);

                $this->cacheData($cacheKey, $standings, 3600); // Cache for 1 hour
                return $standings;
            }
        } catch (RequestException $e) {
            $this->logger->error("Failed to fetch standings for league {$leagueId}: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Get team information
     */
    public function getTeamInfo($teamId)
    {
        $cacheKey = "team_info_{$teamId}";
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/teams', [
                'query' => [
                    'id' => $teamId
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'][0])) {
                $team = $data['response'][0];
                $teamInfo = [
                    'api_team_id' => $team['team']['id'],
                    'name' => $team['team']['name'],
                    'short_name' => $team['team']['code'],
                    'country' => $team['team']['country'],
                    'founded' => $team['team']['founded'],
                    'logo_url' => $team['team']['logo'],
                    'stadium' => $team['venue']['name'] ?? null,
                    'capacity' => $team['venue']['capacity'] ?? null
                ];

                $this->cacheData($cacheKey, $teamInfo, 7200); // Cache for 2 hours
                return $teamInfo;
            }
        } catch (RequestException $e) {
            $this->logger->error("Failed to fetch team info for team {$teamId}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get player information
     */
    public function getPlayerInfo($playerId)
    {
        $cacheKey = "player_info_{$playerId}";
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/players', [
                'query' => [
                    'id' => $playerId
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'][0])) {
                $player = $data['response'][0];
                $playerInfo = [
                    'api_player_id' => $player['player']['id'],
                    'name' => $player['player']['name'],
                    'first_name' => $player['player']['firstname'],
                    'last_name' => $player['player']['lastname'],
                    'age' => $player['player']['age'],
                    'nationality' => $player['player']['nationality'],
                    'height' => $player['player']['height'],
                    'weight' => $player['player']['weight'],
                    'photo_url' => $player['player']['photo'],
                    'position' => $player['statistics'][0]['games']['position'] ?? null
                ];

                $this->cacheData($cacheKey, $playerInfo, 7200); // Cache for 2 hours
                return $playerInfo;
            }
        } catch (RequestException $e) {
            $this->logger->error("Failed to fetch player info for player {$playerId}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get cached data from database
     */
    private function getCachedData($key)
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT data FROM api_cache WHERE cache_key = ? AND expires_at > NOW()"
            );
            $stmt->execute([$key]);
            $result = $stmt->fetch();
            
            return $result ? json_decode($result['data'], true) : null;
        } catch (\Exception $e) {
            $this->logger->error('Cache read error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cache data in database
     */
    private function cacheData($key, $data, $ttl = 3600)
    {
        try {
            $expiresAt = date('Y-m-d H:i:s', time() + $ttl);
            $jsonData = json_encode($data);
            
            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO api_cache (cache_key, data, expires_at) 
                 VALUES (?, ?, ?) 
                 ON CONFLICT (cache_key) 
                 DO UPDATE SET data = EXCLUDED.data, expires_at = EXCLUDED.expires_at"
            );
            $stmt->execute([$key, $jsonData, $expiresAt]);
        } catch (\Exception $e) {
            $this->logger->error('Cache write error: ' . $e->getMessage());
        }
    }

    /**
     * Clear expired cache entries
     */
    public function clearExpiredCache()
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "DELETE FROM api_cache WHERE expires_at <= NOW()"
            );
            $stmt->execute();
        } catch (\Exception $e) {
            $this->logger->error('Cache cleanup error: ' . $e->getMessage());
        }
    }
}
