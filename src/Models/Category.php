<?php

namespace WFN24\Models;

class Category extends BaseModel
{
    protected $table = 'categories';
    protected $fillable = [
        'name', 'slug', 'description', 'color', 'icon',
        'is_active', 'sort_order'
    ];

    public function createCategory(array $data): int
    {
        $data['slug'] = $this->generateSlug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    public function updateCategory(int $id, array $data): bool
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    public function getActiveCategories(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = true ORDER BY sort_order ASC, name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    public function getCategoryWithArticleCount(int $id): ?array
    {
        $sql = "SELECT c.*, COUNT(na.id) as article_count
                FROM {$this->table} c
                LEFT JOIN news_articles na ON c.id = na.category_id AND na.status = 'published'
                WHERE c.id = :id
                GROUP BY c.id";
        
        $stmt = $this->db->query($sql, ['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = trim($slug, '-');
        
        // Check if slug exists
        $existing = $this->findBy('slug', $slug);
        if ($existing) {
            $counter = 1;
            $originalSlug = $slug;
            while ($this->findBy('slug', $slug)) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }
        
        return $slug;
    }
}
