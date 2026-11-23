<?php
declare(strict_types=1);
namespace Fitmeal\Core;
final class View {
    public static function render(string $view, array $data = []): void {
        extract($data, EXTR_SKIP);
        $file = dirname(__DIR__) . '/Views/' . $view . '.php';
        if (!is_file($file)) { http_response_code(500); echo 'View unavailable'; return; }
        require dirname(__DIR__) . '/Views/layouts/header.php';
        require $file;
        require dirname(__DIR__) . '/Views/layouts/footer.php';
    }
}
