<?php

namespace WFN24\Models;

class NewsArticle extends BaseModel
{
    protected $table = 'news_articles';
    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'featured_image',
        'category_id', 'author_id', 'status', 'published_at',
        'meta_title', 'meta_description', 'tags', 'is_featured',
        'view_count', 'seo_score'
    ];

    public function createArticle(array $data): int
    {
        $data['slug'] = $this->generateSlug($data['title']);
        $data['status'] = $data['status'] ?? 'draft';
        $data['is_featured'] = $data['is_featured'] ?? false;
        $data['view_count'] = $data['view_count'] ?? 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        if ($data['status'] === 'published') {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->create($data);
    }

    public function updateArticle(int $id, array $data): bool
    {
        if (isset($data['title']) && !isset($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // If status changed to published, set published_at
        $current = $this->find($id);
        if ($current && $current['status'] !== 'published' && $data['status'] === 'published') {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($id, $data);
    }

    public function getPublishedArticles(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'published'";
        $countStmt = $this->db->query($countSql);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated data
        $sql = "SELECT na.*, u.first_name, u.last_name, c.name as category_name 
                FROM {$this->table} na 
                LEFT JOIN users u ON na.author_id = u.id 
                LEFT JOIN categories c ON na.category_id = c.id 
                WHERE na.status = 'published' 
                ORDER BY na.published_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->getConnection()->prepare($sql);
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

    public function getFeaturedArticles(int $limit = 5): array
    {
        $sql = "SELECT na.*, u.first_name, u.last_name, c.name as category_name 
                FROM {$this->table} na 
                LEFT JOIN users u ON na.author_id = u.id 
                LEFT JOIN categories c ON na.category_id = c.id 
                WHERE na.status = 'published' AND na.is_featured = true 
                ORDER BY na.published_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByCategory(int $categoryId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE category_id = :category_id AND status = 'published'";
        $countStmt = $this->db->query($countSql, ['category_id' => $categoryId]);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated data
        $sql = "SELECT na.*, u.first_name, u.last_name, c.name as category_name 
                FROM {$this->table} na 
                LEFT JOIN users u ON na.author_id = u.id 
                LEFT JOIN categories c ON na.category_id = c.id 
                WHERE na.category_id = :category_id AND na.status = 'published' 
                ORDER BY na.published_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':category_id', $categoryId, \PDO::PARAM_INT);
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

    public function getBySlug(string $slug): ?array
    {
        $sql = "SELECT na.*, u.first_name, u.last_name, c.name as category_name 
                FROM {$this->table} na 
                LEFT JOIN users u ON na.author_id = u.id 
                LEFT JOIN categories c ON na.category_id = c.id 
                WHERE na.slug = :slug AND na.status = 'published'";
        
        $stmt = $this->db->query($sql, ['slug' => $slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function incrementViewCount(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function searchArticles(string $query, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                     WHERE status = 'published' AND 
                     (title ILIKE :query OR content ILIKE :query OR excerpt ILIKE :query)";
        $countStmt = $this->db->query($countSql, ['query' => "%{$query}%"]);
        $total = $countStmt->fetch()['total'];
        
        // Get paginated data
        $sql = "SELECT na.*, u.first_name, u.last_name, c.name as category_name 
                FROM {$this->table} na 
                LEFT JOIN users u ON na.author_id = u.id 
                LEFT JOIN categories c ON na.category_id = c.id 
                WHERE na.status = 'published' AND 
                (na.title ILIKE :query OR na.content ILIKE :query OR na.excerpt ILIKE :query)
                ORDER BY na.published_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%");
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

    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
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
