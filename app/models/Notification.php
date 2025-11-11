<?php
// File: app/models/Notification.php

class Notification
{
    private $id;
    private $userId;
    private $type;
    private $title;
    private $message;
    private $link;
    private $isRead;
    private $createdAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->userId = $data['user_id'] ?? null;
        $this->type = $data['type'] ?? '';
        $this->title = $data['title'] ?? '';
        $this->message = $data['message'] ?? '';
        $this->link = $data['link'] ?? null;
        $this->isRead = $data['is_read'] ?? 0;
        $this->createdAt = $data['created_at'] ?? null;
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): ?int { return $this->userId; }
    public function getType(): string { return $this->type; }
    public function getTitle(): string { return $this->title; }
    public function getMessage(): string { return $this->message; }
    public function getLink(): ?string { return $this->link; }
    public function isRead(): bool { return (bool) $this->isRead; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
}