<?php
// File: app/repositories/ReservationActivityRepository.php

class ReservationActivityRepository
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function log(int $reservationId, int $userId, string $action, ?string $oldStatus = null, ?string $newStatus = null, ?string $notes = null): int
    {
        $sql = "INSERT INTO reservation_activities 
                (reservation_id, user_id, action, old_status, new_status, notes) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $reservationId,
            $userId,
            $action,
            $oldStatus,
            $newStatus,
            $notes
        ]);
        
        return $this->db->lastInsertId();
    }

    public function getByReservation(int $reservationId): array
    {
        $sql = "SELECT ra.*, u.username, u.full_name, u.role 
                FROM reservation_activities ra
                JOIN users u ON ra.user_id = u.id
                WHERE ra.reservation_id = ?
                ORDER BY ra.created_at DESC";
        
        $stmt = $this->db->query($sql, [$reservationId]);
        return $stmt->fetchAll();
    }

    public function getRecent(int $limit = 50): array
    {
        $sql = "SELECT ra.*, u.username, u.full_name, u.role, 
                       r.customer_name, c.name as court_name
                FROM reservation_activities ra
                JOIN users u ON ra.user_id = u.id
                JOIN reservations r ON ra.reservation_id = r.id
                JOIN courts c ON r.court_id = c.id
                ORDER BY ra.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    public function getByUser(int $userId, int $limit = 20): array
    {
        $sql = "SELECT ra.*, r.customer_name, c.name as court_name
                FROM reservation_activities ra
                JOIN reservations r ON ra.reservation_id = r.id
                JOIN courts c ON r.court_id = c.id
                WHERE ra.user_id = ?
                ORDER BY ra.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$userId, $limit]);
        return $stmt->fetchAll();
    }
}