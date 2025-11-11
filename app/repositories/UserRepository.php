<?php
// File: app/repositories/UserRepository.php

class UserRepository
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->db->query(
            "SELECT * FROM users WHERE username = ? AND is_active = 1",
            [$username]
        );
        
        $data = $stmt->fetch();
        
        return $data ? new User($data) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->query(
            "SELECT * FROM users WHERE email = ? AND is_active = 1",
            [$email]
        );
        
        $data = $stmt->fetch();
        
        return $data ? new User($data) : null;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->query(
            "SELECT * FROM users WHERE id = ? AND is_active = 1",
            [$id]
        );
        
        $data = $stmt->fetch();
        
        return $data ? new User($data) : null;
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY id DESC");
        $results = $stmt->fetchAll();
        
        return array_map(fn($data) => new User($data), $results);
    }

    public function create(array $data): int
    {
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (username, email, password, role, full_name) 
                VALUES (?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['role'] ?? 'staff',
            $data['full_name']
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE users SET 
                username = ?, 
                email = ?, 
                role = ?, 
                full_name = ?,
                is_active = ?
                WHERE id = ?";
        
        $stmt = $this->db->query($sql, [
            $data['username'],
            $data['email'],
            $data['role'],
            $data['full_name'],
            $data['is_active'] ?? 1,
            $id
        ]);
        
        return $stmt->rowCount() > 0;
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $this->db->query($sql, [$hashedPassword, $id]);
        
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = "UPDATE users SET is_active = 0 WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        
        return $stmt->rowCount() > 0;
    }

    public function recordLoginAttempt(string $username, string $ipAddress, bool $success): void
    {
        $sql = "INSERT INTO login_attempts (username, ip_address, success) 
                VALUES (?, ?, ?)";
        
        $this->db->query($sql, [$username, $ipAddress, $success ? 1 : 0]);
    }

    public function getRecentFailedAttempts(string $username, int $minutes = 15): int
    {
        $sql = "SELECT COUNT(*) as count 
                FROM login_attempts 
                WHERE username = ? 
                AND success = 0 
                AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        
        $stmt = $this->db->query($sql, [$username, $minutes]);
        $result = $stmt->fetch();
        
        return (int) ($result['count'] ?? 0);
    }

    public function clearLoginAttempts(string $username): void
    {
        $sql = "DELETE FROM login_attempts WHERE username = ?";
        $this->db->query($sql, [$username]);
    }

    public function countByRole(string $role): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) as total FROM users WHERE role = ? AND is_active = 1",
            [$role]
        );
        
        return (int) $stmt->fetch()['total'];
    }
}