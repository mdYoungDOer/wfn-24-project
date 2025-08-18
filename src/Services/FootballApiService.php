<?php

namespace WFN24\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use WFN24\Config\Database;

class FootballApiService
{
    private $client;
    private $apiKey;
    private $baseUrl;
    private $db;

    public function __construct()
    {
        $this->apiKey = $_ENV['FOOTBALL_API_KEY'] ?? '';
        $this->baseUrl = $_ENV['FOOTBALL_API_BASE_URL'] ?? 'https://v3.football.api-sports.io';
        $this->db = Database::getInstance();
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'x-rapidapi-host' => 'v3.football.api-sports.io',
                'x-rapidapi-key' => $this->apiKey,
                'User-Agent' => 'WFN24/1.0'
            ],
            'timeout' => 30
        ]);
    }

    public function getLeagues(): array
    {
        $cacheKey = 'api_leagues';
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/leagues');
            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'])) {
                $this->cacheData($cacheKey, $data['response'], 3600); // Cache for 1 hour
                return $data['response'];
            }
            
            return [];
        } catch (RequestException $e) {
            error_log("Football API Error (Leagues): " . $e->getMessage());
            return [];
        }
    }

    public function getTeams(int $leagueId): array
    {
        $cacheKey = "api_teams_league_{$leagueId}";
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/teams', [
                'query' => ['league' => $leagueId, 'season' => date('Y')]
            ]);
            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'])) {
                $this->cacheData($cacheKey, $data['response'], 3600);
                return $data['response'];
            }
            
            return [];
        } catch (RequestException $e) {
            error_log("Football API Error (Teams): " . $e->getMessage());
            return [];
        }
    }

    public function getPlayers(int $teamId): array
    {
        $cacheKey = "api_players_team_{$teamId}";
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/players', [
                'query' => ['team' => $teamId, 'season' => date('Y')]
            ]);
            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'])) {
                $this->cacheData($cacheKey, $data['response'], 3600);
                return $data['response'];
            }
            
            return [];
        } catch (RequestException $e) {
            error_log("Football API Error (Players): " . $e->getMessage());
            return [];
        }
    }

    public function getLiveMatches(): array
    {
        $cacheKey = 'api_live_matches';
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/fixtures', [
                'query' => ['live' => 'all']
            ]);
            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'])) {
                $this->cacheData($cacheKey, $data['response'], 60); // Cache for 1 minute
                return $data['response'];
            }
            
            return [];
        } catch (RequestException $e) {
            error_log("Football API Error (Live Matches): " . $e->getMessage());
            return [];
        }
    }

    public function getFixtures(int $leagueId, string $season = null): array
    {
        if (!$season) {
            $season = date('Y');
        }
        
        $cacheKey = "api_fixtures_league_{$leagueId}_season_{$season}";
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/fixtures', [
                'query' => [
                    'league' => $leagueId,
                    'season' => $season
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'])) {
                $this->cacheData($cacheKey, $data['response'], 1800); // Cache for 30 minutes
                return $data['response'];
            }
            
            return [];
        } catch (RequestException $e) {
            error_log("Football API Error (Fixtures): " . $e->getMessage());
            return [];
        }
    }

    public function getMatchDetails(int $fixtureId): ?array
    {
        $cacheKey = "api_match_{$fixtureId}";
        $cached = $this->getCachedData($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client->get('/fixtures', [
                'query' => ['id' => $fixtureId]
            ]);
            $data = json_decode($response->getBody(), true);
            
            if (isset($data['response'][0])) {
                $this->cacheData($cacheKey, $data['response'][0], 300); // Cache for 5 minutes
                return $data['response'][0];
            }
            
            return null;
        } catch (RequestException $e) {
            error_log("Football API Error (Match Details): " . $e->getMessage());
            return null;
        }
    }

    public function getStandings(int $leagueId, string $season = null): array
    {
        if (!$season) {
            $season = date('Y');
        }
        
        $cacheKey = "api_standings_league_{$leagueId}_season_{$season}";
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
            
            if (isset($data['response'])) {
                $this->cacheData($cacheKey, $data['response'], 3600); // Cache for 1 hour
                return $data['response'];
            }
            
            return [];
        } catch (RequestException $e) {
            error_log("Football API Error (Standings): " . $e->getMessage());
            return [];
        }
    }

    public function getTopScorers(int $leagueId, string $season = null): array
    {
        if (!$season) {
            $season = date('Y');
        }
        
        $cacheKey = "api_scorers_league_{$leagueId}_season_{$season}";
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
                $this->cacheData($cacheKey, $data['response'], 3600);
                return $data['response'];
            }
            
            return [];
        } catch (RequestException $e) {
            error_log("Football API Error (Top Scorers): " . $e->getMessage());
            return [];
        }
    }

    private function getCachedData(string $key): ?array
    {
        $sql = "SELECT cache_value FROM api_cache WHERE cache_key = :key AND expires_at > NOW()";
        $stmt = $this->db->query($sql, ['key' => $key]);
        $result = $stmt->fetch();
        
        return $result ? json_decode($result['cache_value'], true) : null;
    }

    private function cacheData(string $key, array $data, int $ttlSeconds): void
    {
        $expiresAt = date('Y-m-d H:i:s', time() + $ttlSeconds);
        $cacheValue = json_encode($data);
        
        $sql = "INSERT INTO api_cache (cache_key, cache_value, expires_at) 
                VALUES (:key, :value, :expires_at)
                ON CONFLICT (cache_key) 
                DO UPDATE SET cache_value = :value, expires_at = :expires_at";
        
        $this->db->query($sql, [
            'key' => $key,
            'value' => $cacheValue,
            'expires_at' => $expiresAt
        ]);
    }

    public function clearExpiredCache(): void
    {
        $sql = "DELETE FROM api_cache WHERE expires_at <= NOW()";
        $this->db->query($sql);
    }
}
