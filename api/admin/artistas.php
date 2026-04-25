<?php
// Solo el admin puede ejecutar acciones sobre perfiles de artistas.
// requireRole() revisa la sesión y responde con 403 si el rol no coincide.
require_once __DIR__ . '/../../config/db.php';
$user   = requireRole('admin');
$body   = getBody();
$accion = $body['accion'] ?? '';

switch ($accion) {

    case 'verificar':
        // Marca al artista como verificado y asigna el rol "artista" al usuario dueño
        // del perfil, siempre que no tenga ya un rol de mayor jerarquía.
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

        // Antes de eliminar se verifica si el artista tiene productos vinculados
        // a pedidos existentes. Si los tiene, se bloquea la operación para
        // preservar la integridad del historial de compras de los clientes.
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

        // Se eliminan primero los productos del artista para no dejar huérfanos en la BD
        db()->prepare("DELETE FROM productos WHERE artista_id = ?::uuid")
            ->execute([$id]);

        // El usuario vuelve a "visitante" para que pueda seguir usando la plataforma
        // pero sin acceso a las funciones de artista
        db()->prepare(
            "UPDATE usuarios SET rol = 'visitante'
             WHERE id = (SELECT usuario_id FROM artistas WHERE id = ?::uuid)
               AND rol NOT IN ('admin','organizador')"
        )->execute([$id]);

        // Por último se elimina el perfil del artista de la tabla principal
        db()->prepare("DELETE FROM artistas WHERE id = ?::uuid")
            ->execute([$id]);

        jsonOk(['id' => $id]);

    default:
        jsonErr('Acción no reconocida');
}
