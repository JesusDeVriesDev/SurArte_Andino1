<?php
$pageTitle = 'Tienda';
$pageId    = 'tienda';
require_once '../_layout/head.php';
require_once '../../config/db.php';

try {
    $productosDestacados = db()->query(
        "SELECT p.id, p.nombre, p.descripcion, p.categoria, p.precio, p.stock, p.imagen_url,
                a.nombre AS artista_nombre, a.municipio, a.id AS artista_id
         FROM productos p
         JOIN artistas a ON p.artista_id = a.id
         WHERE p.activo = TRUE AND a.verificado = TRUE
         ORDER BY p.creado_en DESC LIMIT 24"
    )->fetchAll();

    $categorias = db()->query(
        "SELECT p.categoria, COUNT(*) AS total
         FROM productos p JOIN artistas a ON p.artista_id = a.id
         WHERE p.activo = TRUE AND a.verificado = TRUE AND p.categoria IS NOT NULL
         GROUP BY p.categoria ORDER BY total DESC"
    )->fetchAll();

    $carritoItems = [];
    $carritoTotal = 0;
    if ($user) {
        $cStmt = db()->prepare(
            "SELECT ci.id AS ci_id, ci.cantidad, p.id AS prod_id, p.nombre, p.precio,
                    p.stock, p.imagen_url, p.categoria
             FROM carrito_items ci
             JOIN productos p ON ci.producto_id = p.id
             WHERE ci.usuario_id = ?::uuid"
        );
        $cStmt->execute([$_SESSION['user_id']]);
        $carritoItems = $cStmt->fetchAll();
        $carritoTotal = array_sum(array_map(fn($i) => $i['precio'] * $i['cantidad'], $carritoItems));
    }
    $carritoCount = count($carritoItems);

} catch (PDOException $e) {
    $productosDestacados = $categorias = $carritoItems = [];
    $carritoCount = 0; $carritoTotal = 0;
    $dbError = $e->getMessage();
}

