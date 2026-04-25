<?php
// Inicia la sesión para poder escribir errores y datos del nuevo usuario
session_start();

// Carga el helper de base de datos compartido por todo el proyecto
require_once __DIR__ . '/../../../config/db.php';

// Solo acepta peticiones POST; si alguien navega directamente a este script, lo manda al formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Recoge y limpia los campos del formulario
$nombre   = trim($_POST['nombre']    ?? '');
$email    = trim($_POST['email']     ?? '');
$password = trim($_POST['password']  ?? '');
$confirm  = trim($_POST['confirm']   ?? '');

// Conserva nombre y correo en sesión para repoblar el formulario si hay un error de validación
$_SESSION['reg_nombre'] = $nombre;
$_SESSION['reg_email']  = $email;

// Todos los campos son obligatorios — validación temprana antes de tocar la BD
if (!$nombre || !$email || !$password || !$confirm) {
    $_SESSION['reg_error'] = 'Por favor, completa todos los campos.';
    header('Location: index.php'); exit;
}

// Verifica que el correo tenga un formato válido antes de intentar insertarlo
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['reg_error'] = 'El correo electrónico no es válido.';
    header('Location: index.php'); exit;
}

// La contraseña debe tener mínimo 8 caracteres — se valida aquí además del lado del cliente
if (strlen($password) < 8) {
    $_SESSION['reg_error'] = 'La contraseña debe tener al menos 8 caracteres.';
    header('Location: index.php'); exit;
}

// Comprueba que ambas contraseñas coincidan antes de hashear
if ($password !== $confirm) {
    $_SESSION['reg_error'] = 'Las contraseñas no coinciden.';
    header('Location: index.php'); exit;
}

try {
    // Verifica si el correo ya está registrado para evitar duplicados en la tabla usuarios
    $check = db()->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $check->execute([$email]);
    if ($check->fetch()) {
        $_SESSION['reg_error'] = 'Este correo electrónico ya está registrado.';
        header('Location: index.php'); exit;
    }

    // Hashea la contraseña con bcrypt antes de guardarla — nunca se almacena en texto plano
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // Inserta el nuevo usuario con el rol por defecto 'visitante' y cuenta activa desde el inicio
    $stmt = db()->prepare(
        "INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES (?, ?, ?, 'visitante', TRUE)"
    );
    $stmt->execute([$nombre, $email, $hash]);

    // Obtiene el ID del usuario recién creado para iniciar sesión automáticamente
    // Se hace con SELECT porque PostgreSQL no garantiza lastInsertId sin el nombre de la secuencia
    $newUser = db()->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $newUser->execute([$email]);
    $userId = $newUser->fetchColumn();

    // Limpia los datos temporales del proceso de registro de la sesión
    unset($_SESSION['reg_error'], $_SESSION['reg_nombre'], $_SESSION['reg_email']);

    // Inicia sesión automáticamente tras el registro — el usuario no tiene que loguearse aparte
    $_SESSION['user_id'] = $userId;
    $_SESSION['nombre']  = $nombre;
    $_SESSION['rol']     = 'visitante';

    // Redirige directo al inicio sin pasar por el login
    header('Location: ../../inicio/inicio.php');
    exit;

} catch (PDOException $e) {
    // Error de BD — se muestra un mensaje genérico; el detalle real queda en los logs del servidor
    $_SESSION['reg_error'] = 'Error de conexión. Intenta más tarde.';
    header('Location: index.php'); exit;
}
