<?php
// File: app/models/ReservationActivity.php

class ReservationActivity
{
    private $id;
    private $reservationId;
    private $userId;
    private $action;
    private $oldStatus;
    private $newStatus;
    private $notes;
    private $createdAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->reservationId = $data['reservation_id'] ?? null;
        $this->userId = $data['user_id'] ?? null;
        $this->action = $data['action'] ?? '';
        $this->oldStatus = $data['old_status'] ?? null;
        $this->newStatus = $data['new_status'] ?? null;
        $this->notes = $data['notes'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
    }

    public function getId(): ?int { return $this->id; }
    public function getReservationId(): ?int { return $this->reservationId; }
    public function getUserId(): ?int { return $this->userId; }
    public function getAction(): string { return $this->action; }
    public function getOldStatus(): ?string { return $this->oldStatus; }
    public function getNewStatus(): ?string { return $this->newStatus; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
}