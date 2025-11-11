<?php
// File: app/core/Middleware.php

class Middleware
{
    private $auth;

    public function __construct()
    {
        $this->auth = Auth::getInstance();
    }

    /**
     * Middleware untuk memastikan user sudah login
     */
    public function auth(): void
    {
        if (!$this->auth->check()) {
            $_SESSION['error'] = 'Silakan login terlebih dahulu.';
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '';
            
            $config = require __DIR__ . '/../../config/config.php';
            $baseUrl = rtrim($config['base_url'] ?? '/', '/');
            
            header("Location: {$baseUrl}?c=auth&a=login");
            exit;
        }
    }

    /**
     * Middleware untuk memastikan user adalah guest (belum login)
     */
    public function guest(): void
    {
        if ($this->auth->check()) {
            $config = require __DIR__ . '/../../config/config.php';
            $baseUrl = rtrim($config['base_url'] ?? '/', '/');
            
            header("Location: {$baseUrl}?c=dashboard&a=index");
            exit;
        }
    }

    /**
     * Middleware untuk role tertentu
     */
    public function role(string $role): void
    {
        $this->auth();
        
        if (!$this->auth->hasRole($role)) {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini.';
            
            $config = require __DIR__ . '/../../config/config.php';
            $baseUrl = rtrim($config['base_url'] ?? '/', '/');
            
            header("Location: {$baseUrl}?c=dashboard&a=index");
            exit;
        }
    }

    /**
     * Middleware untuk multiple roles (salah satu role saja cukup)
     */
    public function roles(array $roles): void
    {
        $this->auth();
        
        $hasAccess = false;
        foreach ($roles as $role) {
            if ($this->auth->hasRole($role)) {
                $hasAccess = true;
                break;
            }
        }
        
        if (!$hasAccess) {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini.';
            
            $config = require __DIR__ . '/../../config/config.php';
            $baseUrl = rtrim($config['base_url'] ?? '/', '/');
            
            header("Location: {$baseUrl}?c=dashboard&a=index");
            exit;
        }
    }

    /**
     * Middleware untuk permission tertentu
     */
    public function permission(string $permission): void
    {
        $this->auth();
        
        if (!$this->auth->hasPermission($permission)) {
            $_SESSION['error'] = 'Anda tidak memiliki izin untuk melakukan aksi ini.';
            
            $config = require __DIR__ . '/../../config/config.php';
            $baseUrl = rtrim($config['base_url'] ?? '/', '/');
            
            header("Location: {$baseUrl}?c=dashboard&a=index");
            exit;
        }
    }

    /**
     * Middleware untuk admin only
     */
    public function admin(): void
    {
        $this->role('admin');
    }

    /**
     * Middleware untuk manager only
     */
    public function manager(): void
    {
        $this->role('manager');
    }

    /**
     * Middleware untuk staff only
     */
    public function staff(): void
    {
        $this->role('staff');
    }

    /**
     * Rate limiting untuk prevent brute force
     */
    public function rateLimit(string $key, int $maxAttempts = 5, int $decayMinutes = 15): void
    {
        if (!isset($_SESSION['rate_limit'])) {
            $_SESSION['rate_limit'] = [];
        }

        $now = time();
        $attempts = $_SESSION['rate_limit'][$key] ?? [];
        
        // Remove old attempts
        $attempts = array_filter($attempts, function($timestamp) use ($now, $decayMinutes) {
            return ($now - $timestamp) < ($decayMinutes * 60);
        });

        if (count($attempts) >= $maxAttempts) {
            http_response_code(429);
            die('Too many requests. Please try again later.');
        }

        $attempts[] = $now;
        $_SESSION['rate_limit'][$key] = $attempts;
    }
}