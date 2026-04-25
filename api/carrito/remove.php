<?php
// Elimina un producto específico del carrito del usuario en sesión
require_once __DIR__ . '/../../config/db.php';
$user   = requireAuth();
$body   = getBody();
$prodId = $body['producto_id'] ?? '';

if (!$prodId) jsonErr('producto_id requerido');

try {
    // Elimina la fila que combina el usuario y el producto —
    // si no existe, la consulta simplemente no afecta ninguna fila (sin error)
    db()->prepare(
        "DELETE FROM carrito_items WHERE usuario_id = ?::uuid AND producto_id = ?::uuid"
    )->execute([$user['id'], $prodId]);

    // Recalcula los totales del carrito para que el JS pueda actualizar
    // el badge y el resumen sin necesidad de recargar la página
    $totStmt = db()->prepare(
        "SELECT COUNT(*) AS items, COALESCE(SUM(p.precio * ci.cantidad),0) AS total
         FROM carrito_items ci JOIN productos p ON ci.producto_id = p.id
         WHERE ci.usuario_id = ?::uuid"
    );
    $totStmt->execute([$user['id']]);
    $tot = $totStmt->fetch();

    // Mantiene el contador de sesión sincronizado con la BD
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['carrito_count'] = (int)$tot['items'];

    jsonOk(['total_items' => (int)$tot['items'], 'total_precio' => (float)$tot['total']]);

} catch (PDOException $e) {
    jsonErr($e->getMessage());
}
