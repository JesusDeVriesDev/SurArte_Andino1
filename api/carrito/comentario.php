<?php
require_once __DIR__ . '/../../config/db.php';

$user   = requireAuth();
$body   = getBody();
$accion = $body['accion'] ?? '';

switch ($accion) {

    case 'crear':
        $prodId = $body['producto_id'] ?? '';
        $texto  = trim($body['texto'] ?? '');
        if (!$prodId || !$texto) jsonErr('Datos incompletos');
        if (mb_strlen($texto) > 1000) jsonErr('Comentario demasiado largo');

        $stmt = db()->prepare(
            "INSERT INTO producto_comentarios (producto_id, usuario_id, texto)
             VALUES (?::uuid, ?::uuid, ?)
             RETURNING id, creado_en"
        );
        $stmt->execute([$prodId, $user['id'], $texto]);
        $row = $stmt->fetch();

        // Devolver el comentario con nombre y rol del usuario
        $uStmt = db()->prepare("SELECT nombre, rol FROM usuarios WHERE id = ?::uuid");
        $uStmt->execute([$user['id']]);
        $u = $uStmt->fetch();

        jsonOk([
            'id'         => $row['id'],
            'texto'      => htmlspecialchars($texto),
            'nombre'     => htmlspecialchars($u['nombre']),
            'rol'        => $u['rol'],
            'usuario_id' => $user['id'],
            'creado_en'  => $row['creado_en'],
        ]);

    case 'editar':
        $id    = $body['id'] ?? '';
        $texto = trim($body['texto'] ?? '');
        if (!$id || !$texto) jsonErr('Datos incompletos');
        if (mb_strlen($texto) > 1000) jsonErr('Comentario demasiado largo');

        // Verificar que es el dueño
        $check = db()->prepare("SELECT usuario_id FROM producto_comentarios WHERE id = ?::uuid");
        $check->execute([$id]);
        $c = $check->fetch();
        if (!$c || $c['usuario_id'] !== $user['id']) jsonErr('Sin permiso', 403);

        db()->prepare(
            "UPDATE producto_comentarios SET texto = ?, editado_en = NOW() WHERE id = ?::uuid"
        )->execute([$texto, $id]);

        jsonOk(['id' => $id, 'texto' => htmlspecialchars($texto)]);

    case 'eliminar':
        $id = $body['id'] ?? '';
        if (!$id) jsonErr('ID requerido');

        $check = db()->prepare("SELECT usuario_id FROM producto_comentarios WHERE id = ?::uuid");
        $check->execute([$id]);
        $c = $check->fetch();
        if (!$c || $c['usuario_id'] !== $user['id']) jsonErr('Sin permiso', 403);

        db()->prepare("DELETE FROM producto_comentarios WHERE id = ?::uuid")->execute([$id]);
        jsonOk(['id' => $id]);

    default:
        jsonErr('Acción no reconocida');
}