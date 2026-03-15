<?php

define('DB_HOST',   getenv('DB_HOST')   ?: 'aws-1-us-east-1.pooler.supabase.com');
define('DB_PORT',   (int)(getenv('DB_PORT') ?: '6543'));
define('DB_NAME',   getenv('DB_NAME')   ?: 'postgres');
define('DB_USER',   getenv('DB_USER')   ?: 'postgres.vyyqmssvfcicxwguvyeb');
define('DB_PASS',   getenv('DB_PASS')   ?: '8jACXQsawav9YsNR');

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $sock = @stream_socket_client(
        'tcp://' . DB_HOST . ':' . DB_PORT,
        $errno, $errstr,
        5,
        STREAM_CLIENT_CONNECT
    );
    if ($sock === false) {
        throw new PDOException(
            "No se pudo alcanzar la base de datos ({$errno}: {$errstr}). " .
            "Verifica las variables de entorno DB_HOST/DB_PORT en Render."
        );
    }
    fclose($sock);

    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;connect_timeout=5',
        DB_HOST, DB_PORT, DB_NAME
    );
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

function jsonOk(mixed $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonErr(string $msg, int $code = 400): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function getBody(): array {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

function requireAuth(): array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        jsonErr('No autenticado', 401);
    }
    return ['id' => $_SESSION['user_id'], 'rol' => $_SESSION['rol'] ?? 'visitante'];
}

function requireRole(string ...$roles): array {
    $user = requireAuth();
    if (!in_array($user['rol'], $roles)) jsonErr('Acceso denegado', 403);
    return $user;
}