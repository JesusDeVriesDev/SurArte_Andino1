<?php
// Gestiona las tres acciones sobre productos del artista: crear, editar y eliminar.
// Primero verifica que el usuario en sesión tenga un perfil de artista válido.
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$accion = $_POST['accion'] ?? '';

try {
    // Recupera el ID del artista vinculado al usuario en sesión.
    // Si no tiene perfil de artista, lo devuelve al listado sin más.
    $artStmt = db()->prepare("SELECT id FROM artistas WHERE usuario_id = ?::uuid");
    $artStmt->execute([$_SESSION['user_id']]);
    $artista = $artStmt->fetch();
    if (!$artista) { header('Location: index.php'); exit; }

    if ($accion === 'crear') {
        $nombre    = trim($_POST['nombre']      ?? '');
        $categoria = trim($_POST['categoria']   ?? '');
        $desc      = trim($_POST['descripcion'] ?? '');
        $precio    = (float)($_POST['precio']   ?? 0);
        $stock     = (int)($_POST['stock']      ?? 1);
        $img       = trim($_POST['imagen_url']  ?? '');

        if (!$nombre || !$categoria || $precio <= 0) {
            $_SESSION['prod_error'] = 'Nombre, categoría y precio son obligatorios.';
            header('Location: index.php'); exit;
        }
        db()->prepare(
            "INSERT INTO productos (artista_id, nombre, descripcion, categoria, precio, stock, imagen_url)
             VALUES (?::uuid, ?, ?, ?, ?, ?, ?)"
        )->execute([$artista['id'], $nombre, $desc ?: null, $categoria, $precio, $stock, $img ?: null]);
        $_SESSION['prod_ok'] = 'Producto publicado correctamente.';

    } elseif ($accion === 'editar') {
        $id     = $_POST['producto_id'] ?? '';
        $nombre = trim($_POST['nombre']      ?? '');
        $cat    = trim($_POST['categoria']   ?? '');
        $desc   = trim($_POST['descripcion'] ?? '');
        $precio = (float)($_POST['precio']   ?? 0);
        $stock  = (int)($_POST['stock']      ?? 0);
        $img    = trim($_POST['imagen_url']  ?? '');
        // El checkbox "activo" solo viene en POST si está marcado
        $activo = isset($_POST['activo']) ? 'TRUE' : 'FALSE';

        // El AND artista_id=? garantiza que solo el artista dueño puede editar el producto
        db()->prepare(
            "UPDATE productos SET nombre=?, descripcion=?, categoria=?, precio=?, stock=?, imagen_url=?, activo=$activo
             WHERE id=?::uuid AND artista_id=?::uuid"
        )->execute([$nombre, $desc ?: null, $cat, $precio, $stock, $img ?: null, $id, $artista['id']]);
        $_SESSION['prod_ok'] = 'Producto actualizado.';

    } elseif ($accion === 'eliminar') {
        $id = $_POST['producto_id'] ?? '';
        // La condición artista_id=? evita que un artista borre productos de otro
        db()->prepare("DELETE FROM productos WHERE id=?::uuid AND artista_id=?::uuid")
            ->execute([$id, $artista['id']]);
        $_SESSION['prod_ok'] = 'Producto eliminado.';
    }

} catch (PDOException $e) {
    $_SESSION['prod_error'] = 'Error: ' . $e->getMessage();
}

header('Location: index.php'); exit;
