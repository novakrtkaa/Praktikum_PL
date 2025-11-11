<?php
// File: app/models/User.php

class User
{
    private $id;
    private $username;
    private $email;
    private $password;
    private $role;
    private $fullName;
    private $isActive;
    private $createdAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->username = $data['username'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->role = $data['role'] ?? 'staff';
        $this->fullName = $data['full_name'] ?? '';
        $this->isActive = $data['is_active'] ?? 1;
        $this->createdAt = $data['created_at'] ?? null;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function isActive(): bool
    {
        return (bool) $this->isActive;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    // Business Logic Methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = [
            'admin' => [
                'manage_users', 'manage_courts', 'manage_reservations',
                'view_all_data', 'delete_permanent', 'system_settings'
            ],
            'manager' => [
                'manage_courts', 'manage_reservations', 'view_all_data',
                'approve_cancellation'
            ],
            'staff' => [
                'create_reservation', 'view_reservations', 'edit_own_reservation'
            ]
        ];

        return in_array($permission, $permissions[$this->role] ?? []);
    }

    public function canAccessDashboard(string $dashboard): bool
    {
        $access = [
            'admin' => ['admin', 'manager', 'staff'],
            'manager' => ['manager', 'staff'],
            'staff' => ['staff']
        ];

        return in_array($dashboard, $access[$this->role] ?? []);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'full_name' => $this->fullName,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt
        ];
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}