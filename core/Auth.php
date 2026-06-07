<?php
require_once __DIR__ . '/../config/config.php';

class Auth {
    public static function login(array $user): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'   => $user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
        ];
    }

    public static function logout(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
        exit;
    }

    public static function check(): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return isset($_SESSION['user']);
    }

    public static function currentUser(): ?array {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }

    public static function role(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return $_SESSION['user']['role'] ?? '';
    }

    public static function requireRole(string ...$roles): void {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/index.php?page=auth&action=login');
            exit;
        }
        if (!in_array(self::role(), $roles, true)) {
            header('Location: ' . BASE_URL . '/index.php?page=error&action=403');
            exit;
        }
    }
}
