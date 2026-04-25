<?php
// Verificación manual de sesión y rol — este endpoint usa el patrón antiguo
// (sin requireRole()) para mantener compatibilidad con cómo fue originalmente escrito.
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

// Solo admins pueden activar, desactivar o eliminar eventos
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = $body['accion'] ?? '';
$id     = $body['id']     ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID requerido.']);
    exit;
}

try {
    if ($accion === 'desactivar' || $accion === 'activar') {
        // Un mismo endpoint maneja activar y desactivar para reducir rutas en la API.
        // El valor booleano se inyecta como literal SQL para no tener problemas de binding
        // con valores TRUE/FALSE en algunos drivers de PostgreSQL.
        $activo = $accion === 'activar' ? 'TRUE' : 'FALSE';
        $stmt   = db()->prepare("UPDATE eventos SET activo = $activo WHERE id = ?::uuid");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'activo' => ($accion === 'activar')]);

    } elseif ($accion === 'eliminar') {
        // Elimina el evento permanentemente — sin soft delete, por lo que esta
        // acción es irreversible desde el panel
        db()->prepare("DELETE FROM eventos WHERE id = ?::uuid")->execute([$id]);
        echo json_encode(['success' => true]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
