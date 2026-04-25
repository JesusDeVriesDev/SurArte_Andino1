<?php
// Guarda los cambios del perfil del artista desde su panel privado.
// Solo acepta POST y requiere sesión activa.
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$nombre   = trim($_POST['nombre']   ?? '');
$bio      = trim($_POST['bio']      ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['confirm']  ?? '');

if (!$nombre || strlen($nombre) < 2) {
    $_SESSION['perfil_err'] = 'El nombre debe tener al menos 2 caracteres.';
    header('Location: index.php'); exit;
}

// Si la contraseña viene en blanco, no se cambia — es un campo opcional
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
    db()->prepare("UPDATE usuarios SET nombre=?, bio=?, telefono=?, updated_en=NOW() WHERE id=?::uuid")
        ->execute([$nombre, $bio ?: null, $telefono ?: null, $_SESSION['user_id']]);

    // Sincroniza el nombre en sesión para que el nav lo muestre actualizado sin logout
    $_SESSION['nombre'] = $nombre;

    if ($password !== '') {
        db()->prepare("UPDATE usuarios SET password=?, updated_en=NOW() WHERE id=?::uuid")
            ->execute([password_hash($password, PASSWORD_BCRYPT), $_SESSION['user_id']]);
    }

    $_SESSION['perfil_ok'] = 'Datos actualizados correctamente.';

} catch (PDOException $e) {
    $_SESSION['perfil_err'] = 'Error: ' . $e->getMessage();
}

header('Location: index.php'); exit;
