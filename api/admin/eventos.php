<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

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
        $activo = $accion === 'activar' ? 'TRUE' : 'FALSE';
        $stmt   = db()->prepare("UPDATE eventos SET activo = $activo WHERE id = ?::uuid");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'activo' => ($accion === 'activar')]);

    } elseif ($accion === 'eliminar') {
        db()->prepare("DELETE FROM eventos WHERE id = ?::uuid")->execute([$id]);
        echo json_encode(['success' => true]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
