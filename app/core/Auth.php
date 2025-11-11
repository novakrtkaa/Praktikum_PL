<?php
// File: app/core/Auth.php

class Auth
{
    private static $instance = null;
    private $userRepo;
    private $currentUser = null;

    private function __construct()
    {
        $this->userRepo = new UserRepository();
        $this->initSession();
        $this->loadCurrentUser();
    }

    // Singleton Pattern: Hanya ada 1 instance Auth
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Security settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', 0); // Set 1 jika menggunakan HTTPS
            ini_set('session.gc_maxlifetime', 3600); // 1 jam
            
            session_start();
        }
    }

    private function loadCurrentUser(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->currentUser = $this->userRepo->findById($_SESSION['user_id']);
            
            // Jika user tidak ditemukan atau tidak aktif, logout
            if (!$this->currentUser) {
                $this->logout();
            }
        }
    }

    public function attempt(string $username, string $password): bool
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Check rate limiting (max 5 failed attempts dalam 15 menit)
        $failedAttempts = $this->userRepo->getRecentFailedAttempts($username, 15);
        
        if ($failedAttempts >= 5) {
            $_SESSION['error'] = 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.';
            return false;
        }

        // Find user
        $user = $this->userRepo->findByUsername($username);
        
        if (!$user) {
            $this->userRepo->recordLoginAttempt($username, $ipAddress, false);
            $_SESSION['error'] = 'Username atau password salah.';
            return false;
        }

        // Verify password
        if (!$user->verifyPassword($password)) {
            $this->userRepo->recordLoginAttempt($username, $ipAddress, false);
            $_SESSION['error'] = 'Username atau password salah.';
            return false;
        }

        // Check if user is active
        if (!$user->isActive()) {
            $_SESSION['error'] = 'Akun Anda tidak aktif. Hubungi administrator.';
            return false;
        }

        // Login success
        $this->login($user);
        $this->userRepo->recordLoginAttempt($username, $ipAddress, true);
        $this->userRepo->clearLoginAttempts($username);
        
        return true;
    }

    private function login(User $user): void
    {
        // Regenerate session ID untuk mencegah session fixation
        session_regenerate_id(true);
        
        // Store user data in session
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['role'] = $user->getRole();
        $_SESSION['full_name'] = $user->getFullName();
        $_SESSION['login_time'] = time();
        
        $this->currentUser = $user;
    }

    public function logout(): void
    {
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        $this->currentUser = null;
    }

    public function check(): bool
    {
        return $this->currentUser !== null;
    }

    public function user(): ?User
    {
        return $this->currentUser;
    }

    public function id(): ?int
    {
        return $this->currentUser ? $this->currentUser->getId() : null;
    }

    public function username(): ?string
    {
        return $this->currentUser ? $this->currentUser->getUsername() : null;
    }

    public function role(): ?string
    {
        return $this->currentUser ? $this->currentUser->getRole() : null;
    }

    public function isAdmin(): bool
    {
        return $this->check() && $this->currentUser->isAdmin();
    }

    public function isManager(): bool
    {
        return $this->check() && $this->currentUser->isManager();
    }

    public function isStaff(): bool
    {
        return $this->check() && $this->currentUser->isStaff();
    }

    public function hasRole(string $role): bool
    {
        return $this->check() && $this->currentUser->hasRole($role);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->check() && $this->currentUser->hasPermission($permission);
    }

    public function requireAuth(): void
    {
        if (!$this->check()) {
            $_SESSION['error'] = 'Silakan login terlebih dahulu.';
            header('Location: ' . $this->getBaseUrl() . '?c=auth&a=login');
            exit;
        }
    }

    public function requireRole(string $role): void
    {
        $this->requireAuth();
        
        if (!$this->hasRole($role)) {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini.';
            header('Location: ' . $this->getBaseUrl() . '?c=dashboard&a=index');
            exit;
        }
    }

    public function requirePermission(string $permission): void
    {
        $this->requireAuth();
        
        if (!$this->hasPermission($permission)) {
            $_SESSION['error'] = 'Anda tidak memiliki izin untuk melakukan aksi ini.';
            header('Location: ' . $this->getBaseUrl() . '?c=dashboard&a=index');
            exit;
        }
    }

    private function getBaseUrl(): string
    {
        $config = require __DIR__ . '/../../config/config.php';
        return rtrim($config['base_url'] ?? '/', '/');
    }

    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}