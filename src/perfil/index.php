<?php
// Carga los datos del usuario y su historial de pedidos.
// Si no hay sesión activa, redirige al login.
ob_start();
$pageTitle = 'Mi Perfil';
$pageId    = 'perfil';
require_once '../_layout/head.php';
require_once '../../config/db.php';

if (!$user) { header('Location: ' . $base . '/src/auth/login/index.php'); exit; }

try {
    // Trae todos los campos del usuario para pre-rellenar el formulario de cuenta
    $uStmt = db()->prepare("SELECT * FROM usuarios WHERE id = ?::uuid");
    $uStmt->execute([$_SESSION['user_id']]);
    $uData = $uStmt->fetch();

    // Historial de pedidos agrupado por pedido, con el conteo de productos incluidos
    $pedidosStmt = db()->prepare(
        "SELECT p.id, p.total, p.estado, p.creado_en,
                COUNT(pi.id) AS num_items
         FROM pedidos p
         LEFT JOIN pedido_items pi ON pi.pedido_id = p.id
         WHERE p.usuario_id = ?::uuid
         GROUP BY p.id ORDER BY p.creado_en DESC"
    );
    $pedidosStmt->execute([$_SESSION['user_id']]);
    $pedidos = $pedidosStmt->fetchAll();

} catch (PDOException $e) {
    $pedidos = [];
    $dbError = $e->getMessage();
}

