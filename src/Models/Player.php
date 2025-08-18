<?php

namespace WFN24\Models;

class Player extends BaseModel
{
    protected $table = 'players';
    protected $fillable = [
        'name', 'first_name', 'last_name', 'date_of_birth',
        'nationality', 'position', 'shirt_number', 'height',
        'weight', 'preferred_foot', 'photo', 'team_id',
        'api_player_id', 'is_active', 'market_value',
        'contract_until', 'agent'
    ];

    public function createPlayer(array $data): int
    {
        $data['is_active'] = $data['is_active'] ?? true;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    public function updatePlayer(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    public function getActivePlayers(): array
    {
        $sql = "SELECT p.*, t.name as team_name, t.logo as team_logo
                FROM {$this->table} p
                LEFT JOIN teams t ON p.team_id = t.id
                WHERE p.is_active = true
                ORDER BY p.name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getPlayersByTeam(int $teamId): array
    {
        $sql = "SELECT p.*, t.name as team_name, t.logo as team_logo
                FROM {$this->table} p
                LEFT JOIN teams t ON p.team_id = t.id
                WHERE p.team_id = :team_id AND p.is_active = true
                ORDER BY p.position ASC, p.shirt_number ASC";
        
        $stmt = $this->db->query($sql, ['team_id' => $teamId]);
        return $stmt->fetchAll();
    }

    public function getPlayerWithDetails(int $id): ?array
    {
        $sql = "SELECT p.*, t.name as team_name, t.logo as team_logo, t.stadium as team_stadium
                FROM {$this->table} p
                LEFT JOIN teams t ON p.team_id = t.id
                WHERE p.id = :id";
        
        $stmt = $this->db->query($sql, ['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getByApiPlayerId(string $apiPlayerId): ?array
    {
        return $this->findBy('api_player_id', $apiPlayerId);
    }

    public function searchPlayers(string $query): array
    {
        $sql = "SELECT p.*, t.name as team_name, t.logo as team_logo
                FROM {$this->table} p
                LEFT JOIN teams t ON p.team_id = t.id
                WHERE p.is_active = true AND 
                (p.name ILIKE :query OR p.first_name ILIKE :query OR p.last_name ILIKE :query)
                ORDER BY p.name ASC";
        
        $stmt = $this->db->query($sql, ['query' => "%{$query}%"]);
        return $stmt->fetchAll();
    }

    public function getPlayersByPosition(string $position): array
    {
        $sql = "SELECT p.*, t.name as team_name, t.logo as team_logo
                FROM {$this->table} p
                LEFT JOIN teams t ON p.team_id = t.id
                WHERE p.position = :position AND p.is_active = true
                ORDER BY p.name ASC";
        
        $stmt = $this->db->query($sql, ['position' => $position]);
        return $stmt->fetchAll();
    }

    public function getPlayersByNationality(string $nationality): array
    {
        $sql = "SELECT p.*, t.name as team_name, t.logo as team_logo
                FROM {$this->table} p
                LEFT JOIN teams t ON p.team_id = t.id
                WHERE p.nationality = :nationality AND p.is_active = true
                ORDER BY p.name ASC";
        
        $stmt = $this->db->query($sql, ['nationality' => $nationality]);
        return $stmt->fetchAll();
    }
}
