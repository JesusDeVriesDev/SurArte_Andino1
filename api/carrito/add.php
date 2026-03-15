<?php
require_once __DIR__ . '/../../config/db.php';
$user = requireAuth();
$body = getBody();
$prodId   = $body['producto_id'] ?? '';
$cantidad = max(1, (int)($body['cantidad'] ?? 1));
if (!$prodId) jsonErr('producto_id requerido');

try {
    $p = db()->prepare("SELECT stock FROM productos WHERE id = ?::uuid AND activo = TRUE");
    $p->execute([$prodId]);
    $prod = $p->fetch();
    if (!$prod) jsonErr('Producto no encontrado', 404);
    if ($prod['stock'] < 1) jsonErr('Producto agotado', 400);
    db()->prepare(
        "INSERT INTO carrito_items (usuario_id, producto_id, cantidad)
         VALUES (?::uuid, ?::uuid, ?)
         ON CONFLICT (usuario_id, producto_id)
         DO UPDATE SET cantidad = carrito_items.cantidad + EXCLUDED.cantidad"
    )->execute([$user['id'], $prodId, $cantidad]);
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
