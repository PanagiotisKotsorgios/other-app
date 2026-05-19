<?php

namespace App\Core;

class CSRF
{
    private const TOKEN_KEY = '_csrf_token';

    public static function generate(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        }
        return Session::get(self::TOKEN_KEY);
    }

    public static function validate(string $token): bool
    {
        $stored = Session::get(self::TOKEN_KEY, '');
        return hash_equals($stored, $token);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(self::generate()) . '">';
    }

    public static function check(): void
    {
        $token = $_POST['_csrf_token'] ?? '';
        if (!self::validate($token)) {
            http_response_code(403);
            die('Invalid CSRF token. Please go back and try again.');
        }
    }
}
