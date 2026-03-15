<?php
ob_start();
$pageTitle = 'Panel de Administración';
$pageId    = 'admin';
require_once '../_layout/head.php';
require_once '../../config/db.php';

if (!$user || $user['rol'] !== 'admin') {
    header('Location: ' . $base . '/src/auth/login/index.php?redirect=admin');
    exit;
}

try {
    $stats = [
        'usuarios'  => db()->query("SELECT COUNT(*) FROM usuarios")->fetchColumn(),
        'artistas'  => db()->query("SELECT COUNT(*) FROM artistas")->fetchColumn(),
        'eventos'   => db()->query("SELECT COUNT(*) FROM eventos WHERE activo = TRUE")->fetchColumn(),
        'productos' => db()->query("SELECT COUNT(*) FROM productos WHERE activo = TRUE")->fetchColumn(),
        'pendientes'=> db()->query("SELECT COUNT(*) FROM artistas WHERE verificado = FALSE")->fetchColumn(),
        'tickets'   => 0, // tabla tickets existe en schema.sql pero puede no estar migrada
    ];

    $ultimosUsuarios = db()->query(
        "SELECT id, nombre, email, rol, creado_en FROM usuarios ORDER BY creado_en DESC LIMIT 6"
    )->fetchAll();

    $artistasPendientes = db()->query(
        "SELECT id, nombre, disciplina, municipio, creado_en
         FROM artistas WHERE verificado = FALSE ORDER BY creado_en DESC LIMIT 5"
    )->fetchAll();

    $ultimosEventos = db()->query(
        "SELECT titulo, categoria, lugar, municipio, fecha_inicio, precio
         FROM eventos ORDER BY creado_en DESC LIMIT 4"
    )->fetchAll();

    $ultimosProductos = db()->query(
        "SELECT p.nombre, p.precio, p.stock, p.categoria, a.nombre AS artista
         FROM productos p LEFT JOIN artistas a ON p.artista_id = a.id
         WHERE p.activo = TRUE ORDER BY p.creado_en DESC LIMIT 4"
    )->fetchAll();

} catch (PDOException $e) {
    $stats = ['usuarios'=>0,'artistas'=>0,'eventos'=>0,'productos'=>0,'pendientes'=>0,'tickets'=>0];
    $ultimosUsuarios = $artistasPendientes = $ultimosEventos = $ultimosProductos = [];
    $dbError = $e->getMessage();
}

