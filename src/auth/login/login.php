<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$_SESSION['login_email'] = $email;

if (!$email || !$password) {
    $_SESSION['login_error'] = 'Por favor, completa todos los campos.';
    header('Location: index.php'); exit;
}

try {
    $stmt = db()->prepare(
        "SELECT id, nombre, password, rol, activo FROM usuarios WHERE email = ? LIMIT 1"
    );
    $stmt->execute([$email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u) {
        $_SESSION['login_error'] = 'El correo electrónico no está registrado.';
    } elseif (!$u['activo']) {
        $_SESSION['login_error'] = 'Esta cuenta ha sido desactivada.';
    } elseif (!password_verify($password, $u['password'])) {
        $_SESSION['login_error'] = 'La contraseña es incorrecta.';
    } else {
        unset($_SESSION['login_error'], $_SESSION['login_email']);
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['nombre']  = $u['nombre'];
        $_SESSION['rol']     = $u['rol'];
        $_SESSION['artista_verificado'] = false;
        if ($u['rol'] === 'artista') {
            $av = db()->prepare(
                "SELECT verificado FROM artistas WHERE usuario_id = ?::uuid LIMIT 1"
            );
            $av->execute([$u['id']]);
            $row = $av->fetch(PDO::FETCH_ASSOC);
            $_SESSION['artista_verificado'] = ($row && $row['verificado'] == true);
        }

        try {
            $cc = db()->prepare(
                "SELECT COALESCE(SUM(cantidad),0) FROM carrito_items WHERE usuario_id = ?::uuid"
            );
            $cc->execute([$u['id']]);
            $_SESSION['carrito_count'] = (int)$cc->fetchColumn();
        } catch (Exception $e) {
            $_SESSION['carrito_count'] = 0;
        }

        header('Location: ../../inicio/inicio.php'); exit;
    }
} catch (PDOException $e) {
    $_SESSION['login_error'] = 'Error de conexión. Intenta más tarde.';
}

header('Location: index.php'); exit;
