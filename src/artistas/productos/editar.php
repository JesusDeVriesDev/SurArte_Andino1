<?php
ob_start();
$pageTitle = 'Editar Producto';
$pageId    = 'artistas';
require_once '../../_layout/head.php';
require_once '../../../config/db.php';

// Solo artistas verificados pueden editar sus productos
if (!$user || $user['rol'] !== 'artista') {
    header('Location: ' . $base . '/src/artistas/artistas.php'); exit;
}

// Verificación de estado (puede que el admin haya verificado al artista entre sesiones)
$_guardVerificado = $_SESSION['artista_verificado'] ?? false;
if (!$_guardVerificado) {
    try {
        $gStmt = db()->prepare("SELECT verificado FROM artistas WHERE usuario_id = ?::uuid LIMIT 1");
        $gStmt->execute([$_SESSION['user_id']]);
        $gRow = $gStmt->fetch(PDO::FETCH_ASSOC);
        $_guardVerificado = ($gRow && $gRow['verificado'] == true);
        if ($_guardVerificado) $_SESSION['artista_verificado'] = true;
    } catch (Exception $e) { $_guardVerificado = false; }
}
if (!$_guardVerificado) {
    $_SESSION['_flash_warn'] = 'Tu perfil aún no ha sido verificado.';
    header('Location: ' . $base . '/src/artistas/artistas.php'); exit;
}

// Requiere el ID del producto como parámetro GET — sin él no hay nada que editar
$productoId = $_GET['id'] ?? null;
if (!$productoId) { header('Location: index.php'); exit; }

try {
    // Obtiene el artista_id del usuario en sesión para validar propiedad del producto
    $artStmt = db()->prepare("SELECT id FROM artistas WHERE usuario_id = ?::uuid");
    $artStmt->execute([$_SESSION['user_id']]);
    $artista = $artStmt->fetch();

    // El AND artista_id=? impide que un artista edite productos de otro aunque conozca el UUID
    $stmt = db()->prepare("SELECT * FROM productos WHERE id = ?::uuid AND artista_id = ?::uuid");
    $stmt->execute([$productoId, $artista['id']]);
    $prod = $stmt->fetch();
    if (!$prod) { header('Location: index.php'); exit; }
} catch (PDOException $e) { $dbError = $e->getMessage(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/artistas/artistas.css"/>
  <style>
    .field-label{font-size:.82rem!important;font-weight:700!important;color:#1A1208!important;opacity:1!important}
    .field-input,.field-select,.field-textarea{font-size:1.05rem!important;font-weight:400!important;color:#0d0902!important;background:#FFFEF9!important;border:1.5px solid #EDE4D0!important}
    .field-input::placeholder,.field-textarea::placeholder{color:rgba(26,18,8,.55)!important}
    .field-input:focus,.field-select:focus,.field-textarea:focus{border-color:var(--gold)!important;box-shadow:0 0 0 3px rgba(201,146,42,.1)!important;outline:none!important}
  </style>
</head>
<main>
  <div style="max-width:600px;margin:0 auto;padding-top:48px">
    <a href="index.php" style="font-family:var(--ff-m);font-size:.78rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#3d2b10;text-decoration:none">← Mis productos</a>
    <h1 class="page-h1" style="margin:16px 0 8px;color:#0d0902">Editar <em>producto</em></h1>
    <?php if (isset($prod)): ?>
    <div class="form-card" style="max-width:100%">
      <form method="POST" action="guardar.php">
        <input type="hidden" name="accion" value="editar"/>
        <input type="hidden" name="producto_id" value="<?= $prod['id'] ?>"/>
        <div class="form-grid-2">
          <div class="field">
            <label class="field-label">Nombre *</label>
            <input class="field-input" type="text" name="nombre" required value="<?= htmlspecialchars($prod['nombre']) ?>"/>
          </div>
          <div class="field">
            <label class="field-label">Categoría *</label>
            <select class="field-select" name="categoria" required>
              <?php foreach (['artesania','arte','musica','literatura','danza','otro'] as $c): ?>
                <option value="<?= $c ?>" <?= $prod['categoria'] === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="field">
          <label class="field-label">Descripción</label>
          <textarea class="field-textarea" name="descripcion" rows="3"><?= htmlspecialchars($prod['descripcion'] ?? '') ?></textarea>
        </div>
        <div class="form-grid-2">
          <div class="field">
            <label class="field-label">Precio (COP) *</label>
            <input class="field-input" type="number" name="precio" required min="0" step="100" value="<?= $prod['precio'] ?>"/>
          </div>
          <div class="field">
            <label class="field-label">Stock</label>
            <input class="field-input" type="number" name="stock" min="0" value="<?= $prod['stock'] ?>"/>
          </div>
        </div>
        <div class="field">
          <label class="field-label">Imagen (URL)</label>
          <input class="field-input" type="url" name="imagen_url" value="<?= htmlspecialchars($prod['imagen_url'] ?? '') ?>"/>
        </div>
        <div class="field" style="flex-direction:row;align-items:center;gap:10px">
          <input type="checkbox" name="activo" id="activo" <?= $prod['activo'] ? 'checked' : '' ?> style="width:16px;height:16px"/>
          <label for="activo" class="field-label" style="margin:0">Producto visible en la tienda</label>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-gold">Guardar cambios</button>
          <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
      </form>
      <form method="POST" action="guardar.php" style="margin-top:20px;border-top:1px solid var(--cream-dk);padding-top:20px" onsubmit="return confirm('¿Eliminar este producto?')">
        <input type="hidden" name="accion" value="eliminar"/>
        <input type="hidden" name="producto_id" value="<?= $prod['id'] ?>"/>
        <button type="submit" class="btn btn-danger" style="font-size:.82rem">Eliminar producto</button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
