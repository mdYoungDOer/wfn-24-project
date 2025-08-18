<?php

namespace WFN24\Models;

class Team extends BaseModel
{
    protected $table = 'teams';
    protected $fillable = [
        'name', 'short_name', 'country', 'city', 'stadium',
        'capacity', 'founded_year', 'logo', 'website',
        'api_team_id', 'league_id', 'is_active'
    ];

    public function createTeam(array $data): int
    {
        $data['is_active'] = $data['is_active'] ?? true;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    public function updateTeam(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    public function getActiveTeams(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = true ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getTeamsByLeague(int $leagueId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE league_id = :league_id AND is_active = true ORDER BY name ASC";
        $stmt = $this->db->query($sql, ['league_id' => $leagueId]);
        return $stmt->fetchAll();
    }

    public function getTeamWithDetails(int $id): ?array
    {
        $sql = "SELECT t.*, l.name as league_name, l.country as league_country
                FROM {$this->table} t
                LEFT JOIN leagues l ON t.league_id = l.id
                WHERE t.id = :id";
        
        $stmt = $this->db->query($sql, ['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getByApiTeamId(string $apiTeamId): ?array
    {
        return $this->findBy('api_team_id', $apiTeamId);
    }

    public function searchTeams(string $query): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = true AND 
                (name ILIKE :query OR short_name ILIKE :query OR city ILIKE :query)
                ORDER BY name ASC";
        
        $stmt = $this->db->query($sql, ['query' => "%{$query}%"]);
        return $stmt->fetchAll();
    }
}
