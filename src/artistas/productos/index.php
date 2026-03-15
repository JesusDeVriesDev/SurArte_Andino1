<?php
ob_start();
$pageTitle = 'Mis Productos';
$pageId    = 'artistas';
require_once '../../_layout/head.php';
require_once '../../../config/db.php';

if (!$user || $user['rol'] !== 'artista') {
    header('Location: ' . $base . '/src/artistas/artistas.php'); exit;
}

$_guardVerificado = $_SESSION['artista_verificado'] ?? false;
if (!$_guardVerificado) {
    try {
        $gStmt = db()->prepare("SELECT verificado FROM artistas WHERE usuario_id = ?::uuid LIMIT 1");
        $gStmt->execute([$_SESSION['user_id']]);
        $gRow = $gStmt->fetch(PDO::FETCH_ASSOC);
        $_guardVerificado = ($gRow && $gRow['verificado'] == true);
        if ($_guardVerificado) $_SESSION['artista_verificado'] = true; // actualizar sesión
    } catch (Exception $e) { $_guardVerificado = false; }
}
if (!$_guardVerificado) {
    $_SESSION['_flash_warn'] = 'Tu perfil aún no ha sido verificado. Espera la revisión del administrador.';
    header('Location: ' . $base . '/src/artistas/artistas.php'); exit;
}

$ok    = $_SESSION['prod_ok']    ?? null;
$error = $_SESSION['prod_error'] ?? null;
unset($_SESSION['prod_ok'], $_SESSION['prod_error']);

try {
    $artStmt = db()->prepare("SELECT id, verificado FROM artistas WHERE usuario_id = ?::uuid");
    $artStmt->execute([$_SESSION['user_id']]);
    $artista = $artStmt->fetch();

    if (!$artista) { header('Location: ' . $base . '/src/artistas/registro/index.php'); exit; }

    $productos = db()->prepare(
        "SELECT * FROM productos WHERE artista_id = ?::uuid ORDER BY creado_en DESC"
    );
    $productos->execute([$artista['id']]);
    $productos = $productos->fetchAll();

} catch (PDOException $e) { $productos = []; $dbError = $e->getMessage(); }

$catIcons = ['musica'=>'🎵','arte'=>'🎨','artesania'=>'🧵','danza'=>'💃','literatura'=>'📖','otro'=>'✨'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/artistas/artistas.css"/>
  <link rel="stylesheet" href="<?= $base ?>/src/tienda/tienda.css"/>
</head>
<main>

  <?php if ($ok): ?><div class="alert alert-ok" style="margin-bottom:20px">✅ <?= htmlspecialchars($ok) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-err" style="margin-bottom:20px">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div style="display:flex;align-items:center;gap:12px;padding-top:40px;margin-bottom:8px">
    <a href="<?= $base ?>/src/artistas/perfil/index.php" style="font-family:var(--ff-m);font-size:.58rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(26,18,8,.38);text-decoration:none">← Mi perfil</a>
  </div>
  <div class="eyebrow">Tienda personal</div>
  <h1 class="page-h1" style="margin-bottom:8px">Mis <em>productos</em></h1>
  <p class="page-lead" style="margin-bottom:28px">Gestiona los productos que ofreces en SurArte Andino.</p>

  <?php if (!$artista['verificado']): ?>
  <div class="alert alert-info" style="margin-bottom:28px">
    ℹ️ Tu perfil está pendiente de verificación. Una vez verificado, tus productos serán visibles al público.
  </div>
  <?php endif; ?>

  <!-- Formulario nuevo producto -->
  <div class="admin-panel" style="margin-bottom:36px">
    <div class="panel-header">
      <div><div class="eyebrow" style="margin-bottom:4px">Publicar</div><h2 class="panel-title">Nuevo producto</h2></div>
    </div>
    <div style="padding:20px 22px">
      <form method="POST" action="guardar.php">
        <input type="hidden" name="accion" value="crear"/>
        <div class="form-grid-2">
          <div class="field">
            <label class="field-label">Nombre del producto *</label>
            <input class="field-input" type="text" name="nombre" required placeholder="Ej. Caja decorativa barniz" maxlength="200"/>
          </div>
          <div class="field">
            <label class="field-label">Categoría *</label>
            <select class="field-select" name="categoria" required>
              <option value="">Seleccionar…</option>
              <option value="artesania">🧵 Artesanía</option>
              <option value="arte">🎨 Arte</option>
              <option value="musica">🎵 Música</option>
              <option value="literatura">📖 Literatura</option>
              <option value="danza">💃 Danza</option>
              <option value="otro">✨ Otro</option>
            </select>
          </div>
        </div>
        <div class="field">
          <label class="field-label">Descripción</label>
          <textarea class="field-textarea" name="descripcion" rows="2" placeholder="Describe tu producto…"></textarea>
        </div>
        <div class="form-grid-2">
          <div class="field">
            <label class="field-label">Precio (COP) *</label>
            <input class="field-input" type="number" name="precio" required min="0" step="100" placeholder="85000"/>
          </div>
          <div class="field">
            <label class="field-label">Stock *</label>
            <input class="field-input" type="number" name="stock" required min="1" value="1"/>
          </div>
        </div>
        <div class="field">
          <label class="field-label">Imagen (URL)</label>
          <input class="field-input" type="url" name="imagen_url" placeholder="https://…"/>
        </div>
        <button type="submit" class="btn btn-gold" style="font-size:.7rem">Publicar producto →</button>
      </form>
    </div>
  </div>

  <div class="eyebrow" style="margin-bottom:16px">Mis productos (<?= count($productos) ?>)</div>
  <?php if (!empty($productos)): ?>
  <div class="products-grid">
    <?php foreach ($productos as $p):
      $ic = $catIcons[$p['categoria']] ?? '🛍️';
      $stockBadge = $p['stock'] > 3 ? ['ok','En stock '.$p['stock']] : ($p['stock'] > 0 ? ['low','Últimas '.$p['stock']] : ['out','Agotado']);
    ?>
    <div class="product-card">
      <div class="product-img-wrap">
        <?php if ($p['imagen_url']): ?>
          <img class="product-img-real" src="<?= htmlspecialchars($p['imagen_url']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" loading="lazy">
        <?php else: ?>
          <div class="product-img-placeholder"><?= $ic ?></div>
        <?php endif; ?>
        <span class="stock-badge stock-<?= $stockBadge[0] ?>"><?= $stockBadge[1] ?></span>
        <?php if (!$p['activo']): ?>
          <span style="position:absolute;top:10px;left:10px;background:rgba(26,18,8,.7);color:#fff;font-family:var(--ff-m);font-size:.45rem;padding:3px 8px;border-radius:999px;letter-spacing:.07em;text-transform:uppercase">Oculto</span>
        <?php endif; ?>
      </div>
      <div class="product-body">
        <div class="product-name"><?= htmlspecialchars($p['nombre']) ?></div>
        <?php if ($p['descripcion']): ?><div class="product-desc"><?= htmlspecialchars($p['descripcion']) ?></div><?php endif; ?>
        <div class="product-footer">
          <div class="product-price">$<?= number_format((float)$p['precio'],0,',','.') ?></div>
          <a href="editar.php?id=<?= $p['id'] ?>" class="btn-add" title="Editar" style="text-decoration:none;font-size:.75rem">✏️</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty" style="padding:60px 20px"><div class="empty-icon">🛍️</div><p>No tienes productos publicados aún.</p></div>
  <?php endif; ?>

</main>
</body>
</html>
