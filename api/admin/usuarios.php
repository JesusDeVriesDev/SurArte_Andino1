<?php
require_once __DIR__ . '/../../config/db.php';
$user = requireRole('admin');
$body = getBody();
$accion = $body['accion'] ?? '';

switch ($accion) {
    case 'cambiarRol':
        $id  = $body['id']  ?? '';
        $rol = $body['rol'] ?? '';
        if (!$id || !in_array($rol, ['visitante','artista','organizador','admin']))
            jsonErr('Datos inválidos');
        db()->prepare("UPDATE usuarios SET rol = ? WHERE id = ?::uuid")->execute([$rol, $id]);
        jsonOk(['id' => $id, 'rol' => $rol]);

    case 'eliminar':
        $id = $body['id'] ?? '';
        if (!$id) jsonErr('ID inválido');
        if ($id === $_SESSION['user_id']) jsonErr('No puedes eliminar tu propia cuenta', 403);
        db()->prepare("DELETE FROM usuarios WHERE id = ?::uuid")->execute([$id]);
        jsonOk(['id' => $id]);

    default:
        jsonErr('Acción no reconocida');
}
