<?php

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/db.php';

$artistaId = $_GET['id'] ?? null;
$user      = isset($_SESSION['user_id'])
             ? ['nombre' => $_SESSION['nombre'], 'rol' => $_SESSION['rol']]
             : null;

$script = $_SERVER['SCRIPT_NAME'] ?? '';
$base   = '';
if (preg_match('#(/SurArte_Andino)#i', $script, $m)) {
    $base = $m[1];
}

try {
    if ($artistaId) {
        $stmt = db()->prepare(
            "SELECT a.*, u.email, u.avatar_url AS user_avatar
             FROM artistas a LEFT JOIN usuarios u ON a.usuario_id = u.id
             WHERE a.id = ?::uuid AND a.verificado = TRUE"
        );
        $stmt->execute([$artistaId]);
    } elseif ($user) {
        $stmt = db()->prepare(
            "SELECT a.*, u.email, u.avatar_url AS user_avatar
             FROM artistas a LEFT JOIN usuarios u ON a.usuario_id = u.id
             WHERE a.usuario_id = ?::uuid"
        );
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        header('Location: ' . $base . '/src/artistas/artistas.php');
        exit;
    }

    $artista = $stmt->fetch();

    if (!$artista) {
        header('Location: ' . $base . '/src/artistas/artistas.php');
        exit;
    }

    $stmtP = db()->prepare(
        "SELECT * FROM productos WHERE artista_id = ?::uuid AND activo = TRUE ORDER BY creado_en DESC"
    );
    $stmtP->execute([$artista['id']]);
    $productos = $stmtP->fetchAll();

    $esDueno = $user && isset($_SESSION['user_id']) && $artista['usuario_id'] === $_SESSION['user_id'];

    if ($esDueno && !$artista['verificado']) {
        $_SESSION['_flash_warn'] = 'Tu perfil aún no ha sido verificado. Un administrador lo revisará pronto.';
        header('Location: ' . $base . '/src/artistas/artistas.php');
        exit;
    }

    $perfilCarritoItems = [];
    $perfilCarritoTotal = 0;
    if ($user && !$esDueno) {
        $cStmt = db()->prepare(
            "SELECT ci.cantidad, p.id AS prod_id, p.nombre, p.precio, p.imagen_url, p.categoria
             FROM carrito_items ci JOIN productos p ON ci.producto_id = p.id
             WHERE ci.usuario_id = ?::uuid"
        );
        $cStmt->execute([$_SESSION['user_id']]);
        $perfilCarritoItems = $cStmt->fetchAll();
        $perfilCarritoTotal = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $perfilCarritoItems));
    }

} catch (PDOException $e) {
    $artista  = null;
    $productos = [];
    $dbError  = $e->getMessage();
}

$pageTitle = isset($artista) ? htmlspecialchars($artista['nombre']) . ' — SurArte Andino' : 'Perfil Artista';
$pageId    = 'artistas';
require_once '../../_layout/head.php';

