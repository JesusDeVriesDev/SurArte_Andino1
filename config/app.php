<?php
define('APP_NAME',    $_ENV['APP_NAME']    ?? 'SurArte Andino');
define('APP_ENV',     $_ENV['APP_ENV']     ?? 'production');
define('APP_URL',     $_ENV['APP_URL']     ?? 'https://localhost');
define('APP_DEBUG',   $_ENV['APP_DEBUG']   ?? false);
define('APP_VERSION', $_ENV['APP_VERSION'] ?? '1.0.0');

date_default_timezone_set('America/Bogota');

if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

spl_autoload_register(function($class) {
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) require_once $file;
});
