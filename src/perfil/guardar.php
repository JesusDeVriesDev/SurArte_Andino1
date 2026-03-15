<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}
$nombre   = trim($_POST['nombre']   ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['confirm']  ?? '');

if (!$nombre || strlen($nombre) < 2) {
    $_SESSION['perfil_err'] = 'El nombre debe tener al menos 2 caracteres.';
    header('Location: index.php'); exit;
}
if ($password !== '') {
    if (strlen($password) < 8) {
        $_SESSION['perfil_err'] = 'La contraseña debe tener al menos 8 caracteres.';
        header('Location: index.php'); exit;
    }
    if ($password !== $confirm) {
        $_SESSION['perfil_err'] = 'Las contraseñas no coinciden.';
        header('Location: index.php'); exit;
    }
}

try {
    db()->prepare("UPDATE usuarios SET nombre=?, telefono=? WHERE id=?::uuid")
        ->execute([$nombre, $telefono ?: null, $_SESSION['user_id']]);
    if ($nombre) $_SESSION['nombre'] = $nombre;
    if ($password !== '') {
        db()->prepare("UPDATE usuarios SET password=? WHERE id=?::uuid")
            ->execute([password_hash($password, PASSWORD_BCRYPT), $_SESSION['user_id']]);
    }
    $_SESSION['perfil_ok'] = 'Datos actualizados correctamente.';
} catch (PDOException $e) {
    $_SESSION['perfil_err'] = 'Error: ' . $e->getMessage();
}
header('Location: index.php'); exit;