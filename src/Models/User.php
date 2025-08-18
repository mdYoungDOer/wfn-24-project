<?php

namespace WFN24\Models;

class User extends BaseModel
{
    protected $table = 'users';
    protected $fillable = [
        'username', 'email', 'password', 'first_name', 'last_name',
        'avatar', 'role', 'is_active', 'email_verified_at', 'last_login_at'
    ];
    protected $hidden = ['password'];

    public function createUser(array $data): int
    {
        // Hash password before saving
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $data['role'] = $data['role'] ?? 'user';
        $data['is_active'] = $data['is_active'] ?? true;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }

    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findBy('email', $email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }
        
        if (!$user['is_active']) {
            return null;
        }
        
        // Update last login
        $this->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
        
        return $this->filterHidden($user);
    }

    public function getByEmail(string $email): ?array
    {
        $user = $this->findBy('email', $email);
        return $user ? $this->filterHidden($user) : null;
    }

    public function getAdmins(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE role = 'admin' ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getActiveUsers(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = true ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function updateLastLogin(int $userId): bool
    {
        return $this->update($userId, ['last_login_at' => date('Y-m-d H:i:s')]);
    }
}
