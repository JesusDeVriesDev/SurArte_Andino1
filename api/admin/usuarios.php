<?php
// Solo administradores pueden gestionar roles y eliminar usuarios.
// requireRole() bloquea con 403 si el usuario en sesión no es admin.
require_once __DIR__ . '/../../config/db.php';
$user   = requireRole('admin');
$body   = getBody();
$accion = $body['accion'] ?? '';

switch ($accion) {

    case 'cambiarRol':
        // Solo permite roles válidos del sistema para evitar inyectar valores arbitrarios.
        // El whitelist de roles es la única validación necesaria aquí.
        $id  = $body['id']  ?? '';
        $rol = $body['rol'] ?? '';
        if (!$id || !in_array($rol, ['visitante','artista','organizador','admin']))
            jsonErr('Datos inválidos');
        db()->prepare("UPDATE usuarios SET rol = ? WHERE id = ?::uuid")->execute([$rol, $id]);
        jsonOk(['id' => $id, 'rol' => $rol]);

    case 'eliminar':
        $id = $body['id'] ?? '';
        if (!$id) jsonErr('ID inválido');
        // Un admin no puede borrarse a sí mismo desde el panel —
        // evita que el sistema quede sin administradores por accidente.
        if ($id === $_SESSION['user_id']) jsonErr('No puedes eliminar tu propia cuenta', 403);
        db()->prepare("DELETE FROM usuarios WHERE id = ?::uuid")->execute([$id]);
        jsonOk(['id' => $id]);

    default:
        jsonErr('Acción no reconocida');
}
