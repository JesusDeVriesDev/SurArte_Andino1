<?php
ob_start();
$pageTitle = 'Próximamente';
$pageId    = 'admin';
require_once '../_layout/head.php';
require_once '../../config/db.php';

if (!$user || $user['rol'] !== 'admin') {
    header('Location: ' . $base . '/src/auth/login/index.php?redirect=admin');
    exit;
}

// Esta página es un placeholder genérico para módulos del panel que aún no están implementados.
// Recibe el nombre del módulo, su ícono y descripción como parámetros GET para reutilizarse
// sin duplicar HTML. El admin llega aquí desde los links del sidebar que apuntan a módulos pendientes.
$modulo = htmlspecialchars($_GET['modulo'] ?? 'este módulo');
$icono  = htmlspecialchars($_GET['icono']  ?? '🚀');
$desc   = htmlspecialchars($_GET['desc']   ?? 'Estamos trabajando para traerte nuevas funcionalidades al panel de administración.');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/admin/admin.css"/>
  <style>
    .prox-page {
      min-height: calc(100vh - var(--nav-h, 64px) - 80px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 24px;
    }

    .prox-card {
      position: relative;
      max-width: 640px;
      width: 100%;
      background: #fff;
      border: 1px solid var(--cream-dk);
      border-radius: 28px;
      padding: 64px 56px;
      text-align: center;
      overflow: hidden;
      box-shadow: 0 12px 48px rgba(26,18,8,.08);
    }

    .prox-card::before {
      content: '';
      position: absolute;
      top: -80px; left: 50%;
      transform: translateX(-50%);
      width: 500px; height: 500px;
      background: radial-gradient(ellipse 70% 60% at 50% 40%,
        rgba(201,146,42,.09) 0%,
        rgba(139,58,28,.05) 50%,
        transparent 70%);
      pointer-events: none;
    }
    .prox-card::after {
      content: '';
      position: absolute;
      bottom: -40px; right: -40px;
      width: 280px; height: 280px;
      background: radial-gradient(ellipse 80% 80% at 100% 100%,
        rgba(29,78,107,.07) 0%, transparent 70%);
      pointer-events: none;
    }

    .prox-deco-text {
      position: absolute;
      bottom: -12px; left: 50%;
      transform: translateX(-50%);
      font-family: var(--ff-d);
      font-size: 8rem;
      font-weight: 900;
      color: rgba(26,18,8,.025);
      white-space: nowrap;
      pointer-events: none;
      user-select: none;
      letter-spacing: -.04em;
    }

    .prox-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-family: var(--ff-m);
      font-size: .58rem;
      letter-spacing: .22em;
      text-transform: uppercase;
      color: var(--gold);
      background: rgba(201,146,42,.09);
      border: 1px solid rgba(201,146,42,.25);
      border-radius: 9999px;
      padding: 6px 18px;
      margin-bottom: 32px;
      position: relative;
    }
    .prox-badge-dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: var(--gold);
      animation: proxBlink 1.4s ease-in-out infinite;
    }
    @keyframes proxBlink {
      0%, 100% { opacity: 1; }
      50%       { opacity: .15; }
    }

    .prox-icon-ring {
      position: relative;
      width: 88px; height: 88px;
      border-radius: 50%;
      background: linear-gradient(135deg, rgba(201,146,42,.14) 0%, rgba(139,58,28,.07) 100%);
      border: 1.5px solid rgba(201,146,42,.25);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.4rem;
      margin: 0 auto 28px;
      animation: proxPulse 3s ease-in-out infinite;
    }
    @keyframes proxPulse {
      0%, 100% { box-shadow: 0 0 0 0 rgba(201,146,42,.18); }
      50%       { box-shadow: 0 0 0 16px rgba(201,146,42,.0); }
    }

    .prox-title {
      font-family: var(--ff-d);
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 900;
      color: var(--ink);
      line-height: .95;
      letter-spacing: -.03em;
      margin-bottom: 18px;
      position: relative;
    }
    .prox-title em {
      font-style: italic;
      color: var(--clay);
    }

    .prox-line {
      width: 38px; height: 3px;
      background: var(--gold);
      border-radius: 2px;
      margin: 0 auto 20px;
    }

    .prox-desc {
      font-family: var(--ff-b);
      font-size: clamp(.92rem, 1.2vw, 1.02rem);
      font-weight: 300;
      color: rgba(26,18,8,.46);
      max-width: 440px;
      line-height: 1.85;
      margin: 0 auto 40px;
      position: relative;
    }

    .prox-progress-wrap {
      background: var(--cream-dk);
      border-radius: 9999px;
      height: 6px;
      max-width: 320px;
      margin: 0 auto 10px;
      overflow: hidden;
      position: relative;
    }
    .prox-progress-bar {
      height: 100%;
      width: 65%;
      background: linear-gradient(90deg, var(--gold) 0%, var(--gold-lt) 100%);
      border-radius: 9999px;
      animation: proxProgress 2.4s ease-in-out infinite alternate;
    }
    @keyframes proxProgress {
      0%   { width: 55%; opacity: .85; }
      100% { width: 72%; opacity: 1; }
    }
    .prox-progress-label {
      font-family: var(--ff-m);
      font-size: .5rem;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: rgba(26,18,8,.35);
      margin-bottom: 40px;
    }

    .prox-actions {
      display: flex;
      gap: 10px;
      justify-content: center;
      flex-wrap: wrap;
      position: relative;
    }

    .prox-features {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      max-width: 580px;
      margin: 0 auto 40px;
      position: relative;
    }
    .prox-feat {
      background: var(--cream, #FAF5EC);
      border: 1px solid var(--cream-dk);
      border-radius: 14px;
      padding: 18px 14px;
      transition: box-shadow .25s, transform .25s, border-color .25s;
    }
    .prox-feat:hover {
      box-shadow: 0 4px 18px rgba(26,18,8,.08);
      transform: translateY(-3px);
      border-color: rgba(201,146,42,.2);
    }
    .prox-feat-icon { font-size: 1.5rem; margin-bottom: 8px; }
    .prox-feat-title {
      font-family: var(--ff-d);
      font-size: .82rem;
      font-weight: 700;
      color: var(--ink);
      margin-bottom: 4px;
    }
    .prox-feat-text {
      font-family: var(--ff-m);
      font-size: .48rem;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: rgba(26,18,8,.36);
      line-height: 1.6;
    }

    @media (max-width: 600px) {
      .prox-card { padding: 44px 28px; }
      .prox-features { grid-template-columns: 1fr; max-width: 280px; }
      .prox-deco-text { font-size: 5rem; }
    }
  </style>
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
  <div class="prox-page">
    <div class="prox-card">

      <div class="prox-deco-text">Admin</div>

      <div class="prox-badge">
        <span class="prox-badge-dot"></span>
        En desarrollo
      </div>

      <div class="prox-icon-ring"><?= $icono ?></div>

      <h1 class="prox-title">
        Próximamente<br>
        <em><?= $modulo ?></em>
      </h1>

      <div class="prox-line"></div>

      <p class="prox-desc"><?= $desc ?></p>

      <div class="prox-features">
        <div class="prox-feat">
          <div class="prox-feat-icon">📊</div>
          <div class="prox-feat-title">Estadísticas</div>
          <div class="prox-feat-text">Reportes y métricas detalladas</div>
        </div>
        <div class="prox-feat">
          <div class="prox-feat-icon">⚙️</div>
          <div class="prox-feat-title">Configuración</div>
          <div class="prox-feat-text">Ajustes avanzados del módulo</div>
        </div>
        <div class="prox-feat">
          <div class="prox-feat-icon">🔔</div>
          <div class="prox-feat-title">Notificaciones</div>
          <div class="prox-feat-text">Alertas en tiempo real</div>
        </div>
      </div>

      <div class="prox-progress-wrap">
        <div class="prox-progress-bar"></div>
      </div>
      <div class="prox-progress-label">Desarrollo en curso</div>

      <!-- Botones -->
      <div class="prox-actions">
        <a href="<?= $base ?>/src/admin/admin.php" class="btn btn-gold" style="font-size:.85rem">
          ← Volver al panel
        </a>
        <a href="<?= $base ?>/src/admin/dashboard.php" class="btn btn-outline" style="font-size:.85rem">
          Ver estadísticas
        </a>
      </div>

    </div>
  </div>
</main>

<script src="<?= $base ?>/src/admin/admin.js"></script>
</body>
</html>