// Mapa de estilos y emojis para los distintos estados de un pedido
$estadoColors = ['pagado'=>'badge-green','enviado'=>'badge-sky','entregado'=>'badge-green','cancelado'=>'badge-clay'];
$estadoIcons  = ['pagado'=>'✅','enviado'=>'🚚','entregado'=>'📦','cancelado'=>'❌'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/admin/admin.css"/>
  <link rel="stylesheet" href="<?= $base ?>/src/perfil/perfil.css"/>
</head>
<main>

  <?php if (isset($dbError)): ?>
  <div class="alert alert-err" style="margin-bottom:20px"><?= htmlspecialchars($dbError) ?></div>
  <?php endif; ?>

  <div style="padding-top:44px;display:flex;align-items:center;gap:20px;margin-bottom:32px">
    <div style="width:64px;height:64px;border-radius:50%;background:rgba(29,78,107,.12);color:var(--sky);display:flex;align-items:center;justify-content:center;font-family:var(--ff-d);font-size:1.8rem;font-weight:900">
    <?= mb_strtoupper(mb_substr($uData['nombre'] ?? 'U', 0, 1)) ?>
    </div>
    <div>
      <h1 class="page-h1" style="font-size:clamp(1.6rem,3vw,2.4rem);margin:0;color:#0d0902"><?= htmlspecialchars($uData['nombre'] ?? '') ?></h1>
      <div style="font-family:var(--ff-m);font-size:.72rem;font-weight:500;letter-spacing:.08em;text-transform:uppercase;color:#3d2b10;margin-top:6px">
        <?= htmlspecialchars($uData['email'] ?? '') ?> · <span class="badge badge-sky" style="font-size:.58rem"><?= $uData['rol'] ?? 'visitante' ?></span>
      </div>
    </div>
    <?php if ($user['rol'] === 'artista'): ?>
      <a href="<?= $base ?>/src/artistas/perfil/index.php" class="btn btn-outline" style="font-size:.65rem;margin-left:auto">Ver perfil artístico →</a>
    <?php endif; ?>
  </div>

  <div class="perfil-tabs">
    <button class="p-tab active" data-tab="compras">🛍️ Mis compras</button>
    <button class="p-tab" data-tab="cuenta">⚙️ Mi cuenta</button>
  </div>

  <div id="tab-compras">
    <?php if (!empty($pedidos)): ?>
      <div style="margin-bottom:18px;font-family:var(--ff-m);font-size:.75rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#3d2b10">
        <?= count($pedidos) ?> pedido<?= count($pedidos)!==1?'s':'' ?> realizados
      </div>
      <?php foreach ($pedidos as $ped): ?>
      <div class="pedido-card">
        <div class="pedido-card-header" onclick="togglePedido('<?= $ped['id'] ?>')">
          <div>
            <div class="pedido-num">Pedido #<?= strtoupper(substr($ped['id'],0,8)) ?></div>
            <div class="pedido-fecha-h"><?= date('d/m/Y H:i', strtotime($ped['creado_en'])) ?> · <?= $ped['num_items'] ?> producto<?= $ped['num_items']!==1?'s':'' ?></div>
          </div>
          <div style="display:flex;align-items:center;gap:12px">
            <span class="badge <?= $estadoColors[$ped['estado']] ?? 'badge-muted' ?>"><?= $estadoIcons[$ped['estado']] ?? '•' ?> <?= ucfirst($ped['estado']) ?></span>
            <div class="pedido-total-h">$<?= number_format((float)$ped['total'],0,',','.') ?></div>
            <span style="font-size:.8rem;color:rgba(26,18,8,.3)" id="chevron-<?= $ped['id'] ?>">▼</span>
          </div>
        </div>
        <div class="pedido-body" id="body-<?= $ped['id'] ?>">
          <?php
          try {
            $itemsStmt = db()->prepare(
              "SELECT pi.nombre_snap, pi.precio_snap, pi.cantidad,
                      p.imagen_url, p.categoria
               FROM pedido_items pi
               LEFT JOIN productos p ON pi.producto_id = p.id
               WHERE pi.pedido_id = ?::uuid"
            );
            $itemsStmt->execute([$ped['id']]);
            $itemsList = $itemsStmt->fetchAll();
          } catch(PDOException $e) { $itemsList = []; }
          $catIcons = ['musica'=>'🎵','arte'=>'🎨','artesania'=>'🧵','danza'=>'💃','literatura'=>'📖','otro'=>'✨'];
          foreach ($itemsList as $it):
          ?>
          <div class="pedido-item-row">
            <div style="width:40px;height:40px;border-radius:var(--r);background:var(--cream-dk);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0">
              <?php if ($it['imagen_url'] && !str_contains($it['imagen_url'],'example.com')): ?>
                <img src="<?= htmlspecialchars($it['imagen_url']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:var(--r)">
              <?php else: ?>
                <?= $catIcons[$it['categoria']] ?? '🛍️' ?>
              <?php endif; ?>
            </div>
            <div style="flex:1">
              <div style="font-family:var(--ff-d);font-size:.95rem;font-weight:800;color:#0d0902"><?= htmlspecialchars($it['nombre_snap']) ?></div>
              <div style="font-family:var(--ff-m);font-size:.65rem;font-weight:500;color:#3d2b10;letter-spacing:.04em;margin-top:2px">x<?= $it['cantidad'] ?> · $<?= number_format((float)$it['precio_snap'],0,',','.') ?> c/u</div>
            </div>
            <div style="font-family:var(--ff-d);font-size:.95rem;font-weight:700;color:var(--clay)">$<?= number_format($it['precio_snap']*$it['cantidad'],0,',','.') ?></div>
          </div>
          <?php endforeach; ?>
          <div style="text-align:right;padding-top:10px;font-family:var(--ff-d);font-size:.92rem;font-weight:700;color:var(--ink)">
            Total pagado: $<?= number_format((float)$ped['total'],0,',','.') ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

    <?php else: ?>
    <div class="empty" style="padding:60px 20px">
      <div class="empty-icon">🛍️</div>
      <p style='font-size:1.5rem;font-weight:500;color:#000000'>Aún no has realizado ninguna compra.</p>
      <a href="<?= $base ?>/src/tienda/tienda.php" class="btn btn-gold" style="margin-top:16px">Explorar tienda →</a>
    </div>
    <?php endif; ?>
  </div>

  <div id="tab-cuenta" style="display:none">
    <div class="form-card" style="max-width:560px">
      <?php $ok = $_SESSION['perfil_ok'] ?? null; $err = $_SESSION['perfil_err'] ?? null;
            unset($_SESSION['perfil_ok'],$_SESSION['perfil_err']); ?>
      <?php if ($ok): ?><div class="alert alert-ok" style="margin-bottom:16px">✅ <?= htmlspecialchars($ok) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-err" style="margin-bottom:16px">❌ <?= htmlspecialchars($err) ?></div><?php endif; ?>
      <form method="POST" action="guardar.php" id="perfilForm">
        <div class="field">
          <label class="field-label">Nombre completo</label>
          <input class="field-input" type="text" name="nombre" value="<?= htmlspecialchars($uData['nombre'] ?? '') ?>" required/>
        </div>
        <div class="field">
          <label class="field-label">Teléfono</label>
          <input class="field-input" type="tel" name="telefono" value="<?= htmlspecialchars($uData['telefono'] ?? '') ?>" placeholder="+57 300 0000000"/>
        </div>
        <div class="field">
          <label class="field-label">Nueva contraseña <span style="font-weight:300;text-transform:none">(dejar vacío para no cambiar)</span></label>
          <input class="field-input" type="password" id="perfil-password" name="password" placeholder="Mínimo 8 caracteres" autocomplete="new-password"/>
          <div class="strength-bar"><div class="strength-fill" id="perfil-strengthFill"></div></div>
          <span class="strength-label" id="perfil-strengthLabel"></span>
          <span class="input-error-msg" id="perfil-passErr"></span>
        </div>
        <div class="field">
          <label class="field-label">Confirmar nueva contraseña</label>
          <input class="field-input" type="password" id="perfil-confirm" name="confirm" placeholder="Repite la contraseña" autocomplete="new-password"/>
          <span class="input-error-msg" id="perfil-confirmErr"></span>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-gold" id="perfil-saveBtn">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>

</main>
<script src="<?= $base ?>/src/perfil/perfil.js"></script>
</body>
</html>