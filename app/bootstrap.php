<?php
declare(strict_types=1);

const ROOT = __DIR__ . '/..';
date_default_timezone_set('Asia/Bangkok');

function config(): array
{
    static $values;
    if (is_array($values)) return $values;
    $values = require __DIR__ . '/config/example.php';
    if (is_file(__DIR__ . '/config/local.php')) $values = array_replace($values, require __DIR__ . '/config/local.php');
    foreach (['db_host','db_port','db_name','db_user','db_pass','base_path','delivery_fee','table_prefix'] as $key) {
        $env = getenv('CLOZEE_' . strtoupper($key));
        if ($env !== false) $values[$key] = $env;
    }
    $secure = getenv('CLOZEE_SECURE_COOKIES');
    if ($secure !== false) $values['secure_cookies'] = $secure === '1';
    $values['base_path'] = rtrim((string)$values['base_path'], '/');
    return $values;
}

require __DIR__ . '/config/tables.php';
require __DIR__ . '/config/db.php';
require __DIR__ . '/validation.php';
require __DIR__ . '/auth/session.php';
require __DIR__ . '/services/ShopService.php';
require __DIR__ . '/services/ProductService.php';
require __DIR__ . '/services/OrderService.php';
require __DIR__ . '/services/ChatService.php';

if (PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
    header('Cache-Control: no-store');
    ini_set('display_errors', '0');
    set_exception_handler(function (Throwable $e): void {
        error_log('CLOZEE: ' . get_class($e) . ': ' . $e->getMessage());
        http_response_code(500);
        $title = 'ระบบยังไม่พร้อมใช้งาน';
        require __DIR__ . '/templates/header.php';
        echo '<section class="empty"><h1>ขออภัย ระบบยังไม่พร้อม</h1><p>โปรดลองอีกครั้ง หากเพิ่งติดตั้ง ให้ตรวจการเชื่อมต่อฐานข้อมูลตาม README</p></section>';
        require __DIR__ . '/templates/footer.php';
    });
    start_session();
}

function url(string $path = ''): string { return config()['base_path'] . '/' . ltrim($path, '/'); }
function h(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . url($path), true, 303); exit; }
function flash(string $message): void { $_SESSION['flash'] = $message; }
function fail(int $code, string $message): never {
    http_response_code($code); $title = 'ไม่สามารถดำเนินการได้';
    require __DIR__ . '/templates/header.php';
    echo '<section class="empty"><h1>' . h($message) . '</h1><a class="button" href="' . h(url()) . '">กลับหน้าร้าน</a></section>';
    require __DIR__ . '/templates/footer.php'; exit;
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">'; }
function post_guard(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(405, 'กรุณาส่งแบบฟอร์มเพื่อดำเนินการ');
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) fail(403, 'แบบฟอร์มหมดอายุ กรุณาโหลดหน้าใหม่');
}
