<?php
// File: app/repositories/NotificationRepository.php

class NotificationRepository
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function create(int $userId, string $type, string $title, string $message, ?string $link = null): int
    {
        $sql = "INSERT INTO notifications (user_id, type, title, message, link) 
                VALUES (?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [$userId, $type, $title, $message, $link]);
        return $this->db->lastInsertId();
    }

    public function getUnreadByUser(int $userId): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM notifications 
             WHERE user_id = ? AND is_read = 0 
             ORDER BY created_at DESC",
            [$userId]
        );
        
        $results = $stmt->fetchAll();
        return array_map(fn($data) => new Notification($data), $results);
    }

    public function getAllByUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM notifications 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        );
        
        $results = $stmt->fetchAll();
        return array_map(fn($data) => new Notification($data), $results);
    }

    public function countUnread(int $userId): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) as total FROM notifications 
             WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        
        return (int) $stmt->fetch()['total'];
    }

    public function markAsRead(int $id): bool
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    public function markAllAsRead(int $userId): bool
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        $stmt = $this->db->query($sql, [$userId]);
        return $stmt->rowCount() > 0;
    }

    public function deleteOld(int $days = 30): int
    {
        $sql = "DELETE FROM notifications 
                WHERE is_read = 1 
                AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $stmt = $this->db->query($sql, [$days]);
        return $stmt->rowCount();
    }
}