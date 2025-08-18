<?php

namespace WFN24\Models;

class League extends BaseModel
{
    protected $table = 'leagues';
    protected $fillable = [
        'name', 'country', 'type', 'season', 'logo',
        'api_league_id', 'is_active', 'priority'
    ];

    public function createLeague(array $data): int
    {
        $data['is_active'] = $data['is_active'] ?? true;
        $data['priority'] = $data['priority'] ?? 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    public function updateLeague(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    public function getActiveLeagues(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = true ORDER BY priority DESC, name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getLeaguesByCountry(string $country): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE country = :country AND is_active = true ORDER BY priority DESC, name ASC";
        $stmt = $this->db->query($sql, ['country' => $country]);
        return $stmt->fetchAll();
    }

    public function getLeagueWithDetails(int $id): ?array
    {
        $sql = "SELECT l.*, 
                       COUNT(DISTINCT t.id) as team_count,
                       COUNT(DISTINCT m.id) as match_count
                FROM {$this->table} l
                LEFT JOIN teams t ON l.id = t.league_id
                LEFT JOIN matches m ON l.id = m.league_id
                WHERE l.id = :id
                GROUP BY l.id";
        
        $stmt = $this->db->query($sql, ['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getByApiLeagueId(string $apiLeagueId): ?array
    {
        return $this->findBy('api_league_id', $apiLeagueId);
    }

    public function searchLeagues(string $query): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = true AND 
                (name ILIKE :query OR country ILIKE :query)
                ORDER BY priority DESC, name ASC";
        
        $stmt = $this->db->query($sql, ['query' => "%{$query}%"]);
        return $stmt->fetchAll();
    }

    public function getMajorLeagues(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE priority > 0 AND is_active = true ORDER BY priority DESC, name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
