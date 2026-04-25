<?php
// Guarda los cambios del perfil de un usuario regular (no artista).
// Solo acepta POST y requiere sesión activa.
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$nombre   = trim($_POST['nombre']   ?? '');
$telefono = trim($_POST['telefono'] ?? '');
if ($telefono && !preg_match('/^(\+57\s?)?[0-9]{10}$/', str_replace(' ', '', $telefono))) {
    $_SESSION['perfil_err'] = 'Número inválido. Usa (10 dígitos o +57).';
    header('Location: index.php?tab=cuenta');  
    exit;
}
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['confirm']  ?? '');

if (!$nombre || strlen($nombre) < 2) {
    $_SESSION['perfil_err'] = 'El nombre debe tener al menos 2 caracteres.';
    header('Location: index.php?tab=cuenta'); exit;
}

// La contraseña no es obligatoria al editar el perfil — solo si el usuario quiere cambiarla
if ($password !== '') {
    if (strlen($password) < 8) {
        $_SESSION['perfil_err'] = 'La contraseña debe tener al menos 8 caracteres.';
        header('Location: index.php?tab=cuenta'); exit;
    }
    if ($password !== $confirm) {
        $_SESSION['perfil_err'] = 'Las contraseñas no coinciden.';
        header('Location: index.php?tab=cuenta'); exit;
    }
}

try {
    db()->prepare("UPDATE usuarios SET nombre=?, telefono=? WHERE id=?::uuid")
        ->execute([$nombre, $telefono ?: null, $_SESSION['user_id']]);

    // Refleja el nuevo nombre en la sesión para que el nav lo muestre de inmediato
    if ($nombre) $_SESSION['nombre'] = $nombre;

    if ($password !== '') {
        db()->prepare("UPDATE usuarios SET password=? WHERE id=?::uuid")
            ->execute([password_hash($password, PASSWORD_BCRYPT), $_SESSION['user_id']]);
    }

    $_SESSION['perfil_ok'] = 'Datos actualizados correctamente.';

} catch (PDOException $e) {
    $_SESSION['perfil_err'] = 'Error: ' . $e->getMessage();
}

header('Location: index.php?tab=cuenta'); exit;
