<?php
// File: app/repositories/ReservationRepository.php (FIXED VERSION)

class ReservationRepository
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function all($search = '', $sort = 'id', $order = 'ASC', $limit = 10, $offset = 0)
    {
        $query = "SELECT r.*, c.name AS court_name,
                         creator.full_name as created_by_name,
                         approver.full_name as approved_by_name
                  FROM reservations r 
                  JOIN courts c ON r.court_id = c.id
                  LEFT JOIN users creator ON r.created_by = creator.id
                  LEFT JOIN users approver ON r.approved_by = approver.id
                  WHERE r.deleted_at IS NULL";

        if ($search) {
            $search = "%{$search}%";
            $query .= " AND (r.customer_name LIKE ? OR c.name LIKE ?)";
        }

        $query .= " ORDER BY {$sort} {$order} LIMIT ? OFFSET ?";

        if ($search) {
            $stmt = $this->db->query($query, [$search, $search, $limit, $offset]);
        } else {
            $stmt = $this->db->query($query, [$limit, $offset]);
        }

        return $stmt->fetchAll();
    }

    public function count($search = '')
    {
        $query = "SELECT COUNT(*) as total FROM reservations WHERE deleted_at IS NULL";
        $params = [];

        if ($search) {
            $query .= " AND (customer_name LIKE ?)";
            $params[] = "%{$search}%";
        }

        $stmt = $this->db->query($query, $params);
        return $stmt->fetch()['total'];
    }

    public function find($id)
    {
        $stmt = $this->db->query("SELECT r.*, c.name AS court_name,
                                          creator.full_name as created_by_name,
                                          approver.full_name as approved_by_name
                                   FROM reservations r
                                   JOIN courts c ON r.court_id = c.id
                                   LEFT JOIN users creator ON r.created_by = creator.id
                                   LEFT JOIN users approver ON r.approved_by = approver.id
                                   WHERE r.id = ?", [$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO reservations 
                (customer_name, court_id, start_time, end_time, status, workflow_status, created_by, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['customer_name'],
            $data['court_id'],
            $data['start_time'],
            $data['end_time'],
            $data['status'] ?? 'pending',
            $data['workflow_status'] ?? 'pending_manager',
            $data['created_by'] ?? null,
            $data['notes'] ?? null
        ]);
        
        $reservationId = $this->db->lastInsertId();
        
        // Log activity
        if (isset($data['created_by'])) {
            $activityRepo = new ReservationActivityRepository();
            $activityRepo->log(
                $reservationId,
                $data['created_by'],
                'created',
                null,
                $data['workflow_status'] ?? 'pending_manager',
                'Reservasi dibuat'
            );
        }
        
        return $reservationId;
    }

    public function update($id, $data)
    {
        $sql = "UPDATE reservations 
                SET customer_name=?, court_id=?, start_time=?, end_time=?, status=?, notes=?
                WHERE id=?";
        
        return $this->db->query($sql, [
            $data['customer_name'],
            $data['court_id'],
            $data['start_time'],
            $data['end_time'],
            $data['status'],
            $data['notes'] ?? null,
            $id
        ]);
    }

    // === WORKFLOW METHODS ===

    public function getPendingForManager()
    {
        $stmt = $this->db->query(
            "SELECT r.*, c.name AS court_name, u.full_name as created_by_name
             FROM reservations r
             JOIN courts c ON r.court_id = c.id
             LEFT JOIN users u ON r.created_by = u.id
             WHERE r.workflow_status = 'pending_manager' 
             AND r.deleted_at IS NULL
             ORDER BY r.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function getPendingReschedule()
    {
        $stmt = $this->db->query(
            "SELECT r.*, c.name AS court_name, u.full_name as created_by_name
             FROM reservations r
             JOIN courts c ON r.court_id = c.id
             LEFT JOIN users u ON r.created_by = u.id
             WHERE r.workflow_status = 'pending_reschedule' 
             AND r.deleted_at IS NULL
             ORDER BY r.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function getPendingCancel()
    {
        $stmt = $this->db->query(
            "SELECT r.*, c.name AS court_name, u.full_name as created_by_name
             FROM reservations r
             JOIN courts c ON r.court_id = c.id
             LEFT JOIN users u ON r.created_by = u.id
             WHERE r.workflow_status = 'pending_cancel' 
             AND r.deleted_at IS NULL
             ORDER BY r.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function approveReservation(int $id, int $approverId, ?string $notes = null): bool
    {
        $reservation = $this->find($id);
        if (!$reservation) return false;

        $sql = "UPDATE reservations 
                SET workflow_status = 'approved', 
                    status = 'aktif',
                    approved_by = ?, 
                    approved_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->db->query($sql, [$approverId, $id]);
        
        if ($stmt->rowCount() > 0) {
            // Log activity
            $activityRepo = new ReservationActivityRepository();
            $activityRepo->log(
                $id,
                $approverId,
                'approved',
                $reservation['workflow_status'],
                'approved',
                $notes ?? 'Reservasi disetujui'
            );

            // Notify creator
            if ($reservation['created_by']) {
                $notificationRepo = new NotificationRepository();
                $notificationRepo->create(
                    $reservation['created_by'],
                    'reservation_approved',
                    'Reservasi Disetujui',
                    "Reservasi untuk {$reservation['customer_name']} telah disetujui",
                    "?c=reservation&a=edit&id={$id}"
                );
            }

            return true;
        }

        return false;
    }

    public function requestReschedule(int $id, int $managerId, string $reason): bool
    {
        $reservation = $this->find($id);
        if (!$reservation) return false;

        $sql = "UPDATE reservations 
                SET workflow_status = 'pending_reschedule',
                    rejection_reason = ?
                WHERE id = ?";
        
        $stmt = $this->db->query($sql, [$reason, $id]);
        
        if ($stmt->rowCount() > 0) {
            // Log activity
            $activityRepo = new ReservationActivityRepository();
            $activityRepo->log(
                $id,
                $managerId,
                'request_reschedule',
                $reservation['workflow_status'],
                'pending_reschedule',
                $reason
            );

            // Notify creator
            if ($reservation['created_by']) {
                $notificationRepo = new NotificationRepository();
                $notificationRepo->create(
                    $reservation['created_by'],
                    'reschedule_requested',
                    'Permintaan Reschedule',
                    "Reservasi untuk {$reservation['customer_name']} perlu dijadwalkan ulang: {$reason}",
                    "?c=reservation&a=edit&id={$id}"
                );
            }

            return true;
        }

        return false;
    }

    public function rejectReservation(int $id, int $managerId, string $reason): bool
    {
        $reservation = $this->find($id);
        if (!$reservation) return false;

        $sql = "UPDATE reservations 
                SET workflow_status = 'rejected',
                    status = 'dibatalkan',
                    rejection_reason = ?
                WHERE id = ?";
        
        $stmt = $this->db->query($sql, [$reason, $id]);
        
        if ($stmt->rowCount() > 0) {
            // Log activity
            $activityRepo = new ReservationActivityRepository();
            $activityRepo->log(
                $id,
                $managerId,
                'rejected',
                $reservation['workflow_status'],
                'rejected',
                $reason
            );

            // Notify creator
            if ($reservation['created_by']) {
                $notificationRepo = new NotificationRepository();
                $notificationRepo->create(
                    $reservation['created_by'],
                    'reservation_rejected',
                    'Reservasi Ditolak',
                    "Reservasi untuk {$reservation['customer_name']} ditolak: {$reason}",
                    "?c=reservation&a=edit&id={$id}"
                );
            }

            return true;
        }

        return false;
    }

    public function requestCancel(int $id, int $userId, string $reason): bool
    {
        $reservation = $this->find($id);
        if (!$reservation) return false;

        $sql = "UPDATE reservations 
                SET workflow_status = 'pending_cancel',
                    cancellation_reason = ?
                WHERE id = ?";
        
        $stmt = $this->db->query($sql, [$reason, $id]);
        
        if ($stmt->rowCount() > 0) {
            // Log activity
            $activityRepo = new ReservationActivityRepository();
            $activityRepo->log(
                $id,
                $userId,
                'request_cancel',
                $reservation['workflow_status'],
                'pending_cancel',
                $reason
            );

            // Notify all managers
            $this->notifyManagers(
                'cancel_requested',
                'Permintaan Pembatalan',
                "Permintaan pembatalan reservasi untuk {$reservation['customer_name']}: {$reason}",
                "?c=reservation&a=edit&id={$id}"
            );

            return true;
        }

        return false;
    }

    public function approveCancel(int $id, int $managerId, ?string $notes = null): bool
    {
        $reservation = $this->find($id);
        if (!$reservation) return false;

        $sql = "UPDATE reservations 
                SET workflow_status = 'canceled',
                    status = 'dibatalkan',
                    approved_by = ?,
                    approved_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->db->query($sql, [$managerId, $id]);
        
        if ($stmt->rowCount() > 0) {
            // Log activity
            $activityRepo = new ReservationActivityRepository();
            $activityRepo->log(
                $id,
                $managerId,
                'cancel_approved',
                $reservation['workflow_status'],
                'canceled',
                $notes ?? 'Pembatalan disetujui'
            );

            // Notify creator
            if ($reservation['created_by']) {
                $notificationRepo = new NotificationRepository();
                $notificationRepo->create(
                    $reservation['created_by'],
                    'cancel_approved',
                    'Pembatalan Disetujui',
                    "Pembatalan reservasi untuk {$reservation['customer_name']} telah disetujui",
                    "?c=reservation&a=edit&id={$id}"
                );
            }

            return true;
        }

        return false;
    }

    private function notifyManagers(string $type, string $title, string $message, string $link)
    {
        $stmt = $this->db->query("SELECT id FROM users WHERE role IN ('manager', 'admin') AND is_active = 1");
        $managers = $stmt->fetchAll();

        $notificationRepo = new NotificationRepository();
        foreach ($managers as $manager) {
            $notificationRepo->create(
                $manager['id'],
                $type,
                $title,
                $message,
                $link
            );
        }
    }

    public function getWorkflowStats(): array
    {
        $stmt = $this->db->query(
            "SELECT workflow_status, COUNT(*) as count
             FROM reservations
             WHERE deleted_at IS NULL
             GROUP BY workflow_status"
        );
        
        $results = $stmt->fetchAll();
        $stats = [];
        
        foreach ($results as $row) {
            $stats[$row['workflow_status']] = (int) $row['count'];
        }
        
        return $stats;
    }

    // Existing methods remain the same
    public function softDelete($id)
    {
        $sql = "UPDATE reservations SET deleted_at=NOW() WHERE id=?";
        return $this->db->query($sql, [$id]);
    }

    public function restore($id)
    {
        $sql = "UPDATE reservations SET deleted_at=NULL WHERE id=?";
        return $this->db->query($sql, [$id]);
    }

    public function forceDelete($id)
    {
        $sql = "DELETE FROM reservations WHERE id=?";
        return $this->db->query($sql, [$id]);
    }

    public function restoreBulk($ids)
    {
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "UPDATE reservations SET deleted_at=NULL WHERE id IN ($in)";
        return $this->db->query($sql, $ids);
    }

    public function forceDeleteBulk($ids)
    {
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "DELETE FROM reservations WHERE id IN ($in)";
        return $this->db->query($sql, $ids);
    }

    public function autoDeleteOld($days = 30)
    {
        $sql = "DELETE FROM reservations WHERE deleted_at IS NOT NULL AND deleted_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->db->query($sql, [$days]);
        return $stmt->rowCount();
    }

    public function trashed()
    {
        $stmt = $this->db->query("SELECT r.*, c.name AS court_name 
                                  FROM reservations r 
                                  JOIN courts c ON r.court_id = c.id
                                  WHERE r.deleted_at IS NOT NULL
                                  ORDER BY deleted_at DESC");
        return $stmt->fetchAll();
    }
}