$rolColors  = ['admin'=>'badge-clay','artista'=>'badge-sky','organizador'=>'badge-gold','visitante'=>'badge-muted'];
$catIcons   = ['musica'=>'🎵','arte'=>'🎨','artesania'=>'🧵','danza'=>'💃','literatura'=>'📖','otro'=>'✨'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/admin/admin.css"/>
</head>
<main>

  <?php if (isset($dbError)): ?>
  <div class="alert alert-err" style="margin-bottom:28px">
    ⚠️ Error de base de datos: <code style="font-family:var(--ff-m);font-size:.85em"><?= htmlspecialchars($dbError) ?></code>
  </div>
  <?php endif; ?>

  <div class="admin-hero">
    <div class="admin-hero-bg"></div>
    <div>
      <div class="eyebrow">Panel de control</div>
      <h1 class="page-h1">Administración <em>SurArte</em></h1>
      <p class="page-lead">Gestiona usuarios, artistas, eventos y productos de la plataforma.</p>
    </div>
    <div class="admin-hero-actions">
      <a href="<?= $base ?>/src/admin/usuarios.php"  class="btn btn-gold" style="font-size:.7rem">Gestionar usuarios</a>
      <a href="<?= $base ?>/src/admin/artistas.php"  class="btn btn-gold" style="font-size:.7rem">Gestionar artistas</a>
      <a href="<?= $base ?>/src/admin/eventos.php"   class="btn btn-gold" style="font-size:.7rem">Gestionar eventos</a>
      <a href="<?= $base ?>/src/admin/dashboard.php" class="btn btn-gold" style="font-size:.7rem">Ver Estadísticas</a>
    </div>
  </div>

  <div class="stats-grid">
    <?php
    $statsData = [
      ['👤', $stats['usuarios'],   'Usuarios',             'badge-sky',   $base.'/src/admin/usuarios.php'],
      ['🎨', $stats['artistas'],   'Artistas',             'badge-gold',  $base.'/src/admin/artistas.php'],
      ['📅', $stats['eventos'],    'Eventos activos',      'badge-clay',  $base.'/src/eventos/eventos.php'],
      ['🛍️', $stats['productos'],  'Productos activos',    'badge-green', $base.'/src/tienda/tienda.php'],
      ['⏳', $stats['pendientes'], 'Artistas por verificar','badge-muted', $base.'/src/admin/artistas.php'],
      ['🏔️', 'Nariño',            'Colombia · Sur Andino','badge-sky',   $base.'/src/inicio/inicio.php'],
    ];
    foreach ($statsData as [$ic, $n, $lbl, $badge, $href]):
    ?>
    <a href="<?= $href ?>" class="stat-card">
      <div class="stat-icon"><?= $ic ?></div>
      <div>
        <div class="stat-num"><?= is_numeric($n) ? number_format((int)$n) : $n ?></div>
        <div class="stat-lbl"><?= $lbl ?></div>
      </div>
      <span class="badge <?= $badge ?> stat-badge">Ver →</span>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="admin-cols">
    <div class="admin-col">
      <div class="admin-panel">
        <div class="panel-header">
          <div>
            <div class="eyebrow" style="margin-bottom:4px">Registro reciente</div>
            <h2 class="panel-title">Últimos usuarios</h2>
          </div>
          <a href="<?= $base ?>/src/admin/usuarios.php" class="panel-link">Ver todos →</a>
        </div>
        <?php if (!empty($ultimosUsuarios)): ?>
        <div class="user-list">
          <?php foreach ($ultimosUsuarios as $u): ?>
          <div class="user-row">
            <div class="user-avatar"><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></div>
            <div class="user-info">
              <div class="user-name"><?= htmlspecialchars($u['nombre']) ?></div>
              <div class="user-email"><?= htmlspecialchars($u['email']) ?></div>
            </div>
            <span class="badge <?= $rolColors[$u['rol']] ?? 'badge-muted' ?>"><?= $u['rol'] ?></span>
            <div class="user-date"><?= date('d/m/y', strtotime($u['creado_en'])) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty"><div class="empty-icon">👤</div><p>Sin usuarios registrados aún.</p></div>
        <?php endif; ?>
      </div>
      <div class="admin-panel" style="margin-top:24px">
        <div class="panel-header">
          <div>
            <div class="eyebrow" style="margin-bottom:4px">Por verificar</div>
            <h2 class="panel-title">Artistas pendientes</h2>
          </div>
          <a href="<?= $base ?>/src/admin/artistas.php" class="panel-link">Gestionar →</a>
        </div>
        <?php if (!empty($artistasPendientes)): ?>
        <div class="user-list">
          <?php foreach ($artistasPendientes as $a): ?>
          <div class="user-row">
            <div class="user-avatar" style="background:rgba(201,146,42,.15);color:var(--gold)"><?= $catIcons[$a['disciplina']] ?? '🎨' ?></div>
            <div class="user-info">
              <div class="user-name"><?= htmlspecialchars($a['nombre']) ?></div>
              <div class="user-email"><?= htmlspecialchars($a['disciplina'] ?? 'Sin disciplina') ?><?= $a['municipio'] ? ' · '.htmlspecialchars($a['municipio']) : '' ?></div>
            </div>
            <button class="btn-verify" onclick="verificarArtista('<?= $a['id'] ?>', this)">Verificar</button>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty"><div class="empty-icon">✅</div><p>Todos los artistas están verificados.</p></div>
        <?php endif; ?>
      </div>

    </div>
    <div class="admin-col">
      <div class="admin-panel">
        <div class="panel-header">
          <div>
            <div class="eyebrow" style="margin-bottom:4px">Navegación rápida</div>
            <h2 class="panel-title">Ver modulos</h2>
          </div>
        </div>
        <div class="quick-actions">
          <a href="<?= $base ?>/src/inicio/inicio.php"     class="qa-btn"><span class="qa-icon">🏔️</span><span class="qa-label">Inicio</span></a>
          <a href="<?= $base ?>/src/artistas/artistas.php"     class="qa-btn"><span class="qa-icon">🎨</span><span class="qa-label">Artistas</span></a>
          <a href="<?= $base ?>/src/eventos/eventos.php"    class="qa-btn"><span class="qa-icon">📅</span><span class="qa-label">Eventos</span></a>
          <a href="<?= $base ?>/src/tienda/tienda.php"      class="qa-btn"><span class="qa-icon">🛍️</span><span class="qa-label">Tienda</span></a>
          <a href="<?= $base ?>/src/comunidad/comunidad.php" class="qa-btn"><span class="qa-icon">🤝</span><span class="qa-label">Comunidad</span></a>
          <a href="<?= $base ?>/src/admin/dashboard.php"   class="qa-btn"><span class="qa-icon">📊</span><span class="qa-label">Dashboard</span></a>
        </div>
      </div>
      <div class="admin-panel" style="margin-top:24px">
        <div class="panel-header">
          <div>
            <div class="eyebrow" style="margin-bottom:4px">Agenda</div>
            <h2 class="panel-title">Últimos eventos</h2>
          </div>
          <a href="<?= $base ?>/src/eventos/eventos.php" class="panel-link">Ver todos →</a>
        </div>
        <?php if (!empty($ultimosEventos)): ?>
        <div class="pedidos-list">
          <?php foreach ($ultimosEventos as $ev):
            $dt = new DateTime($ev['fecha_inicio']);
          ?>
          <div class="pedido-row">
            <div class="pedido-id" style="min-width:42px;text-align:center">
              <div style="font-family:var(--ff-d);font-size:.95rem;font-weight:900;color:var(--gold);line-height:1"><?= $dt->format('d') ?></div>
              <div style="font-family:var(--ff-m);font-size:.42rem;letter-spacing:.07em;text-transform:uppercase;color:rgba(26,18,8,.38)"><?= $dt->format('M') ?></div>
            </div>
            <div class="pedido-info">
              <div class="pedido-cliente"><?= htmlspecialchars($ev['titulo']) ?></div>
              <div class="pedido-fecha"><?= htmlspecialchars($ev['municipio'] ?? '') ?><?= $ev['lugar'] ? ' · '.htmlspecialchars($ev['lugar']) : '' ?></div>
            </div>
            <span class="badge <?= $catIcons[$ev['categoria'] ?? 'otro'] ? 'badge-gold' : 'badge-muted' ?>"><?= $catIcons[$ev['categoria']] ?? '✨' ?> <?= ucfirst($ev['categoria'] ?? 'evento') ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty"><div class="empty-icon">📅</div><p>No hay eventos registrados aún.</p></div>
        <?php endif; ?>
      </div>
      <div class="admin-panel" style="margin-top:24px">
        <div class="panel-header">
          <div>
            <div class="eyebrow" style="margin-bottom:4px">Tienda</div>
            <h2 class="panel-title">Últimos productos</h2>
          </div>
          <a href="<?= $base ?>/src/tienda/tienda.php" class="panel-link">Ver todos →</a>
        </div>
        <?php if (!empty($ultimosProductos)): ?>
        <div class="pedidos-list">
          <?php foreach ($ultimosProductos as $p): ?>
          <div class="pedido-row">
            <div class="pedido-id" style="font-size:1.2rem"><?= $catIcons[$p['categoria']] ?? '🛍️' ?></div>
            <div class="pedido-info">
              <div class="pedido-cliente"><?= htmlspecialchars($p['nombre']) ?></div>
              <div class="pedido-fecha">por <?= htmlspecialchars($p['artista'] ?? 'Artista') ?></div>
            </div>
            <div class="pedido-total" style="font-size:.85rem">$<?= number_format((float)$p['precio'], 0, ',', '.') ?></div>
            <span class="badge <?= $p['stock'] > 0 ? 'badge-green' : 'badge-clay' ?>"><?= $p['stock'] > 0 ? 'Stock: '.$p['stock'] : 'Agotado' ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty"><div class="empty-icon">🛍️</div><p>No hay productos registrados aún.</p></div>
        <?php endif; ?>
      </div>

    </div>
  </div>

</main>
<script src="<?= $base ?>/src/admin/admin.js"></script>
</body>
</html>
