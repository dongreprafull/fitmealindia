<?php
declare(strict_types=1);
namespace Fitmeal\Core;
final class Security {
    public static function csrfToken(): string { if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32)); return $_SESSION['_csrf']; }
    public static function verifyCsrf(): void {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['_csrf'] ?? '', $token)) { http_response_code(419); exit('Session expired. Please go back and try again.'); }
    }
    public static function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    public static function redirect(string $to): never { header('Location: ' . $to, true, 302); exit; }
    public static function requireAuth(): array { if (empty($_SESSION['user'])) self::redirect('/login'); return $_SESSION['user']; }
    public static function requireRole(array $roles): array { $user = self::requireAuth(); if (!in_array($user['role'] ?? '', $roles, true)) { http_response_code(403); exit('Forbidden'); } return $user; }
}
