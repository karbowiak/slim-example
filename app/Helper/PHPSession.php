<?php

namespace App\Helper;

class PHPSession
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function get(string $key): ?string
    {
        return $this->has($key) ? $_SESSION[$key] : null;
    }

    public function set(string $key, string $value): bool
    {
        try {
            $_SESSION[$key] = $value;
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
}