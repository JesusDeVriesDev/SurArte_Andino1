<?php
// Variables de configuración global de la aplicación.
// En producción (Render) estos valores vienen de las env vars del dashboard.
// En desarrollo local se usan los valores por defecto definidos aquí.
define('APP_NAME',    $_ENV['APP_NAME']    ?? 'SurArte Andino');
define('APP_ENV',     $_ENV['APP_ENV']     ?? 'production');
define('APP_URL',     $_ENV['APP_URL']     ?? 'https://localhost');
define('APP_DEBUG',   $_ENV['APP_DEBUG']   ?? false);
define('APP_VERSION', $_ENV['APP_VERSION'] ?? '1.0.0');

// Zona horaria de Colombia — afecta todas las fechas generadas con date() y NOW() de PHP
date_default_timezone_set('America/Bogota');

// En desarrollo se muestran todos los errores para facilitar la depuración.
// En producción se ocultan para no exponer información sensible al usuario.
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Cabeceras de seguridad básicas enviadas en cada respuesta.
// Reducen la superficie de ataque frente a XSS, clickjacking y sniffing de MIME.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Autoloader PSR-4 simplificado: convierte el nombre de la clase en una ruta de archivo
// dentro de /src. Permite hacer new NombreClase() sin require_once manual.
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) require_once $file;
});
