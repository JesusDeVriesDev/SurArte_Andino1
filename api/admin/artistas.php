<?php
require_once __DIR__ . '/../../config/db.php';
$user = requireRole('admin');
$body = getBody();
$accion = $body['accion'] ?? '';

switch ($accion) {
    case 'verificar':
        $id = $body['id'] ?? '';
        if (!$id) jsonErr('ID inválido');
        db()->prepare("UPDATE artistas SET verificado = TRUE WHERE id = ?::uuid")
            ->execute([$id]);
        db()->prepare(
            "UPDATE usuarios SET rol = 'artista'
             WHERE id = (SELECT usuario_id FROM artistas WHERE id = ?::uuid)
               AND rol NOT IN ('admin','organizador')"
        )->execute([$id]);
        jsonOk(['id' => $id, 'verificado' => true]);

    case 'eliminar':
    $id = $body['id'] ?? '';
    if (!$id) jsonErr('ID inválido');

    // 🔍 Verificar si el artista tiene productos en pedidos
    $stmt = db()->prepare("
        SELECT COUNT(*) 
        FROM pedido_items pi
        JOIN productos p ON pi.producto_id = p.id
        WHERE p.artista_id = ?::uuid
    ");
    $stmt->execute([$id]);
    $tienePedidos = $stmt->fetchColumn();

    if ($tienePedidos > 0) {
        jsonErr('No puedes eliminar este artista porque tiene productos en pedidos');
    }

    // 🧹 Eliminar productos del artista
    db()->prepare("DELETE FROM productos WHERE artista_id = ?::uuid")
        ->execute([$id]);

    // 🔄 Cambiar rol del usuario
    db()->prepare(
        "UPDATE usuarios SET rol = 'visitante'
         WHERE id = (SELECT usuario_id FROM artistas WHERE id = ?::uuid)
           AND rol NOT IN ('admin','organizador')"
    )->execute([$id]);

    // 🗑️ Eliminar artista
    db()->prepare("DELETE FROM artistas WHERE id = ?::uuid")
        ->execute([$id]);

    jsonOk(['id' => $id]);
    default:
        jsonErr('Acción no reconocida');
}