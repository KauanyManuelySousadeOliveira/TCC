<?php
declare(strict_types=1);
// Carrega .env (se existir), helpers, autoload das camadas MVC e inicia a sessão.
$env = __DIR__ . '/../.env';
if (is_file($env)) foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
    if ($l[0] === '#' || !str_contains($l, '=')) continue;
    [$k, $v] = explode('=', $l, 2); if (getenv(trim($k)) === false) putenv(trim($k) . '=' . trim($v, " \t\"'"));
}
date_default_timezone_set(getenv('APP_TZ') ?: 'America/Sao_Paulo');
require __DIR__ . '/helpers.php';
spl_autoload_register(function (string $c) {
    foreach (['Core', 'Models', 'Controllers'] as $d) { $f = __DIR__ . "/$d/$c.php"; if (is_file($f)) { require $f; return; } }
    if (in_array($c, ['Notifier'], true)) require_once __DIR__ . '/Core/Log.php';
});
if (PHP_SAPI !== 'cli') {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')]);
    session_start();
}
