<?php
ob_start();
$pageTitle = 'Usuarios — Admin';
$pageId    = 'admin';
require_once '../_layout/head.php';
require_once '../../config/db.php';

if (!$user || $user['rol'] !== 'admin') {
    header('Location: ' . $base . '/src/auth/login/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id     = $_POST['id'] ?? '';
    try {
        if ($accion === 'cambiarRol' && $id) {
            $rol = $_POST['rol'] ?? 'visitante';
            if (!in_array($rol, ['visitante','usuario','artista','organizador','admin'])) {
                $msgErr = 'Rol inválido.';
            } else {
                $stmt = db()->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
                $stmt->execute([$rol, $id]);
                $msgOk = 'Rol actualizado correctamente.';
            }
        } elseif ($accion === 'eliminar' && $id) {
            $stmt = db()->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $msgOk = 'Usuario eliminado.';
        }
    } catch (PDOException $e) {
        $msgErr = $e->getMessage();
    }
}

try {
    $usuarios = db()->query(
        "SELECT id, nombre, email, rol, activo, creado_en FROM usuarios ORDER BY creado_en DESC"
    )->fetchAll();
} catch (PDOException $e) {
    $usuarios = [];
    $dbError = $e->getMessage();
}

$rolColors = ['admin'=>'badge-clay','artista'=>'badge-sky','organizador'=>'badge-gold','visitante'=>'badge-muted','usuario'=>'badge-muted'];
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
  <?php if (isset($msgOk)): ?>
  <div class="alert alert-ok" style="margin-bottom:18px">✅ <?= htmlspecialchars($msgOk) ?></div>
  <?php endif; ?>
  <?php if (isset($msgErr)): ?>
  <div class="alert alert-err" style="margin-bottom:18px">❌ <?= htmlspecialchars($msgErr) ?></div>
  <?php endif; ?>

  <div style="display:flex;align-items:center;gap:10px;padding-top:40px;margin-bottom:8px">
    <a href="<?= $base ?>/src/admin/admin.php" style="font-family:var(--ff-m);font-size:.58rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(26,18,8,.38);text-decoration:none">← Admin</a>
    <span style="color:rgba(26,18,8,.2)">/</span>
    <span style="font-family:var(--ff-m);font-size:.58rem;letter-spacing:.1em;text-transform:uppercase;color:var(--clay)">Usuarios</span>
  </div>

  <div class="eyebrow">Gestión</div>
  <h1 class="page-h1" style="margin-bottom:8px">Usuarios <em>registrados</em></h1>
  <p class="page-lead" style="margin-bottom:28px">Administra los usuarios, cambia roles y gestiona el acceso a la plataforma.</p>

  <div class="admin-toolbar">
    <input class="admin-search" id="adminSearch" type="text" placeholder="Buscar por nombre o correo…"/>
    <button class="filter-pill active" data-rol="all">Todos (<?= count($usuarios) ?>)</button>
    <?php
    $roles = array_count_values(array_column($usuarios, 'rol'));
    foreach ($roles as $r => $c):
    ?>
    <button class="filter-pill" data-rol="<?= htmlspecialchars($r) ?>"><?= ucfirst($r) ?> (<?= $c ?>)</button>
    <?php endforeach; ?>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table" id="usersTable">
      <thead>
        <tr>
          <th>Usuario</th>
          <th>Correo</th>
          <th>Rol</th>
          <th>Estado</th>
          <th>Registrado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($usuarios)): ?>
          <?php foreach ($usuarios as $u): ?>
          <tr data-rol="<?= htmlspecialchars($u['rol']) ?>">
            <td>
              <span class="table-avatar"><?= mb_strtoupper(mb_substr($u['nombre'], 0, 1)) ?></span>
              <strong style="font-family:var(--ff-d);font-size:.92rem"><?= htmlspecialchars($u['nombre']) ?></strong>
            </td>
            <td style="font-family:var(--ff-m);font-size:.75rem;color:rgba(26,18,8,.52)"><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge <?= $rolColors[$u['rol']] ?? 'badge-muted' ?>"><?= $u['rol'] ?></span></td>
            <td>
              <?php if ($u['activo']): ?>
                <span class="badge badge-green">Activo</span>
              <?php else: ?>
                <span class="badge badge-clay">Inactivo</span>
              <?php endif; ?>
            </td>
            <td style="font-family:var(--ff-m);font-size:.72rem;color:rgba(26,18,8,.35)"><?= date('d/m/Y', strtotime($u['creado_en'])) ?></td>
            <td>
              <div class="table-actions">
                <select class="rol-select" data-uid="<?= htmlspecialchars($u['id']) ?>" onchange="cambiarRol('<?= htmlspecialchars($u['id']) ?>', this.value, this)"
                  style="font-family:var(--ff-m);font-size:.52rem;padding:4px 8px;border:1px solid rgba(26,18,8,.14);border-radius:var(--r);background:var(--cream-dk);color:var(--ink);cursor:pointer">
                  <?php foreach (['visitante','usuario','artista','organizador','admin'] as $r): ?>
                    <option value="<?= $r ?>" <?= $u['rol'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="action-btn action-btn-delete" onclick="eliminarUsuario('<?= htmlspecialchars($u['id']) ?>', this)">Eliminar</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6">
              <div class="empty" style="padding:40px 0"><div class="empty-icon">👤</div><p>No hay usuarios registrados.</p></div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <div class="admin-pagination">
      <span><?= count($usuarios) ?> usuario<?= count($usuarios) !== 1 ? 's' : '' ?> en total</span>
    </div>
  </div>

</main>
<script src="<?= $base ?>/src/admin/admin.js"></script>
</body>
</html>