$catIcons  = ['musica'=>'🎵','arte'=>'🎨','artesania'=>'🧵','danza'=>'💃','literatura'=>'📖','otro'=>'✨'];
$catLabels = ['musica'=>'Música','arte'=>'Arte','artesania'=>'Artesanía','danza'=>'Danza','literatura'=>'Literatura','otro'=>'Otro'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/tienda/tienda.css"/>
</head>

<main>
  <?php if (isset($dbError)): ?>
  <div class="alert alert-err" style="margin-bottom:28px">⚠️ Error: <code><?= htmlspecialchars($dbError) ?></code></div>
  <?php endif; ?>

  <div class="products-header">
    <div class="products-hero-bg"></div>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px">
      <div>
        <div class="eyebrow">Arte hecho a mano</div>
        <h1 style="font-family:var(--ff-d);font-size:clamp(2rem,4vw,3.4rem);font-weight:900;color:var(--ink);line-height:.95;letter-spacing:-.03em;margin-bottom:14px">
          Tienda <em style="font-style:italic;color:var(--clay)">artesanal</em>
        </h1>
        <p style="font-family:var(--ff-b);font-size:1rem;font-weight:300;color:rgba(26,18,8,.45);max-width:480px;line-height:1.75">
          Adquiere piezas únicas de artistas verificados de Nariño.
        </p>
      </div>
      <?php if ($user): ?>
      <button class="cart-fab" id="cartFab" onclick="toggleCarrito()">
        🛒
        <span class="cart-badge" id="cartCount"><?= $carritoCount ?></span>
        <span class="cart-fab-label">Carrito</span>
      </button>
      <?php endif; ?>
    </div>

    <?php if (!empty($categorias)): ?>
    <div class="categories-strip">
      <button class="cat-pill active" data-cat="all">🛍️ Todos</button>
      <?php foreach ($categorias as $cat): ?>
        <button class="cat-pill" data-cat="<?= htmlspecialchars($cat['categoria']) ?>">
          <?= $catIcons[$cat['categoria']] ?? '✨' ?>
          <?= htmlspecialchars($catLabels[$cat['categoria']] ?? ucfirst($cat['categoria'])) ?>
          <span class="cat-count">(<?= $cat['total'] ?>)</span>
        </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="tienda-layout" id="tiendaLayout">

    <div class="products-grid" id="productsGrid">
      <?php if (!empty($productosDestacados)): ?>
        <?php foreach ($productosDestacados as $i => $p):
          $stockBadge = $p['stock'] > 3 ? ['ok','En stock'] : ($p['stock'] > 0 ? ['low','Últimas '.$p['stock']] : ['out','Agotado']);
          $catKey = $p['categoria'] ?? 'otro';
          $emoji  = $catIcons[$catKey] ?? '🛍️';
        ?>
        <div class="product-card" data-cat="<?= htmlspecialchars($catKey) ?>">
          <div class="product-img-wrap">
            <?php if (!empty($p['imagen_url']) && str_starts_with($p['imagen_url'],'http') && !str_contains($p['imagen_url'],'example.com')): ?>
              <img class="product-img-real" src="<?= htmlspecialchars($p['imagen_url']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" loading="lazy">
            <?php else: ?>
              <div class="product-img-placeholder"><?= $emoji ?></div>
            <?php endif; ?>
            <span class="stock-badge stock-<?= $stockBadge[0] ?>"><?= htmlspecialchars($stockBadge[1]) ?></span>
          </div>
          <div class="product-body">
            <?php if (!empty($p['artista_nombre'])): ?>
              <div class="product-artist">
                <a href="<?= $base ?>/src/artistas/perfil/index.php?id=<?= urlencode($p['artista_id']) ?>" style="color:inherit;text-decoration:none">
                  🎨 <?= htmlspecialchars($p['artista_nombre']) ?><?= $p['municipio'] ? ' · '.htmlspecialchars($p['municipio']) : '' ?>
                </a>
              </div>
            <?php endif; ?>
            <div class="product-name"><?= htmlspecialchars($p['nombre']) ?></div>
            <?php if (!empty($p['descripcion'])): ?>
              <div class="product-desc"><?= htmlspecialchars($p['descripcion']) ?></div>
            <?php endif; ?>
            <?php if (!empty($p['categoria'])): ?>
              <span class="badge badge-gold" style="margin-bottom:10px;align-self:flex-start">
                <?= $catIcons[$p['categoria']] ?? '✨' ?> <?= htmlspecialchars($catLabels[$p['categoria']] ?? ucfirst($p['categoria'])) ?>
              </span>
            <?php endif; ?>
            <div class="product-footer">
              <div class="product-price"><?= $p['precio'] > 0 ? '$'.number_format((float)$p['precio'],0,',','.') : 'Gratis' ?></div>
              <?php if ($user): ?>
                <button class="btn-add"
                  data-id="<?= htmlspecialchars($p['id']) ?>"
                  data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                  data-precio="<?= $p['precio'] ?>"
                  title="Agregar al carrito"
                  <?= $p['stock'] == 0 ? 'disabled' : '' ?>>+</button>
              <?php else: ?>
                <a href="<?= $base ?>/src/auth/login/index.php" style="width:32px;height:32px;border-radius:50%;background:var(--gold);color:var(--ink);display:flex;align-items:center;justify-content:center;font-size:1rem;text-decoration:none" title="Inicia sesión para comprar">+</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-products">
          <div class="empty-icon">🛍️</div>
          <p>Aún no hay productos disponibles.</p>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($user): ?>
    <aside class="carrito-panel<?= $carritoCount > 0 ? ' carrito-open' : '' ?>" id="carritoPanel">
      <div class="carrito-header">
        <h3 class="carrito-title">🛒 Mi carrito <span style="font-size:.7rem;font-weight:400;color:rgba(26,18,8,.38)">(<?= $carritoCount ?> items)</span></h3>
        <button onclick="toggleCarrito()" class="carrito-close-btn">✕</button>
      </div>

      <div class="carrito-items" id="carritoItems">
        <?php if (!empty($carritoItems)): ?>
          <?php foreach ($carritoItems as $ci): ?>
          <div class="carrito-item" id="ci-<?= htmlspecialchars($ci['prod_id']) ?>">
            <div class="ci-img">
              <?php if (!empty($ci['imagen_url']) && !str_contains($ci['imagen_url'],'example.com')): ?>
                <img src="<?= htmlspecialchars($ci['imagen_url']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:6px">
              <?php else: ?>
                <?= $catIcons[$ci['categoria']] ?? '🛍️' ?>
              <?php endif; ?>
            </div>
            <div class="ci-info">
              <div class="ci-nombre"><?= htmlspecialchars($ci['nombre']) ?></div>
              <div class="ci-precio">$<?= number_format((float)$ci['precio'],0,',','.') ?></div>
              <div class="ci-qty">
                <button onclick="cambiarCantidad('<?= htmlspecialchars($ci['prod_id']) ?>', -1)" class="qty-btn">−</button>
                <span id="qty-<?= htmlspecialchars($ci['prod_id']) ?>"><?= $ci['cantidad'] ?></span>
                <button onclick="cambiarCantidad('<?= htmlspecialchars($ci['prod_id']) ?>', 1)" class="qty-btn">+</button>
              </div>
            </div>
            <button onclick="quitarDelCarrito('<?= htmlspecialchars($ci['prod_id']) ?>')" class="ci-del" title="Quitar">✕</button>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="carrito-empty" id="carritoEmpty">
            <div style="font-size:2.2rem">🛒</div>
            <p>Tu carrito está vacío</p>
            <span style="font-size:.75rem;color:rgba(26,18,8,.3)">Agrega productos con el botón +</span>
          </div>
        <?php endif; ?>
      </div>

      <div class="carrito-footer" id="carritoFooter" style="<?= $carritoCount === 0 ? 'display:none' : '' ?>">
        <div class="carrito-total">
          Total: <strong id="carritoTotalEl">$<?= number_format($carritoTotal,0,',','.') ?></strong>
        </div>
        <button class="btn btn-gold" id="btnPagar"
          style="width:100%;justify-content:center;font-size:.75rem;margin-top:10px"
          onclick="pagar()">
          💳 Pagar ahora
        </button>
        <div style="font-family:var(--ff-m);font-size:.48rem;letter-spacing:.07em;text-transform:uppercase;color:rgba(26,18,8,.28);text-align:center;margin-top:8px">
          El stock se actualiza al confirmar
        </div>
      </div>
    </aside>
    <?php endif; ?>

  </div>

  <?php if (!$user || ($user['rol'] !== 'artista' && $user['rol'] !== 'admin')): ?>
  <div class="section-cta">
    <div class="section-cta-deco">🧵</div>
    <div style="position:relative">
      <h2 style="font-family:var(--ff-d);font-size:clamp(1.6rem,3vw,2.6rem);font-weight:900;color:#fff;line-height:1.1">
        ¿Tienes artesanías<br>que <em style="font-style:italic;color:var(--gold)">vender?</em>
      </h2>
      <p style="font-size:.95rem;font-weight:300;color:rgba(250,245,236,.45);margin-top:10px;max-width:360px">
        Crea tu perfil de artista y comienza a vender sin intermediarios.
      </p>
    </div>
    <div style="position:relative;display:flex;gap:10px;flex-wrap:wrap">
      <a href="<?= $base ?>/src/artistas/registro/index.php" class="btn btn-gold" style="font-size:.72rem">Empezar a vender →</a>
    </div>
  </div>
  <?php endif; ?>

</main>

<script src="<?= $base ?>/src/tienda/tienda.js"></script>
</body>
</html>
