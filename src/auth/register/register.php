<?php
session_start();
require_once __DIR__ . '/../../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$nombre   = trim($_POST['nombre']    ?? '');
$email    = trim($_POST['email']     ?? '');
$password = trim($_POST['password']  ?? '');
$confirm  = trim($_POST['confirm']   ?? '');

$_SESSION['reg_nombre'] = $nombre;
$_SESSION['reg_email']  = $email;

if (!$nombre || !$email || !$password || !$confirm) {
    $_SESSION['reg_error'] = 'Por favor, completa todos los campos.';
    header('Location: index.php'); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['reg_error'] = 'El correo electrónico no es válido.';
    header('Location: index.php'); exit;
}
if (strlen($password) < 8) {
    $_SESSION['reg_error'] = 'La contraseña debe tener al menos 8 caracteres.';
    header('Location: index.php'); exit;
}
if ($password !== $confirm) {
    $_SESSION['reg_error'] = 'Las contraseñas no coinciden.';
    header('Location: index.php'); exit;
}

try {
    $check = db()->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $check->execute([$email]);
    if ($check->fetch()) {
        $_SESSION['reg_error'] = 'Este correo electrónico ya está registrado.';
        header('Location: index.php'); exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = db()->prepare(
        "INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES (?, ?, ?, 'visitante', TRUE)"
    );
    $stmt->execute([$nombre, $email, $hash]);

    // PostgreSQL usa lastInsertId con el nombre de la secuencia
    // pero mejor hacer SELECT para obtener el id recién insertado
    $newUser = db()->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $newUser->execute([$email]);
    $userId = $newUser->fetchColumn();

    unset($_SESSION['reg_error'], $_SESSION['reg_nombre'], $_SESSION['reg_email']);

    $_SESSION['user_id'] = $userId;
    $_SESSION['nombre']  = $nombre;
    $_SESSION['rol']     = 'visitante';

    header('Location: ../../inicio/inicio.php');
    exit;

} catch (PDOException $e) {
    // Mostrar el error real para diagnosticar
    $_SESSION['reg_error'] = 'Error de conexión. Intenta más tarde.';
    header('Location: index.php'); exit;
}