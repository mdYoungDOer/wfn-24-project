<?php

namespace WFN24\Models;

class FootballMatch extends BaseModel
{
    protected $table = 'matches';
    protected $fillable = [
        'home_team_id', 'away_team_id', 'league_id', 'season_id',
        'match_date', 'kickoff_time', 'status', 'home_score',
        'away_score', 'home_penalties', 'away_penalties',
        'stadium', 'referee', 'attendance', 'weather',
        'home_possession', 'away_possession', 'home_shots',
        'away_shots', 'home_shots_on_target', 'away_shots_on_target',
        'home_corners', 'away_corners', 'home_fouls', 'away_fouls',
        'home_yellow_cards', 'away_yellow_cards', 'home_red_cards',
        'away_red_cards', 'api_match_id', 'is_live', 'highlights_url'
    ];

    public function createMatch(array $data): int
    {
        $data['status'] = $data['status'] ?? 'scheduled';
        $data['is_live'] = $data['is_live'] ?? false;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    public function updateMatch(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // If match is now live, update is_live flag
        if (isset($data['status']) && $data['status'] === 'live') {
            $data['is_live'] = true;
        }
        
        return $this->update($id, $data);
    }

    public function getLiveMatches(): array
    {
        $sql = "SELECT m.*, 
                       ht.name as home_team_name, ht.logo as home_team_logo,
                       at.name as away_team_name, at.logo as away_team_logo,
                       l.name as league_name, l.country as league_country
                FROM {$this->table} m
                LEFT JOIN teams ht ON m.home_team_id = ht.id
                LEFT JOIN teams at ON m.away_team_id = at.id
                LEFT JOIN leagues l ON m.league_id = l.id
                WHERE m.is_live = true AND m.status = 'live'
                ORDER BY m.kickoff_time ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getUpcomingMatches(int $limit = 10): array
    {
        $sql = "SELECT m.*, 
                       ht.name as home_team_name, ht.logo as home_team_logo,
                       at.name as away_team_name, at.logo as away_team_logo,
                       l.name as league_name, l.country as league_country
                FROM {$this->table} m
                LEFT JOIN teams ht ON m.home_team_id = ht.id
                LEFT JOIN teams at ON m.away_team_id = at.id
                LEFT JOIN leagues l ON m.league_id = l.id
                WHERE m.status = 'scheduled' AND m.match_date >= CURRENT_DATE
                ORDER BY m.match_date ASC, m.kickoff_time ASC
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecentMatches(int $limit = 10): array
    {
        $sql = "SELECT m.*, 
                       ht.name as home_team_name, ht.logo as home_team_logo,
                       at.name as away_team_name, at.logo as away_team_logo,
                       l.name as league_name, l.country as league_country
                FROM {$this->table} m
                LEFT JOIN teams ht ON m.home_team_id = ht.id
                LEFT JOIN teams at ON m.away_team_id = at.id
                LEFT JOIN leagues l ON m.league_id = l.id
                WHERE m.status = 'finished' AND m.match_date <= CURRENT_DATE
                ORDER BY m.match_date DESC, m.kickoff_time DESC
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMatchWithDetails(int $id): ?array
    {
        $sql = "SELECT m.*, 
                       ht.name as home_team_name, ht.logo as home_team_logo, ht.stadium as home_stadium,
                       at.name as away_team_name, at.logo as away_team_logo,
                       l.name as league_name, l.country as league_country, l.logo as league_logo
                FROM {$this->table} m
                LEFT JOIN teams ht ON m.home_team_id = ht.id
                LEFT JOIN teams at ON m.away_team_id = at.id
                LEFT JOIN leagues l ON m.league_id = l.id
                WHERE m.id = :id";
        
        $stmt = $this->db->query($sql, ['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getMatchesByTeam(int $teamId, int $limit = 20): array
    {
        $sql = "SELECT m.*, 
                       ht.name as home_team_name, ht.logo as home_team_logo,
                       at.name as away_team_name, at.logo as away_team_logo,
                       l.name as league_name, l.country as league_country
                FROM {$this->table} m
                LEFT JOIN teams ht ON m.home_team_id = ht.id
                LEFT JOIN teams at ON m.away_team_id = at.id
                LEFT JOIN leagues l ON m.league_id = l.id
                WHERE (m.home_team_id = :team_id OR m.away_team_id = :team_id)
                ORDER BY m.match_date DESC, m.kickoff_time DESC
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':team_id', $teamId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMatchesByLeague(int $leagueId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE league_id = :league_id";
        $countStmt = $this->db->query($countSql, ['league_id' => $leagueId]);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated data
        $sql = "SELECT m.*, 
                       ht.name as home_team_name, ht.logo as home_team_logo,
                       at.name as away_team_name, at.logo as away_team_logo,
                       l.name as league_name, l.country as league_country
                FROM {$this->table} m
                LEFT JOIN teams ht ON m.home_team_id = ht.id
                LEFT JOIN teams at ON m.away_team_id = at.id
                LEFT JOIN leagues l ON m.league_id = l.id
                WHERE m.league_id = :league_id
                ORDER BY m.match_date DESC, m.kickoff_time DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':league_id', $leagueId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    public function updateLiveScore(int $id, int $homeScore, int $awayScore): bool
    {
        $sql = "UPDATE {$this->table} SET 
                home_score = :home_score, 
                away_score = :away_score, 
                updated_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->query($sql, [
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'id' => $id
        ]);
        
        return $stmt->rowCount() > 0;
    }

    public function getMatchStatistics(int $id): ?array
    {
        $sql = "SELECT home_possession, away_possession, 
                       home_shots, away_shots, 
                       home_shots_on_target, away_shots_on_target,
                       home_corners, away_corners,
                       home_fouls, away_fouls,
                       home_yellow_cards, away_yellow_cards,
                       home_red_cards, away_red_cards
                FROM {$this->table} 
                WHERE id = :id";
        
        $stmt = $this->db->query($sql, ['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getByApiMatchId(string $apiMatchId): ?array
    {
        return $this->findBy('api_match_id', $apiMatchId);
    }
}
