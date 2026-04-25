<?php
// Credenciales de conexión leídas desde variables de entorno en producción (Render).
// Los valores por defecto son los de desarrollo/staging con Supabase.
// Nunca hardcodear credenciales reales en producción — usar las env vars de Render.
define('DB_HOST', getenv('DB_HOST') ?: 'aws-1-us-east-1.pooler.supabase.com');
define('DB_PORT', (int)(getenv('DB_PORT') ?: '6543'));
define('DB_NAME', getenv('DB_NAME') ?: 'postgres');
define('DB_USER', getenv('DB_USER') ?: 'postgres.vyyqmssvfcicxwguvyeb');
define('DB_PASS', getenv('DB_PASS') ?: '8jACXQsawav9YsNR');

// Conexión singleton a PostgreSQL vía PDO.
// La primera llamada abre la conexión; las siguientes reutilizan la misma instancia.
// Antes de conectar, hace una prueba de socket rápida (5 segundos) para dar
// un mensaje de error claro si la BD no es alcanzable.
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    // Prueba de conectividad TCP antes del DSN — si esto falla, el error de PDO
    // sería críptico; con esto el mensaje indica exactamente qué revisar
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
        // Prepares reales de PostgreSQL — más seguro y más eficiente en consultas repetidas
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

// Responde con JSON de éxito y termina la ejecución.
// $data puede ser cualquier valor serializable: array, string, int, etc.
function jsonOk(mixed $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

// Responde con JSON de error y termina la ejecución.
// El código por defecto es 400 (bad request); usar 401/403/404/500 cuando corresponda.
function jsonErr(string $msg, int $code = 400): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lee y decodifica el cuerpo JSON de la petición entrante.
// Si el body está vacío o no es JSON válido, devuelve un array vacío en lugar de null.
function getBody(): array {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

// Verifica que haya una sesión activa con usuario autenticado.
// Devuelve el ID y rol del usuario, o responde 401 si no hay sesión.
function requireAuth(): array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        jsonErr('No autenticado', 401);
    }
    return ['id' => $_SESSION['user_id'], 'rol' => $_SESSION['rol'] ?? 'visitante'];
}

// Verifica autenticación y además comprueba que el usuario tenga uno de los roles indicados.
// Acepta múltiples roles: requireRole('admin', 'organizador') permite ambos.
// Responde 403 si el rol no coincide.
function requireRole(string ...$roles): array {
    $user = requireAuth();
    if (!in_array($user['rol'], $roles)) jsonErr('Acceso denegado', 403);
    return $user;
}
