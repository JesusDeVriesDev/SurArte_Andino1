<?php
require_once __DIR__ . '/../../config/db.php';
$user = requireAuth();

try {
    $pdo = db();
    $items = $pdo->prepare(
        "SELECT ci.producto_id, ci.cantidad, p.precio, p.nombre, p.stock
         FROM carrito_items ci
         JOIN productos p ON ci.producto_id = p.id
         WHERE ci.usuario_id = ?::uuid"
    );
    $items->execute([$user['id']]);
    $carritoItems = $items->fetchAll();
    if (empty($carritoItems)) jsonErr('El carrito está vacío', 400);
    foreach ($carritoItems as $item) {
        if ($item['stock'] < $item['cantidad']) {
            jsonErr("Stock insuficiente para \"{$item['nombre']}\" (disponible: {$item['stock']})", 400);
        }
    }
    $total = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $carritoItems));

    $pdo->beginTransaction();

    $pedidoStmt = $pdo->prepare(
        "INSERT INTO pedidos (usuario_id, total, estado) VALUES (?::uuid, ?, 'pagado') RETURNING id"
    );
    $pedidoStmt->execute([$user['id'], $total]);
    $pedidoId = $pedidoStmt->fetchColumn();

    $itemStmt  = $pdo->prepare(
        "INSERT INTO pedido_items (pedido_id, producto_id, nombre_snap, precio_snap, cantidad)
         VALUES (?::uuid, ?::uuid, ?, ?, ?)"
    );
    $stockStmt = $pdo->prepare(
        "UPDATE productos SET stock = stock - ? WHERE id = ?::uuid"
    );

    foreach ($carritoItems as $item) {
        $itemStmt->execute([$pedidoId, $item['producto_id'], $item['nombre'], $item['precio'], $item['cantidad']]);
        $stockStmt->execute([$item['cantidad'], $item['producto_id']]);
        $pdo->prepare(
            "UPDATE productos SET activo = FALSE WHERE id = ?::uuid AND stock <= 0"
        )->execute([$item['producto_id']]);
    }

    $pdo->prepare("DELETE FROM carrito_items WHERE usuario_id = ?::uuid")->execute([$user['id']]);

    $pdo->commit();

    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['carrito_count'] = 0;

    jsonOk(['pedido_id' => $pedidoId, 'total' => $total, 'items' => count($carritoItems)]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    jsonErr('Error al procesar el pago: ' . $e->getMessage());
}
