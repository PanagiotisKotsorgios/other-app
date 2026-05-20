<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Detect HTTPS — ngrok/reverse-proxy sends X-Forwarded-Proto: https
            $isHttps = (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
                    || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => $isHttps,   // Secure cookie over HTTPS
                'httponly' => true,
                'samesite' => 'Lax',      // Lax: allows links from external sites; Strict broke ngrok navigation
            ]);
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function destroy(): void
    {
        session_destroy();
        $_SESSION = [];
    }
}
