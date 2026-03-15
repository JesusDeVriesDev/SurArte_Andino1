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
        db()->prepare("DELETE FROM artistas WHERE id = ?::uuid")->execute([$id]);
        jsonOk(['id' => $id]);

    default:
        jsonErr('Acción no reconocida');
}
