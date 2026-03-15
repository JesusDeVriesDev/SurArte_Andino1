<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$tipo = $_POST['tipo'] ?? '';

try {
    if ($tipo === 'perfil') {
        $nombre    = trim($_POST['nombre']     ?? '');
        $disciplina= trim($_POST['disciplina'] ?? '');
        $bio       = trim($_POST['bio']        ?? '');
        $municipio = trim($_POST['municipio']  ?? '');
        $foto_url  = trim($_POST['foto_url']   ?? '');
        $instagram = trim($_POST['instagram']  ?? '');
        $website   = trim($_POST['website']    ?? '');

        if (!$nombre || !$disciplina) {
            $_SESSION['editar_error'] = 'Nombre y disciplina son obligatorios.';
            header('Location: index.php'); exit;
        }
        db()->prepare(
            "UPDATE artistas SET nombre=?, disciplina=?, bio=?, municipio=?, foto_url=?, instagram=?, website=?
             WHERE usuario_id=?::uuid"
        )->execute([$nombre, $disciplina, $bio ?: null, $municipio ?: null,
                    $foto_url ?: null, $instagram ?: null, $website ?: null, $_SESSION['user_id']]);

    } elseif ($tipo === 'cuenta') {
        $nombre   = trim($_POST['nombre_usuario'] ?? '');
        $bio      = trim($_POST['bio_usuario']    ?? '');
        $telefono = trim($_POST['telefono']       ?? '');
        $password = trim($_POST['password']       ?? '');
        $confirm  = trim($_POST['confirm']        ?? '');

        if (!$nombre || strlen($nombre) < 2) {
            $_SESSION['editar_error'] = 'El nombre debe tener al menos 2 caracteres.';
            header('Location: index.php'); exit;
        }
        if ($password !== '') {
            if (strlen($password) < 8) {
                $_SESSION['editar_error'] = 'La contraseña debe tener al menos 8 caracteres.';
                header('Location: index.php'); exit;
            }
            if ($password !== $confirm) {
                $_SESSION['editar_error'] = 'Las contraseñas no coinciden.';
                header('Location: index.php'); exit;
            }
        }

        db()->prepare("UPDATE usuarios SET nombre=?, bio=?, telefono=?, updated_en=NOW() WHERE id=?::uuid")
            ->execute([$nombre, $bio ?: null, $telefono ?: null, $_SESSION['user_id']]);
        $_SESSION['nombre'] = $nombre;

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            db()->prepare("UPDATE usuarios SET password=?, updated_en=NOW() WHERE id=?::uuid")
                ->execute([$hash, $_SESSION['user_id']]);
        }
    }
    $_SESSION['editar_ok'] = 'Cambios guardados correctamente.';
} catch (PDOException $e) {
    $_SESSION['editar_error'] = 'Error al guardar: ' . $e->getMessage();
}
header('Location: index.php'); exit;