<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login/index.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$nombre    = trim($_POST['nombre']    ?? '');
$disciplina= trim($_POST['disciplina']?? '');
$bio       = trim($_POST['bio']       ?? '');
$municipio = trim($_POST['municipio'] ?? '');
$foto_url  = trim($_POST['foto_url']  ?? '');
$instagram = trim($_POST['instagram'] ?? '');
$website   = trim($_POST['website']   ?? '');

if (!$nombre || !$disciplina || !$bio) {
    $_SESSION['reg_artista_error'] = 'Nombre, disciplina y biografía son obligatorios.';
    header('Location: index.php'); exit;
}

try {
    // ¿Ya tiene perfil de artista?
    $check = db()->prepare("SELECT id FROM artistas WHERE usuario_id = ?::uuid");
    $check->execute([$_SESSION['user_id']]);
    if ($check->fetch()) {
        $_SESSION['reg_artista_error'] = 'Ya tienes un perfil de artista registrado.';
        header('Location: index.php'); exit;
    }

    db()->prepare(
        "INSERT INTO artistas (usuario_id, nombre, disciplina, bio, municipio, foto_url, instagram, website, verificado)
         VALUES (?::uuid, ?, ?, ?, ?, ?, ?, ?, FALSE)"
    )->execute([$_SESSION['user_id'], $nombre, $disciplina, $bio, $municipio ?: null,
                $foto_url ?: null, $instagram ?: null, $website ?: null]);

    // Cambiar rol a 'artista' en usuarios
    db()->prepare("UPDATE usuarios SET rol = 'artista' WHERE id = ?::uuid")
        ->execute([$_SESSION['user_id']]);

    $_SESSION['rol'] = 'artista';
    $_SESSION['reg_artista_ok'] = 'Perfil enviado correctamente. Pronto será verificado por nuestro equipo.';
    header('Location: index.php'); exit;

} catch (PDOException $e) {
    $_SESSION['reg_artista_error'] = 'Error al guardar: ' . $e->getMessage();
    header('Location: index.php'); exit;
}
