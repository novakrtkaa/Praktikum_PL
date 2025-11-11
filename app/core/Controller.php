<?php

class Controller
{
    protected $conn;
    protected array $config;
    protected array $data = [];

    public function __construct($conn = null, array $config = [])
    {
        $this->conn = $conn;
        
        if (empty($config)) {
            $this->config = require __DIR__ . '/../../config/config.php';
        } else {
            $this->config = $config;
        }
    }

    protected function view(string $viewPath, array $vars = []): void
    {
        extract($vars, EXTR_SKIP);
        
        $base_url = $this->config['base_url'] ?? '/';
        
        $viewFile = __DIR__ . "/../views/{$viewPath}.php";
        
        if (!file_exists($viewFile)) {
            die("View file not found: {$viewFile}");
        }
        
        include $viewFile;
    }

    protected function redirect(string $path): void
    {
        $base = rtrim($this->config['base_url'] ?? '/', '/');
        
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            header("Location: {$path}");
            exit;
        }
        
        if (!str_contains($path, '?') && !str_contains($path, '=')) {
            $parts = explode('/', $path);
            $controller = $parts[0];
            $action = $parts[1] ?? 'index';
            $id = $parts[2] ?? null;
            
            $url = $base . "?c={$controller}&a={$action}";
            if ($id) $url .= "&id={$id}";
            
            header("Location: {$url}");
        } else {
            $path = ltrim($path, '/');
            header("Location: {$base}/{$path}");
        }
        
        exit;
    }

    protected function flash(string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash'] = $message;
    }
}