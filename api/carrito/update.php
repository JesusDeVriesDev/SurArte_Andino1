<?php
require_once __DIR__ . '/../../config/db.php';
$user = requireAuth();
$body = getBody();
$prodId   = $body['producto_id'] ?? '';
$cantidad = max(1, (int)($body['cantidad'] ?? 1));
if (!$prodId) jsonErr('producto_id requerido');
try {
    db()->prepare(
        "UPDATE carrito_items SET cantidad = ? WHERE usuario_id = ?::uuid AND producto_id = ?::uuid"
    )->execute([$cantidad, $user['id'], $prodId]);
    $totStmt = db()->prepare(
        "SELECT COUNT(*) AS items, COALESCE(SUM(p.precio * ci.cantidad),0) AS total
         FROM carrito_items ci JOIN productos p ON ci.producto_id = p.id
         WHERE ci.usuario_id = ?::uuid"
    );
    $totStmt->execute([$user['id']]);
    $tot = $totStmt->fetch();
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['carrito_count'] = (int)$tot['items'];
    jsonOk(['total_items' => (int)$tot['items'], 'total_precio' => (float)$tot['total']]);
} catch (PDOException $e) { jsonErr($e->getMessage()); }
