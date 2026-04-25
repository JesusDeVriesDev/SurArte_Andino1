<?php
// El proceso de checkout es una operación crítica que requiere usuario autenticado.
// requireAuth() devuelve el usuario o responde 401 antes de llegar a la lógica de pago.
require_once __DIR__ . '/../../config/db.php';
$user = requireAuth();

try {
    $pdo = db();

    // Carga todos los ítems del carrito junto con el precio y stock actual del producto
    $items = $pdo->prepare(
        "SELECT ci.producto_id, ci.cantidad, p.precio, p.nombre, p.stock
         FROM carrito_items ci
         JOIN productos p ON ci.producto_id = p.id
         WHERE ci.usuario_id = ?::uuid"
    );
    $items->execute([$user['id']]);
    $carritoItems = $items->fetchAll();

    if (empty($carritoItems)) jsonErr('El carrito está vacío', 400);

    // Valida que todos los productos tengan stock suficiente antes de iniciar
    // la transacción — así el error es visible sin haber modificado nada aún
    foreach ($carritoItems as $item) {
        if ($item['stock'] < $item['cantidad']) {
            jsonErr("Stock insuficiente para \"{$item['nombre']}\" (disponible: {$item['stock']})", 400);
        }
    }

    $total = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $carritoItems));

    // La transacción agrupa: crear pedido, insertar ítems, descontar stock y vaciar el carrito.
    // Si cualquier paso falla, el rollback deshace todo sin dejar datos inconsistentes.
    $pdo->beginTransaction();

    // Crea el pedido principal — RETURNING id devuelve el UUID generado por PostgreSQL
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
        // nombre_snap y precio_snap guardan el nombre y precio del producto al momento
        // de la compra, por si el artista los cambia después — el historial queda intacto
        $itemStmt->execute([$pedidoId, $item['producto_id'], $item['nombre'], $item['precio'], $item['cantidad']]);
        $stockStmt->execute([$item['cantidad'], $item['producto_id']]);
        // Si el stock llegó a 0, desactiva el producto para que no aparezca en tienda
        $pdo->prepare(
            "UPDATE productos SET activo = FALSE WHERE id = ?::uuid AND stock <= 0"
        )->execute([$item['producto_id']]);
    }

    // Vacía el carrito del usuario — el pedido ya quedó registrado
    $pdo->prepare("DELETE FROM carrito_items WHERE usuario_id = ?::uuid")->execute([$user['id']]);
    $pdo->commit();

    // Resetea el contador de carrito en la sesión para que el badge del nav
    // muestre 0 correctamente si el usuario recarga la página
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['carrito_count'] = 0;

    jsonOk(['pedido_id' => $pedidoId, 'total' => $total, 'items' => count($carritoItems)]);

} catch (PDOException $e) {
    // El rollback solo se ejecuta si la transacción está abierta —
    // puede no estarlo si el error ocurrió antes del beginTransaction()
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    jsonErr('Error al procesar el pago: ' . $e->getMessage());
}
