<?php
// Solo usuarios autenticados pueden agregar al carrito.
// requireAuth() devuelve los datos del usuario en sesión o responde 401.
require_once __DIR__ . '/../../config/db.php';
$user     = requireAuth();
$body     = getBody();
$prodId   = $body['producto_id'] ?? '';
$cantidad = max(1, (int)($body['cantidad'] ?? 1));

if (!$prodId) jsonErr('producto_id requerido');

try {
    // Verifica que el producto exista, esté activo y tenga stock disponible
    // antes de intentar insertarlo en el carrito
    $p = db()->prepare("SELECT stock FROM productos WHERE id = ?::uuid AND activo = TRUE");
    $p->execute([$prodId]);
    $prod = $p->fetch();
    if (!$prod)            jsonErr('Producto no encontrado', 404);
    if ($prod['stock'] < 1) jsonErr('Producto agotado', 400);

    // INSERT con ON CONFLICT para que si el producto ya está en el carrito del usuario,
    // simplemente sume la cantidad nueva a la existente en lugar de duplicar la fila.
    db()->prepare(
        "INSERT INTO carrito_items (usuario_id, producto_id, cantidad)
         VALUES (?::uuid, ?::uuid, ?)
         ON CONFLICT (usuario_id, producto_id)
         DO UPDATE SET cantidad = carrito_items.cantidad + EXCLUDED.cantidad"
    )->execute([$user['id'], $prodId, $cantidad]);

    // Recalcula los totales del carrito completo para devolvérselos al JS,
    // que los usa para actualizar el badge y el resumen sin recargar la página.
    $totStmt = db()->prepare(
        "SELECT COUNT(*) AS items, COALESCE(SUM(p.precio * ci.cantidad),0) AS total
         FROM carrito_items ci JOIN productos p ON ci.producto_id = p.id
         WHERE ci.usuario_id = ?::uuid"
    );
    $totStmt->execute([$user['id']]);
    $tot = $totStmt->fetch();

    // Sincroniza el contador del badge del nav en la sesión de PHP
    // para que sea coherente con el valor que mostraría un reload de página.
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['carrito_count'] = (int)$tot['items'];

    jsonOk(['total_items' => (int)$tot['items'], 'total_precio' => (float)$tot['total']]);

} catch (PDOException $e) {
    jsonErr($e->getMessage());
}
