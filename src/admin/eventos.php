<?php
ob_start();
$pageTitle = 'Gestionar Eventos';
$pageId    = 'admin';
require_once '../_layout/head.php';
require_once '../../config/db.php';

// Acceso restringido al rol admin
if (!$user || $user['rol'] !== 'admin') {
    header('Location: ' . $base . '/src/auth/login/index.php?redirect=admin');
    exit;
}

// Mensajes flash del formulario de crear/editar eventos
$ok    = $_SESSION['evento_ok']    ?? null;
$error = $_SESSION['evento_error'] ?? null;
unset($_SESSION['evento_ok'], $_SESSION['evento_error']);

try {
    // Trae todos los eventos con el nombre del organizador para mostrarlo en la tabla
    $eventos = db()->query(
        "SELECT e.id, e.titulo, e.descripcion, e.categoria, e.lugar, e.municipio,
                e.fecha_inicio, e.fecha_fin, e.precio, e.aforo,
                e.imagen_url, e.activo, e.creado_en,
                u.nombre AS organizador_nombre
         FROM eventos e
         LEFT JOIN usuarios u ON e.organizador_id = u.id
         ORDER BY e.fecha_inicio DESC"
    )->fetchAll();
} catch (PDOException $e) {
    $eventos = [];
    $dbError = $e->getMessage();
}

