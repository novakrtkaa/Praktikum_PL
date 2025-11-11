<?php

class Csrf
{
    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function generate(): string
    {
        return self::token();
    }

    public static function verifyOrFail(?string $token = null): bool
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($token === null) {
            $token = $_POST['csrf_token'] ?? null;
        }

        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            die('403 Forbidden - CSRF token invalid or missing.');
        }
        
        return true;
    }
}