$catIcons = ['musica'=>'🎵','arte'=>'🎨','artesania'=>'🧵','danza'=>'💃','literatura'=>'📖',
             'pintura'=>'🖼️','escultura'=>'🗿','fotografia'=>'📷','teatro'=>'🎭','otro'=>'✨'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/artistas/artistas.css"/>
  <link rel="stylesheet" href="<?= $base ?>/src/tienda/tienda.css"/>
</head>
<main>

  <?php if (isset($dbError)): ?>
  <div class="alert alert-err"><?= htmlspecialchars($dbError) ?></div>
  <?php endif; ?>

  <?php if ($artista): ?>

  <div class="perfil-hero">
    <div class="perfil-avatar">
      <?php $foto = $artista['foto_url'] ?? $artista['user_avatar'] ?? null; ?>
      <?php if ($foto): ?>
        <img src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($artista['nombre']) ?>"/>
      <?php else: ?>
        <span style="font-size:3.5rem"><?= $catIcons[$artista['disciplina']] ?? '🎨' ?></span>
      <?php endif; ?>
    </div>

    <div>
      <?php if ($artista['verificado']): ?>
        <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:999px;padding:5px 14px;font-family:var(--ff-m);font-size:.72rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:#16a34a;margin-bottom:16px">
          <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block"></span> Artista verificado
        </div>
      <?php endif; ?>
      <h1 class="perfil-nombre" style="color:#0d0902;font-size:clamp(2.2rem,4.5vw,3.8rem)"><?= htmlspecialchars($artista['nombre']) ?></h1>

      <div class="perfil-meta">
        <?php if ($artista['disciplina']): ?>
          <span class="badge badge-gold" style="font-size:.72rem;font-weight:600;padding:5px 14px"><?= $catIcons[$artista['disciplina']] ?? '🎨' ?> <?= htmlspecialchars($artista['disciplina']) ?></span>
        <?php endif; ?>
        <?php if ($artista['municipio']): ?>
          <span class="badge badge-muted" style="font-size:.72rem;font-weight:600;padding:5px 14px;color:#3d2b10">📍 <?= htmlspecialchars($artista['municipio']) ?></span>
        <?php endif; ?>
      </div>

      <?php if ($artista['bio']): ?>
        <p class="perfil-bio" style="font-size:clamp(1.05rem,1.6vw,1.2rem);font-weight:400;color:#1A1208;line-height:1.85"><?= nl2br(htmlspecialchars($artista['bio'])) ?></p>
      <?php endif; ?>

      <div class="perfil-social">
        <?php if (!empty($artista['instagram'])): ?>
          <a href="https://instagram.com/<?= htmlspecialchars(ltrim($artista['instagram'],'@')) ?>" target="_blank" style="font-size:.78rem;font-weight:600;color:#3d2b10">📷 Instagram</a>
        <?php endif; ?>
        <?php if (!empty($artista['facebook'])): ?>
          <a href="<?= htmlspecialchars($artista['facebook']) ?>" target="_blank" style="font-size:.78rem;font-weight:600;color:#3d2b10">📘 Facebook</a>
        <?php endif; ?>
        <?php if (!empty($artista['website'])): ?>
          <a href="<?= htmlspecialchars($artista['website']) ?>" target="_blank" style="font-size:.78rem;font-weight:600;color:#3d2b10">🌐 Sitio web</a>
        <?php endif; ?>
      </div>

      <?php if ($esDueno): ?>
      <div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap">
        <a href="<?= $base ?>/src/artistas/editar/index.php" class="btn btn-gold" style="font-size:.82rem">✏️ Editar perfil</a>
        <a href="<?= $base ?>/src/artistas/productos/index.php" class="btn btn-outline" style="font-size:.82rem">🛍️ Mis productos</a>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="perfil-section">
    <div class="perfil-section-header">
      <div>
        <div class="eyebrow" style="margin-bottom:4px;font-size:.75rem;font-weight:700;color:#5a2d0c">Tienda personal</div>
        <h2 class="perfil-section-title" style="font-size:clamp(1.6rem,3vw,2.4rem);color:#0d0902">Obras y <em style="color:var(--clay)">productos</em></h2>
      </div>
      <?php if ($esDueno): ?>
        <a href="<?= $base ?>/src/artistas/productos/index.php" class="btn btn-gold" style="font-size:.68rem">+ Agregar producto</a>
      <?php endif; ?>
    </div>

    <?php if (!empty($productos)): ?>
    <div class="products-grid">
      <?php foreach ($productos as $p):
        $stockBadge = $p['stock'] > 3 ? ['ok','En stock'] : ($p['stock'] > 0 ? ['low','Últimas '.$p['stock']] : ['out','Agotado']);
        $ic = $catIcons[$p['categoria']] ?? '🛍️';
      ?>
      <div class="product-card" data-cat="<?= htmlspecialchars($p['categoria'] ?? '') ?>">
        <div class="product-img-wrap">
          <?php if (!empty($p['imagen_url']) && str_starts_with($p['imagen_url'],'http') && !str_contains($p['imagen_url'],'example.com')): ?>
            <img class="product-img-real" src="<?= htmlspecialchars($p['imagen_url']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" loading="lazy">
          <?php else: ?>
            <div class="product-img-placeholder"><?= $ic ?></div>
          <?php endif; ?>
          <span class="stock-badge stock-<?= $stockBadge[0] ?>"><?= htmlspecialchars($stockBadge[1]) ?></span>
        </div>
        <div class="product-body">
          <div class="product-name"><?= htmlspecialchars($p['nombre']) ?></div>
          <?php if ($p['descripcion']): ?>
            <div class="product-desc"><?= htmlspecialchars($p['descripcion']) ?></div>
          <?php endif; ?>
          <div class="product-footer">
            <div class="product-price">$<?= number_format((float)$p['precio'],0,',','.') ?></div>
            <?php if ($user && !$esDueno): ?>
              <button class="btn-add"
                data-id="<?= htmlspecialchars($p['id']) ?>"
                data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                data-precio="<?= $p['precio'] ?>"
                title="Agregar al carrito"
                <?= $p['stock'] == 0 ? 'disabled' : '' ?>>+</button>
            <?php elseif ($esDueno): ?>
              <a href="<?= $base ?>/src/artistas/productos/editar.php?id=<?= urlencode($p['id']) ?>" class="btn-add" title="Editar" style="text-decoration:none;font-size:.75rem">✏️</a>
            <?php else: ?>
              <a href="<?= $base ?>/src/auth/login/index.php"
                 style="width:32px;height:32px;border-radius:50%;background:var(--gold);color:var(--ink);display:flex;align-items:center;justify-content:center;font-size:1rem;text-decoration:none"
                 title="Inicia sesión para comprar">+</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty" style="padding:60px 20px">
      <div class="empty-icon">🛍️</div>
      <p style='font-size:1.5rem;font-weight:500;color:#000000'>Este artista aún no tiene productos publicados.</p>
      <?php if ($esDueno): ?>
        <a href="<?= $base ?>/src/artistas/productos/index.php" class="btn btn-gold" style="margin-top:12px">Publicar mi primer producto →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <?php endif; ?>
</main>
<script src="<?= $base ?>/src/artistas/perfil/perfil.js"></script>
</body>
</html>