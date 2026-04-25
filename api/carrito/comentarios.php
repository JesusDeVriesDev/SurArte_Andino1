<?php
// Limpia cualquier output buffer previo para que la respuesta JSON llegue limpia
// sin bytes extra que rompan el parse en el cliente
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/db.php';

try {
    // Solo usuarios autenticados pueden crear, editar o eliminar comentarios
    $user   = requireAuth();
    $body   = getBody();
    $accion = $body['accion'] ?? '';

    switch ($accion) {

        case 'crear':
            $prodId = $body['producto_id'] ?? '';
            $texto  = trim($body['texto'] ?? '');
            if (!$prodId || !$texto) jsonErr('Datos incompletos');
            // Limita la longitud para evitar spam o contenido excesivo en la BD
            if (mb_strlen($texto) > 1000) jsonErr('Comentario demasiado largo');

            $stmt = db()->prepare(
                "INSERT INTO producto_comentarios (producto_id, usuario_id, texto)
                 VALUES (?::uuid, ?::uuid, ?)
                 RETURNING id, creado_en"
            );
            $stmt->execute([$prodId, $user['id'], $texto]);
            $row = $stmt->fetch();

            // Devuelve también el nombre y rol del usuario para que el JS pueda
            // mostrar el nuevo comentario en el DOM sin necesidad de recargar la lista
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
            break;

        case 'editar':
            $id    = $body['id'] ?? '';
            $texto = trim($body['texto'] ?? '');
            if (!$id || !$texto) jsonErr('Datos incompletos');
            if (mb_strlen($texto) > 1000) jsonErr('Comentario demasiado largo');

            // Verifica que el comentario le pertenece al usuario que hace la petición.
            // Un usuario no puede editar comentarios de otra persona.
            $check = db()->prepare("SELECT usuario_id FROM producto_comentarios WHERE id = ?::uuid");
            $check->execute([$id]);
            $c = $check->fetch();
            if (!$c || $c['usuario_id'] !== $user['id']) jsonErr('Sin permiso', 403);

            db()->prepare(
                "UPDATE producto_comentarios SET texto = ?, editado_en = NOW() WHERE id = ?::uuid"
            )->execute([$texto, $id]);

            jsonOk(['id' => $id, 'texto' => htmlspecialchars($texto)]);
            break;

        case 'eliminar':
            $id = $body['id'] ?? '';
            if (!$id) jsonErr('ID requerido');

            // Misma verificación de propiedad que en "editar" —
            // el usuario solo puede borrar sus propios comentarios
            $check = db()->prepare("SELECT usuario_id FROM producto_comentarios WHERE id = ?::uuid");
            $check->execute([$id]);
            $c = $check->fetch();
            if (!$c || $c['usuario_id'] !== $user['id']) jsonErr('Sin permiso', 403);

            db()->prepare("DELETE FROM producto_comentarios WHERE id = ?::uuid")->execute([$id]);
            jsonOk(['id' => $id]);
            break;

        default:
            jsonErr('Acción no reconocida');
    }

} catch (PDOException $e) {
    jsonErr('DB: ' . $e->getMessage());
} catch (Throwable $e) {
    jsonErr($e->getMessage());
}
