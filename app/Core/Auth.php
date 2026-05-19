<?php
// E:\call_center\app\Core\Auth.php

namespace App\Core;

class Auth
{
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        Session::set('user_id',    $user['id']);
        Session::set('user_name',  $user['name']);
        Session::set('user_role',  $user['role']);
        Session::set('user_email', $user['email']);
        // Cache secondary roles at login for sidebar rendering
        Session::set('user_roles', []); // will be lazily loaded on first hasRole call
    }

    public static function logout(): void
    {
        Session::destroy();
        session_start();
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function name(): string
    {
        return Session::get('user_name', '');
    }

    public static function role(): string
    {
        return Session::get('user_role', '');
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function isCaller(): bool
    {
        return self::role() === 'caller';
    }

    public static function isDeveloper(): bool
    {
        return self::role() === 'developer' || self::hasRole('developer');
    }

    public static function isPartner(): bool
    {
        return self::role() === 'partner' || self::hasRole('partner');
    }

    /**
     * Check if the logged-in user has a given role (checks both primary role and user_roles pivot).
     */
    public static function hasRole(string $role): bool
    {
        if (self::role() === $role) return true;
        if (self::role() === 'admin') return true; // admin has all roles

        // Check pivot table via cached roles in session
        $cached = Session::get('user_roles_loaded', false);
        if (!$cached && self::check()) {
            try {
                $db    = \Database::getInstance();
                $stmt  = $db->prepare("SELECT role FROM user_roles WHERE user_id = ?");
                $stmt->execute([self::id()]);
                $roles = array_column($stmt->fetchAll(), 'role');
                Session::set('user_extra_roles', $roles);
                Session::set('user_roles_loaded', true);
            } catch (\Exception $e) {
                Session::set('user_extra_roles', []);
                Session::set('user_roles_loaded', true);
            }
        }

        $extraRoles = Session::get('user_extra_roles', []);
        return in_array($role, $extraRoles, true);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: ' . APP_URL . '/caller/dashboard');
            exit;
        }
    }

    public static function requireCaller(): void
    {
        self::requireLogin();
        if (!self::isCaller() && !self::isAdmin()) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
    }

    public static function requireDeveloper(): void
    {
        self::requireLogin();
        if (!self::isDeveloper() && !self::isAdmin()) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
    }

    public static function requirePartner(): void
    {
        self::requireLogin();
        if (!self::isPartner() && !self::isAdmin()) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
    }

    public static function dashboardUrl(): string
    {
        return match(self::role()) {
            'admin'     => APP_URL . '/admin/dashboard',
            'developer' => APP_URL . '/developer/dashboard',
            'partner'   => APP_URL . '/partner/dashboard',
            default     => APP_URL . '/caller/dashboard',
        };
    }
}
