<?php

namespace WFN24\Models;

use WFN24\Config\Database;
use PDO;

abstract class BaseModel
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findBy(string $column, $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";
        $stmt = $this->db->query($sql, ['value' => $value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function where(string $column, $value): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";
        $stmt = $this->db->query($sql, ['value' => $value]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($sql, $data);
        
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        
        $stmt = $this->db->query($sql, $data);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $countStmt = $this->db->query($countSql);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated data
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    public function search(string $query, array $columns = []): array
    {
        if (empty($columns)) {
            $columns = $this->fillable;
        }
        
        $searchConditions = [];
        $params = [];
        
        foreach ($columns as $column) {
            $searchConditions[] = "{$column} ILIKE :search_{$column}";
            $params["search_{$column}"] = "%{$query}%";
        }
        
        $whereClause = implode(' OR ', $searchConditions);
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} ORDER BY {$this->primaryKey} DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getAllWithPagination(int $page = 1, int $limit = 10, string $search = ''): array
    {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            $searchConditions = [];
            foreach ($this->fillable as $column) {
                $searchConditions[] = "{$column} ILIKE :search_{$column}";
                $params["search_{$column}"] = "%{$search}%";
            }
            $whereClause = 'WHERE ' . implode(' OR ', $searchConditions);
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        $countStmt = $this->db->getConnection()->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated data
        $sql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        return $data;
    }

    public function getTotalCount(string $search = ''): int
    {
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            $searchConditions = [];
            foreach ($this->fillable as $column) {
                $searchConditions[] = "{$column} ILIKE :search_{$column}";
                $params["search_{$column}"] = "%{$search}%";
            }
            $whereClause = 'WHERE ' . implode(' OR ', $searchConditions);
        }
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        $countStmt = $this->db->getConnection()->prepare($countSql);
        $countStmt->execute($params);
        return (int) $countStmt->fetch()['total'];
    }

    protected function filterFillable(array $data): array
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function filterHidden(array $data): array
    {
        return array_diff_key($data, array_flip($this->hidden));
    }
}