// Arrays de categorías para el select del modal de creación/edición
$categorias = ['musica','arte','artesania','danza','literatura','otro'];
$catIcons   = ['musica'=>'🎵','arte'=>'🎨','artesania'=>'🧵','danza'=>'💃','literatura'=>'📖','otro'=>'✨'];
$catLabels  = ['musica'=>'Música','arte'=>'Arte','artesania'=>'Artesanía','danza'=>'Danza','literatura'=>'Literatura','otro'=>'Otro'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/admin/admin.css"/>
  <link rel="stylesheet" href="<?= $base ?>/src/admin/eventos.css"/>
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
  <div class="alert alert-err" style="margin-bottom:28px">⚠️ <?= htmlspecialchars($dbError) ?></div>
  <?php endif; ?>
  <?php if ($ok): ?>
  <div class="alert alert-ok" style="margin-bottom:28px">✅ <?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
  <div class="alert alert-err" style="margin-bottom:28px">❌ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="admin-hero">
    <div class="admin-hero-bg"></div>
    <div>
      <div style="display:flex;align-items:center;gap:10px;padding-top:0px;margin-bottom:8px">
      <a href="<?= $base ?>/src/admin/admin.php" style="font-family:var(--ff-m);font-size:.78rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#3d2b10;text-decoration:none">← Admin</a>
      <span style="color:rgba(26,18,8,.2)">/</span>
      <span style="font-family:var(--ff-m);font-size:.78rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--clay)">Eventos</span>
    </div>
      <h1 class="page-h1">Personalizar <em>eventos</em></h1>
      <p class="page-lead">Crea y administra los eventos de la plataforma.</p>
    </div>
    <div class="admin-hero-actions" style="padding-top:70px;margin-bottom:8px">
      <button class="btn btn-gold" style="font-size:.85rem" onclick="abrirModal()">+ Crear evento</button>
      <a href="<?= $base ?>/src/admin/admin.php" class="btn btn-outline" style="font-size:.85rem">← Panel</a>
    </div>
  </div>

  <div class="ev-toolbar">
    <input class="ev-search" type="text" id="evSearch" placeholder="Buscar evento…"/>
    <div class="ev-filters">
      <button class="filter-pill active" data-cat="all">Todos (<?= count($eventos) ?>)</button>
      <?php foreach ($categorias as $cat): ?>
        <?php $n = count(array_filter($eventos, fn($e) => $e['categoria'] === $cat)); ?>
        <?php if ($n > 0): ?>
        <button class="filter-pill" data-cat="<?= $cat ?>"><?= $catIcons[$cat] ?> <?= $catLabels[$cat] ?> (<?= $n ?>)</button>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (!empty($eventos)): ?>
  <div class="ev-grid" id="evGrid">
    <?php foreach ($eventos as $ev):
      $dt = new DateTime($ev['fecha_inicio']);
      $ic = $catIcons[$ev['categoria']] ?? '✨';
    ?>
    <div class="ev-card <?= !$ev['activo'] ? 'ev-card--inactive' : '' ?>" data-cat="<?= htmlspecialchars($ev['categoria'] ?? '') ?>">
      <div class="ev-card-date">
        <span class="ev-day"><?= $dt->format('d') ?></span>
        <span class="ev-month"><?= $dt->format('M') ?></span>
      </div>
      <div class="ev-card-body">
        <div class="ev-card-top">
          <span class="badge badge-gold ev-badge"><?= $ic ?> <?= htmlspecialchars($catLabels[$ev['categoria']] ?? ucfirst($ev['categoria'] ?? 'evento')) ?></span>
          <span class="badge <?= $ev['activo'] ? 'badge-green' : 'badge-muted' ?>"><?= $ev['activo'] ? 'Activo' : 'Inactivo' ?></span>
        </div>
        <div class="ev-title"><?= htmlspecialchars($ev['titulo']) ?></div>
        <?php if ($ev['lugar'] || $ev['municipio']): ?>
        <div class="ev-meta">📍 <?= htmlspecialchars(trim(($ev['lugar'] ?? '') . ($ev['lugar'] && $ev['municipio'] ? ' · ' : '') . ($ev['municipio'] ?? ''))) ?></div>
        <?php endif; ?>
        <div class="ev-meta">🕐 <?= $dt->format('d/m/Y H:i') ?><?= $ev['fecha_fin'] ? ' → ' . (new DateTime($ev['fecha_fin']))->format('d/m/Y H:i') : '' ?></div>
        <?php if ($ev['precio'] > 0): ?>
        <div class="ev-meta">💵 $<?= number_format((float)$ev['precio'], 0, ',', '.') ?></div>
        <?php else: ?>
        <div class="ev-meta">🎟️ Gratuito</div>
        <?php endif; ?>
      </div>
      <div class="ev-card-actions">
        <button class="action-btn-edit" onclick="editarEvento(<?= htmlspecialchars(json_encode($ev)) ?>)">Editar</button>
        <button class="action-btn-delete" onclick="eliminarEvento('<?= $ev['id'] ?>', this)"><?= $ev['activo'] ? 'Desactivar' : 'Activar' ?></button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty" style="padding:80px 20px">
    <div class="empty-icon">📅</div>
    <p>No hay eventos registrados aún.</p>
    <button class="btn btn-gold" style="margin-top:16px" onclick="abrirModal()">Crear primer evento →</button>
  </div>
  <?php endif; ?>

</main>

<div class="ev-overlay" id="evOverlay" onclick="cerrarModal()"></div>
<div class="ev-modal" id="evModal" role="dialog" aria-modal="true">
  <div class="ev-modal-header">
    <h2 class="ev-modal-title" id="evModalTitle">Crear evento</h2>
    <button class="ev-modal-close" onclick="cerrarModal()">✕</button>
  </div>
  <form method="POST" action="eventos_guardar.php" id="evForm">
    <input type="hidden" name="id" id="evId"/>
    <div class="form-grid-2">
      <div class="field" style="grid-column:1/-1">
        <label class="field-label">Título *</label>
        <input class="field-input" type="text" name="titulo" id="evTitulo" required maxlength="200" placeholder="Nombre del evento"/>
      </div>
      <div class="field">
        <label class="field-label">Categoría *</label>
        <select class="field-select" name="categoria" id="evCategoria" required>
          <option value="">Seleccionar…</option>
          <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat ?>"><?= $catIcons[$cat] ?> <?= $catLabels[$cat] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label class="field-label">Municipio</label>
        <input class="field-input" type="text" name="municipio" id="evMunicipio" placeholder="Ej: Pasto" maxlength="100"/>
      </div>
      <div class="field">
        <label class="field-label">Lugar / Venue</label>
        <input class="field-input" type="text" name="lugar" id="evLugar" placeholder="Teatro, parque, galería…" maxlength="200"/>
      </div>
      <div class="field">
        <label class="field-label">Fecha y hora inicio *</label>
        <input class="field-input" type="datetime-local" name="fecha_inicio" id="evFechaInicio" required/>
      </div>
      <div class="field">
        <label class="field-label">Fecha y hora fin</label>
        <input class="field-input" type="datetime-local" name="fecha_fin" id="evFechaFin"/>
      </div>
      <div class="field">
        <label class="field-label">Precio entrada ($)</label>
        <input class="field-input" type="number" name="precio" id="evPrecio" min="0" step="100" value="0" placeholder="0 = gratuito"/>
      </div>
      <div class="field">
        <label class="field-label">Aforo (personas)</label>
        <input class="field-input" type="number" name="aforo" id="evAforo" min="1" placeholder="Capacidad máxima"/>
      </div>
      <div class="field" style="grid-column:1/-1">
        <label class="field-label">Imagen (URL)</label>
        <input class="field-input" type="url" name="imagen_url" id="evImagen" placeholder="https://…"/>
      </div>
      <div class="field" style="grid-column:1/-1">
        <label class="field-label">Descripción</label>
        <textarea class="field-input ev-textarea" name="descripcion" id="evDescripcion" rows="3" placeholder="Detalle del evento…"></textarea>
      </div>
    </div>
    <div class="form-actions" style="margin-top:8px">
      <button type="submit" class="btn btn-gold" id="evSubmitBtn">Crear evento</button>
      <button type="button" class="btn btn-outline" onclick="cerrarModal()">Cancelar</button>
    </div>
  </form>
</div>

<script src="<?= $base ?>/src/admin/admin.js"></script>
<script src="<?= $base ?>/src/admin/eventos.js"></script>
</body>
</html>
