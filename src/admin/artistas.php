<?php
ob_start();
$pageTitle = 'Artistas — Admin';
$pageId    = 'admin';
require_once '../_layout/head.php';
require_once '../../config/db.php';

if (!$user || $user['rol'] !== 'admin') {
    header('Location: ' . $base . '/src/auth/login/index.php');
    exit;
}

try {
    // Trae todos los artistas con el email del usuario vinculado.
    // Se ordenan primero los no verificados para que el admin los atienda enseguida.
    $artistas = db()->query(
        "SELECT a.id, a.nombre, a.disciplina, a.municipio, a.verificado, a.creado_en,
                u.email AS usuario_email
         FROM artistas a
         LEFT JOIN usuarios u ON a.usuario_id = u.id
         ORDER BY a.verificado ASC, a.creado_en DESC"
    )->fetchAll();
} catch (PDOException $e) {
    $artistas = [];
    $dbError = $e->getMessage();
}

$catIcons    = ['musica'=>'🎵','arte'=>'🎨','artesania'=>'🧵','danza'=>'💃','literatura'=>'📖','otro'=>'✨',
                'pintura'=>'🖼️','escultura'=>'🗿','fotografia'=>'📷','teatro'=>'🎭'];
// Separa los arrays en pendientes y verificados para renderizar dos secciones distintas
$pendientes  = array_filter($artistas, fn($a) => !$a['verificado']);
$verificados = array_filter($artistas, fn($a) => $a['verificado']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/admin/admin.css"/>
  <style>
    /* ── Textos más grandes y oscuros ── */
    .eyebrow{font-size:.78rem!important;font-weight:700!important;color:#5a2d0c!important}
    .page-h1{color:#0d0902!important}
    .page-lead{font-size:clamp(1.05rem,1.5vw,1.2rem)!important;font-weight:400!important;color:#1A1208!important}
    .panel-title{font-size:1.05rem!important;font-weight:800!important;color:#0d0902!important}
    .panel-link{font-size:.72rem!important;font-weight:600!important}
    .eyebrow{font-size:.78rem!important;font-weight:700!important;color:#5a2d0c!important}
    /* Breadcrumb */
    .stat-lbl{font-size:.65rem!important;font-weight:600!important;color:#3d2b10!important}
    .stat-num{font-size:1.9rem!important;color:#0d0902!important}
    /* Tabla */
    .admin-table th{font-size:.65rem!important;font-weight:700!important;color:#3d2b10!important}
    .admin-table td{font-size:.95rem!important;color:#1A1208!important}
    /* user-row */
    .user-name{font-size:.95rem!important;font-weight:700!important;color:#0d0902!important}
    .user-email{font-size:.65rem!important;color:#3d2b10!important}
    .user-date{font-size:.65rem!important;color:#3d2b10!important}
    /* pedido-row / bar chart */
    .pedido-cliente{font-size:.95rem!important;font-weight:700!important;color:#0d0902!important}
    .pedido-fecha{font-size:.68rem!important;color:#3d2b10!important}
    .pedido-total{font-size:.95rem!important;color:#0d0902!important}
    .bar-label{font-size:.65rem!important;color:#3d2b10!important;font-weight:600!important}
    .bar-count{font-size:.88rem!important;font-weight:700!important;color:#0d0902!important}
    /* kpi-card */
    .kpi-label{font-size:.65rem!important;font-weight:600!important;color:#3d2b10!important}
    .kpi-value{font-size:2.1rem!important;color:#0d0902!important}
    .kpi-sub{font-size:.62rem!important;color:#3d2b10!important}
    /* cat-item */
    .cat-info-label{font-size:.62rem!important;font-weight:600!important;color:#3d2b10!important}
    .cat-info-count{font-size:1.05rem!important;font-weight:700!important;color:#0d0902!important}
    /* qa-label */
    .qa-label{font-size:.65rem!important;font-weight:600!important;color:#3d2b10!important}
    /* filter-pill */
    .filter-pill{font-size:.68rem!important;font-weight:600!important}
    /* admin-search */
    .admin-search,.admin-search::placeholder{font-size:.95rem!important}
    .admin-search::placeholder{color:rgba(26,18,8,.45)!important}
    /* campos de formulario (eventos) */
    .field-label{font-size:.82rem!important;font-weight:700!important;color:#1A1208!important;opacity:1!important}
    .field-input,.field-select,.field-textarea{font-size:1.05rem!important;font-weight:400!important;color:#0d0902!important;background:#FFFEF9!important;border:1.5px solid #EDE4D0!important}
    .field-input::placeholder,.field-textarea::placeholder{color:rgba(26,18,8,.55)!important}
    /* proximamente */
    .prox-title{color:#0d0902!important}
    .prox-desc{font-size:clamp(1rem,1.3vw,1.1rem)!important;font-weight:400!important;color:#1A1208!important}
    .prox-feat-title{font-size:.95rem!important;font-weight:800!important;color:#0d0902!important}
    .prox-feat-text{font-size:.65rem!important;color:#3d2b10!important}
    .prox-badge{font-size:.72rem!important;font-weight:700!important}
    .prox-progress-label{font-size:.62rem!important;color:#3d2b10!important}
  </style>
</head>
<main>

  <?php if (isset($dbError)): ?>
  <div class="alert alert-err" style="margin-bottom:28px">⚠️ Error de base de datos: <code><?= htmlspecialchars($dbError) ?></code></div>
  <?php endif; ?>
  <div style="display:flex;align-items:center;gap:10px;padding-top:40px;margin-bottom:8px">
    <a href="<?= $base ?>/src/admin/admin.php" style="font-family:var(--ff-m);font-size:.78rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#3d2b10;text-decoration:none">← Admin</a>
    <span style="color:rgba(26,18,8,.2)">/</span>
    <span style="font-family:var(--ff-m);font-size:.78rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--clay)">Artistas</span>
  </div>

  <h1 class="page-h1" style="margin-bottom:8px">Artistas <em>de la plataforma</em></h1>
  <p class="page-lead" style="margin-bottom:28px">Verifica perfiles artísticos y gestiona el contenido de los creadores de Nariño.</p>
  <div style="display:flex;gap:14px;margin-bottom:28px;flex-wrap:wrap">
    <div style="background:var(--white);border:1px solid var(--cream-dk);border-radius:var(--r-lg);padding:14px 20px;display:flex;align-items:center;gap:12px">
      <span style="font-size:1.4rem">🎨</span>
      <div>
        <div style="font-family:var(--ff-d);font-size:1.6rem;font-weight:900;color:#0d0902;line-height:1"><?= count($artistas) ?></div>
        <div style="font-family:var(--ff-m);font-size:.68rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#3d2b10">Total artistas</div>
      </div>
    </div>
    <div style="background:var(--white);border:1px solid var(--cream-dk);border-radius:var(--r-lg);padding:14px 20px;display:flex;align-items:center;gap:12px">
      <span style="font-size:1.4rem">✅</span>
      <div>
        <div style="font-family:var(--ff-d);font-size:1.6rem;font-weight:900;color:#16a34a;line-height:1"><?= count($verificados) ?></div>
        <div style="font-family:var(--ff-m);font-size:.68rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#3d2b10">Verificados</div>
      </div>
    </div>
    <div style="background:var(--white);border:1px solid rgba(239,68,68,.18);border-radius:var(--r-lg);padding:14px 20px;display:flex;align-items:center;gap:12px">
      <span style="font-size:1.4rem">⏳</span>
      <div>
        <div style="font-family:var(--ff-d);font-size:1.6rem;font-weight:900;color:var(--clay);line-height:1"><?= count($pendientes) ?></div>
        <div style="font-family:var(--ff-m);font-size:.68rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#3d2b10">Pendientes</div>
      </div>
    </div>
  </div>

  <div class="admin-toolbar">
    <input class="admin-search" id="adminSearch" type="text" placeholder="Buscar por nombre, disciplina o municipio…"/>
    <button class="filter-pill active" data-rol="all">Todos (<?= count($artistas) ?>)</button>
    <button class="filter-pill" data-rol="pendiente" style="<?= count($pendientes) > 0 ? 'border-color:var(--clay);color:var(--clay)' : '' ?>">
      ⏳ Pendientes (<?= count($pendientes) ?>)
    </button>
    <button class="filter-pill" data-rol="verificado">✅ Verificados (<?= count($verificados) ?>)</button>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Artista</th>
          <th>Disciplina</th>
          <th>Municipio</th>
          <th>Usuario</th>
          <th>Estado</th>
          <th>Registrado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php /* ORDER BY verificado ASC coloca los pendientes primero automáticamente */ ?>
        <?php if (!empty($artistas)): ?>
          <?php foreach ($artistas as $a): ?>
          <tr data-rol="<?= $a['verificado'] ? 'verificado' : 'pendiente' ?>">
            <td>
              <span class="table-avatar" style="background:rgba(201,146,42,.12);color:var(--gold)">
                <?= $catIcons[$a['disciplina']] ?? '🎨' ?>
              </span>
              <strong style="font-family:var(--ff-d);font-size:.95rem"><?= htmlspecialchars($a['nombre']) ?></strong>
            </td>
            <td><span class="badge badge-gold" style="font-size:.48rem"><?= htmlspecialchars($a['disciplina'] ?? '—') ?></span></td>
            <td style="font-family:var(--ff-m);font-size:.82rem;font-weight:500;color:#3d2b10">📍 <?= htmlspecialchars($a['municipio'] ?? '—') ?></td>
            <td style="font-family:var(--ff-m);font-size:.75rem;font-weight:500;color:#3d2b10"><?= htmlspecialchars($a['usuario_email'] ?? '—') ?></td>
            <td>
              <?php if ($a['verificado']): ?>
                <span class="badge badge-green">✓ Verificado</span>
              <?php else: ?>
                <span class="badge badge-clay">Pendiente</span>
              <?php endif; ?>
            </td>
            <td style="font-family:var(--ff-m);font-size:.78rem;font-weight:500;color:#3d2b10"><?= date('d/m/Y', strtotime($a['creado_en'])) ?></td>
            <td>
              <div class="table-actions">
                <?php if (!$a['verificado']): ?>
                  <button class="btn-verify" onclick="verificarArtista('<?= htmlspecialchars($a['id']) ?>', this)">Verificar</button>
                <?php else: ?>
                  <span class="badge badge-green" style="font-size:.45rem">✓</span>
                <?php endif; ?>
                <button class="action-btn action-btn-delete" onclick="eliminarArtista('<?= htmlspecialchars($a['id']) ?>', this)">Eliminar</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7">
              <div class="empty" style="padding:40px 0"><div class="empty-icon">🎨</div><p>No hay artistas registrados.</p></div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <div class="admin-pagination">
      <span><?= count($artistas) ?> artista<?= count($artistas) !== 1 ? 's' : '' ?> en total · <?= count($pendientes) ?> pendiente<?= count($pendientes) !== 1 ? 's' : '' ?> de verificación</span>
    </div>
  </div>

</main>
<script src="<?= $base ?>/src/admin/admin.js"></script>
</body>
</html>
