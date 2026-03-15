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

$catIcons   = ['musica'=>'🎵','arte'=>'🎨','artesania'=>'🧵','danza'=>'💃','literatura'=>'📖','otro'=>'✨',
               'pintura'=>'🖼️','escultura'=>'🗿','fotografia'=>'📷','teatro'=>'🎭'];
$pendientes = array_filter($artistas, fn($a) => !$a['verificado']);
$verificados = array_filter($artistas, fn($a) => $a['verificado']);
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
  <div class="alert alert-err" style="margin-bottom:28px">⚠️ Error de base de datos: <code><?= htmlspecialchars($dbError) ?></code></div>
  <?php endif; ?>
  <div style="display:flex;align-items:center;gap:10px;padding-top:40px;margin-bottom:8px">
    <a href="<?= $base ?>/src/admin/admin.php" style="font-family:var(--ff-m);font-size:.58rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(26,18,8,.38);text-decoration:none">← Admin</a>
    <span style="color:rgba(26,18,8,.2)">/</span>
    <span style="font-family:var(--ff-m);font-size:.58rem;letter-spacing:.1em;text-transform:uppercase;color:var(--clay)">Artistas</span>
  </div>

  <div class="eyebrow">Gestión</div>
  <h1 class="page-h1" style="margin-bottom:8px">Artistas <em>de la plataforma</em></h1>
  <p class="page-lead" style="margin-bottom:28px">Verifica perfiles artísticos y gestiona el contenido de los creadores de Nariño.</p>
  <div style="display:flex;gap:14px;margin-bottom:28px;flex-wrap:wrap">
    <div style="background:var(--white);border:1px solid var(--cream-dk);border-radius:var(--r-lg);padding:14px 20px;display:flex;align-items:center;gap:12px">
      <span style="font-size:1.4rem">🎨</span>
      <div>
        <div style="font-family:var(--ff-d);font-size:1.5rem;font-weight:900;color:var(--ink);line-height:1"><?= count($artistas) ?></div>
        <div style="font-family:var(--ff-m);font-size:.5rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(26,18,8,.38)">Total artistas</div>
      </div>
    </div>
    <div style="background:var(--white);border:1px solid var(--cream-dk);border-radius:var(--r-lg);padding:14px 20px;display:flex;align-items:center;gap:12px">
      <span style="font-size:1.4rem">✅</span>
      <div>
        <div style="font-family:var(--ff-d);font-size:1.5rem;font-weight:900;color:#16a34a;line-height:1"><?= count($verificados) ?></div>
        <div style="font-family:var(--ff-m);font-size:.5rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(26,18,8,.38)">Verificados</div>
      </div>
    </div>
    <div style="background:var(--white);border:1px solid rgba(239,68,68,.18);border-radius:var(--r-lg);padding:14px 20px;display:flex;align-items:center;gap:12px">
      <span style="font-size:1.4rem">⏳</span>
      <div>
        <div style="font-family:var(--ff-d);font-size:1.5rem;font-weight:900;color:var(--clay);line-height:1"><?= count($pendientes) ?></div>
        <div style="font-family:var(--ff-m);font-size:.5rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(26,18,8,.38)">Pendientes</div>
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
        <?php if (!empty($artistas)): ?>
          <?php foreach ($artistas as $a): ?>
          <tr data-rol="<?= $a['verificado'] ? 'verificado' : 'pendiente' ?>">
            <td>
              <span class="table-avatar" style="background:rgba(201,146,42,.12);color:var(--gold)">
                <?= $catIcons[$a['disciplina']] ?? '🎨' ?>
              </span>
              <strong style="font-family:var(--ff-d);font-size:.92rem"><?= htmlspecialchars($a['nombre']) ?></strong>
            </td>
            <td><span class="badge badge-gold" style="font-size:.48rem"><?= htmlspecialchars($a['disciplina'] ?? '—') ?></span></td>
            <td style="font-family:var(--ff-m);font-size:.75rem;color:rgba(26,18,8,.48)">📍 <?= htmlspecialchars($a['municipio'] ?? '—') ?></td>
            <td style="font-family:var(--ff-m);font-size:.68rem;color:rgba(26,18,8,.38)"><?= htmlspecialchars($a['usuario_email'] ?? '—') ?></td>
            <td>
              <?php if ($a['verificado']): ?>
                <span class="badge badge-green">✓ Verificado</span>
              <?php else: ?>
                <span class="badge badge-clay">Pendiente</span>
              <?php endif; ?>
            </td>
            <td style="font-family:var(--ff-m);font-size:.72rem;color:rgba(26,18,8,.35)"><?= date('d/m/Y', strtotime($a['creado_en'])) ?></td>
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
