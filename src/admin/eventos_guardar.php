<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

$id          = trim($_POST['id']          ?? '');
$titulo      = trim($_POST['titulo']      ?? '');
$categoria   = trim($_POST['categoria']   ?? '');
$lugar       = trim($_POST['lugar']       ?? '');
$municipio   = trim($_POST['municipio']   ?? '');
$fecha_inicio= trim($_POST['fecha_inicio']?? '');
$fecha_fin   = trim($_POST['fecha_fin']   ?? '');
$precio      = trim($_POST['precio']      ?? '0');
$aforo       = trim($_POST['aforo']       ?? '');
$imagen_url  = trim($_POST['imagen_url']  ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

$categorias_validas = ['musica','arte','artesania','danza','literatura','otro'];

if (!$titulo) {
    $_SESSION['evento_error'] = 'El título es obligatorio.';
    header('Location: eventos.php'); exit;
}
if (!$categoria || !in_array($categoria, $categorias_validas)) {
    $_SESSION['evento_error'] = 'Selecciona una categoría válida.';
    header('Location: eventos.php'); exit;
}
if (!$fecha_inicio) {
    $_SESSION['evento_error'] = 'La fecha de inicio es obligatoria.';
    header('Location: eventos.php'); exit;
}
if ($fecha_fin && $fecha_fin <= $fecha_inicio) {
    $_SESSION['evento_error'] = 'La fecha de fin debe ser posterior a la de inicio.';
    header('Location: eventos.php'); exit;
}

$precio = is_numeric($precio) && $precio >= 0 ? (float)$precio : 0;
$aforo  = is_numeric($aforo) && $aforo > 0 ? (int)$aforo : null;

try {
    if ($id) {
        $check = db()->prepare("SELECT id FROM eventos WHERE id = ?::uuid LIMIT 1");
        $check->execute([$id]);
        if (!$check->fetch()) {
            $_SESSION['evento_error'] = 'Evento no encontrado.';
            header('Location: eventos.php'); exit;
        }
        db()->prepare(
            "UPDATE eventos SET titulo=?, categoria=?, lugar=?, municipio=?, fecha_inicio=?, fecha_fin=?,
             precio=?, aforo=?, imagen_url=?, descripcion=? WHERE id=?::uuid"
        )->execute([
            $titulo, $categoria, $lugar ?: null, $municipio ?: null,
            $fecha_inicio, $fecha_fin ?: null,
            $precio, $aforo, $imagen_url ?: null, $descripcion ?: null,
            $id
        ]);
        $_SESSION['evento_ok'] = 'Evento actualizado correctamente.';
    } else {
        db()->prepare(
            "INSERT INTO eventos (organizador_id, titulo, categoria, lugar, municipio,
             fecha_inicio, fecha_fin, precio, aforo, imagen_url, descripcion, activo)
             VALUES (?::uuid, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE)"
        )->execute([
            $_SESSION['user_id'],
            $titulo, $categoria, $lugar ?: null, $municipio ?: null,
            $fecha_inicio, $fecha_fin ?: null,
            $precio, $aforo, $imagen_url ?: null, $descripcion ?: null
        ]);
        $_SESSION['evento_ok'] = 'Evento creado correctamente.';
    }
} catch (PDOException $e) {
    $_SESSION['evento_error'] = 'Error al guardar: ' . $e->getMessage();
}

header('Location: eventos.php');
exit;
