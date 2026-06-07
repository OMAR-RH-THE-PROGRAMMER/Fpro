<?php
require_once __DIR__ . '/../config/config.php';

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatDate(string $date): string {
    return date('d M Y', strtotime($date));
}

function formatTime(string $time): string {
    return date('h:i A', strtotime($time));
}

function setFlash(string $type, string $message): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function generateTimeSlots(): array {
    $slots = [];
    for ($h = 9; $h <= 16; $h++) {
        $slots[] = sprintf('%02d:00', $h);
        if ($h < 16) {
            $slots[] = sprintf('%02d:30', $h);
        }
    }
    return $slots;
}

function statusBadge(string $status): string {
    $map = [
        'pending'   => 'warning',
        'confirmed' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];
    $color = $map[$status] ?? 'secondary';
    return '<span class="badge badge-' . $color . '">' . ucfirst(e($status)) . '</span>';
}

function url(string $page, string $action = '', array $extra = []): string {
    $params = ['page' => $page];
    if ($action !== '') $params['action'] = $action;
    $params = array_merge($params, $extra);
    return BASE_URL . '/index.php?' . http_build_query($params);
}
