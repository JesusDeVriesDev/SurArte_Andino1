<?php
ob_start();
$pageTitle = 'Dashboard — Admin';
$pageId    = 'admin';
require_once '../_layout/head.php';
require_once '../../config/db.php';

if (!$user || $user['rol'] !== 'admin') {
    header('Location: ' . $base . '/src/auth/login/index.php');
    exit;
}

try {
    // KPIs del mes en curso — se calculan con DATE_TRUNC para siempre partir del 1ro del mes
    $resumen = [
        'nuevosUsuariosMes' => db()->query(
            "SELECT COUNT(*) FROM usuarios WHERE creado_en >= DATE_TRUNC('month', NOW())"
        )->fetchColumn(),
        'eventosMes'  => db()->query(
            "SELECT COUNT(*) FROM eventos WHERE activo = TRUE AND fecha_inicio >= DATE_TRUNC('month', NOW())"
        )->fetchColumn(),
        'artistasMes' => db()->query(
            "SELECT COUNT(*) FROM artistas WHERE creado_en >= DATE_TRUNC('month', NOW())"
        )->fetchColumn(),
        'productosMes' => db()->query(
            "SELECT COUNT(*) FROM productos WHERE activo = TRUE AND creado_en >= DATE_TRUNC('month', NOW())"
        )->fetchColumn(),
    ];

    // Datos para el gráfico de barras de registros mensuales (últimos 6 meses)
    $registrosMes = db()->query(
        "SELECT TO_CHAR(DATE_TRUNC('month', creado_en), 'Mon YY') AS mes,
                COUNT(*) AS total
         FROM usuarios
         WHERE creado_en >= NOW() - INTERVAL '6 months'
         GROUP BY DATE_TRUNC('month', creado_en)
         ORDER BY DATE_TRUNC('month', creado_en)"
    )->fetchAll();

    // Distribución de eventos por categoría para la gráfica de categorías
    $eventosPorCat = db()->query(
        "SELECT categoria, COUNT(*) AS total
         FROM eventos WHERE activo = TRUE AND categoria IS NOT NULL
         GROUP BY categoria ORDER BY total DESC"
    )->fetchAll();

    $productosPorCat = db()->query(
        "SELECT categoria, COUNT(*) AS total
         FROM productos WHERE activo = TRUE AND categoria IS NOT NULL
         GROUP BY categoria ORDER BY total DESC"
    )->fetchAll();

    // Top 5 municipios con más artistas registrados
    $artistasMunicipio = db()->query(
        "SELECT municipio, COUNT(*) AS total
         FROM artistas WHERE municipio IS NOT NULL
         GROUP BY municipio ORDER BY total DESC LIMIT 5"
    )->fetchAll();

} catch (PDOException $e) {
    $resumen = ['nuevosUsuariosMes'=>0,'eventosMes'=>0,'artistasMes'=>0,'productosMes'=>0];
    $registrosMes = $eventosPorCat = $productosPorCat = $artistasMunicipio = [];
    $dbError = $e->getMessage();
}

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
    <span style="font-family:var(--ff-m);font-size:.78rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--clay)">Dashboard</span>
  </div>
  <div class="eyebrow">Métricas del mes</div>
  <h1 class="page-h1" style="margin-bottom:8px">Dashboard <em>analítico</em></h1>
  <p class="page-lead" style="margin-bottom:36px">Resumen de actividad de la plataforma durante el mes actual.</p>

  <div class="dash-kpi-grid">
    <div class="kpi-card kpi-blue">
      <div class="kpi-icon">👤</div>
      <div class="kpi-label">Nuevos usuarios</div>
      <div class="kpi-value"><?= number_format((int)$resumen['nuevosUsuariosMes']) ?></div>
      <div class="kpi-sub">Este mes</div>
    </div>
    <div class="kpi-card kpi-gold">
      <div class="kpi-icon">🎨</div>
      <div class="kpi-label">Nuevos artistas</div>
      <div class="kpi-value"><?= number_format((int)$resumen['artistasMes']) ?></div>
      <div class="kpi-sub">Este mes</div>
    </div>
    <div class="kpi-card kpi-clay">
      <div class="kpi-icon">📅</div>
      <div class="kpi-label">Eventos activos</div>
      <div class="kpi-value"><?= number_format((int)$resumen['eventosMes']) ?></div>
      <div class="kpi-sub">Este mes</div>
    </div>
    <div class="kpi-card kpi-green">
      <div class="kpi-icon">🛍️</div>
      <div class="kpi-label">Productos nuevos</div>
      <div class="kpi-value"><?= number_format((int)$resumen['productosMes']) ?></div>
      <div class="kpi-sub">Este mes</div>
    </div>
  </div>

  <div class="dash-charts">
    <div class="chart-panel">
      <div class="panel-header">
        <div><div class="eyebrow" style="margin-bottom:4px">Tendencia</div><h2 class="panel-title">Usuarios últimos 6 meses</h2></div>
      </div>
      <div class="bar-chart">
        <?php /* Barras proporcionales al máximo del período — ancho calculado en el foreach */ ?>
        <?php if (!empty($registrosMes)):
          $maxVal = max(array_column($registrosMes, 'total')) ?: 1;
          foreach ($registrosMes as $r):
            $pct = round(($r['total'] / $maxVal) * 100);
        ?>
        <div class="bar-row">
          <span class="bar-label"><?= htmlspecialchars($r['mes']) ?></span>
          <div class="bar-track"><div class="bar-fill" style="width:<?= $pct ?>%"></div></div>
          <span class="bar-count"><?= $r['total'] ?></span>
        </div>
        <?php endforeach; else: ?>
        <div class="empty" style="padding:32px 0"><div class="empty-icon">📊</div><p>Sin datos aún.</p></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="chart-panel">
      <div class="panel-header">
        <div><div class="eyebrow" style="margin-bottom:4px">Geografía</div><h2 class="panel-title">Artistas por municipio</h2></div>
      </div>
      <div class="bar-chart">
        <?php /* Top 5 municipios con más artistas para ver concentración geográfica */ ?>
        <?php if (!empty($artistasMunicipio)):
          $maxM = max(array_column($artistasMunicipio, 'total')) ?: 1;
          foreach ($artistasMunicipio as $m):
            $pct = round(($m['total'] / $maxM) * 100);
        ?>
        <div class="bar-row">
          <span class="bar-label">📍 <?= htmlspecialchars($m['municipio']) ?></span>
          <div class="bar-track"><div class="bar-fill" style="width:<?= $pct ?>%;background:var(--clay)"></div></div>
          <span class="bar-count"><?= $m['total'] ?></span>
        </div>
        <?php endforeach; else: ?>
        <div class="empty" style="padding:32px 0"><div class="empty-icon">📍</div><p>Sin datos aún.</p></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="chart-panel">
      <div class="panel-header">
        <div><div class="eyebrow" style="margin-bottom:4px">Eventos</div><h2 class="panel-title">Eventos por categoría</h2></div>
      </div>
      <?php if (!empty($eventosPorCat)): ?>
      <div class="cat-grid">
        <?php foreach ($eventosPorCat as $c): ?>
        <div class="cat-item">
          <span class="cat-emoji"><?= $catIcons[$c['categoria']] ?? '✨' ?></span>
          <div>
            <div class="cat-info-count"><?= $c['total'] ?></div>
            <div class="cat-info-label"><?= ucfirst($c['categoria']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="empty" style="padding:32px 0"><div class="empty-icon">📅</div><p>Sin eventos aún.</p></div>
      <?php endif; ?>
    </div>
    <div class="chart-panel">
      <div class="panel-header">
        <div><div class="eyebrow" style="margin-bottom:4px">Tienda</div><h2 class="panel-title">Productos por categoría</h2></div>
      </div>
      <div class="bar-chart">
        <?php if (!empty($productosPorCat)):
          $maxP = max(array_column($productosPorCat, 'total')) ?: 1;
          foreach ($productosPorCat as $p):
            $pct = round(($p['total'] / $maxP) * 100);
        ?>
        <div class="bar-row">
          <span class="bar-label"><?= ($catIcons[$p['categoria']] ?? '✨').' '.ucfirst($p['categoria']) ?></span>
          <div class="bar-track"><div class="bar-fill" style="width:<?= $pct ?>%;background:var(--sky)"></div></div>
          <span class="bar-count"><?= $p['total'] ?></span>
        </div>
        <?php endforeach; else: ?>
        <div class="empty" style="padding:32px 0"><div class="empty-icon">🛍️</div><p>Sin productos aún.</p></div>
        <?php endif; ?>
      </div>
    </div>

  </div>

</main>
<script src="<?= $base ?>/src/admin/admin.js"></script>
</body>
</html>
