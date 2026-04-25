<?php
ob_start();
$pageTitle = 'Editar Perfil';
$pageId    = 'artistas';
require_once '../../_layout/head.php';
require_once '../../../config/db.php';

// Solo artistas pueden editar su perfil artístico
if (!$user || $user['rol'] !== 'artista') {
    header('Location: ' . $base . '/src/artistas/artistas.php'); exit;
}

// Comprueba si el artista ya está verificado — la sesión puede estar desactualizada
// si el admin lo verificó después del último login, así que siempre consultamos la BD
$_guardVerificado = $_SESSION['artista_verificado'] ?? false;
if (!$_guardVerificado) {
    try {
        $gStmt = db()->prepare("SELECT verificado FROM artistas WHERE usuario_id = ?::uuid LIMIT 1");
        $gStmt->execute([$_SESSION['user_id']]);
        $gRow = $gStmt->fetch(PDO::FETCH_ASSOC);
        $_guardVerificado = ($gRow && $gRow['verificado'] == true);
        // Si acaba de ser verificado, actualiza la sesión para que el nav lo refleje
        if ($_guardVerificado) $_SESSION['artista_verificado'] = true;
    } catch (Exception $e) { $_guardVerificado = false; }
}

// Un artista no verificado no tiene acceso al editor — dejamos el aviso y redirigimos
if (!$_guardVerificado) {
    $_SESSION['_flash_warn'] = 'Tu perfil aún no ha sido verificado. Espera la revisión del administrador.';
    header('Location: ' . $base . '/src/artistas/artistas.php'); exit;
}

// Mensajes flash del último guardado
$ok    = $_SESSION['editar_ok']    ?? null;
$error = $_SESSION['editar_error'] ?? null;
unset($_SESSION['editar_ok'], $_SESSION['editar_error']);

try {
    // Carga los datos artísticos y los de la cuenta de usuario por separado
    // porque viven en tablas distintas (artistas y usuarios)
    $stmt = db()->prepare("SELECT * FROM artistas WHERE usuario_id = ?::uuid");
    $stmt->execute([$_SESSION['user_id']]);
    $artista = $stmt->fetch();
    if (!$artista) { header('Location: ' . $base . '/src/artistas/registro/index.php'); exit; }

    $uStmt = db()->prepare("SELECT nombre, email, bio, telefono FROM usuarios WHERE id = ?::uuid");
    $uStmt->execute([$_SESSION['user_id']]);
    $uData = $uStmt->fetch();
} catch (PDOException $e) {
    $dbError = $e->getMessage();
}

// Lista de disciplinas para el select del formulario de perfil artístico
$disciplinas = ['Barniz de Pasto','Cerámica','Pintura','Escultura','Música Andina','Danza','Literatura',
                'Fotografía','Teatro','Artesanía','Tejido','Orfebrería','Otro'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/artistas/artistas.css"/>
  <link rel="stylesheet" href="<?= $base ?>/src/artistas/editar/editar.css"/>
  <style>
    .field-label{font-size:.82rem!important;font-weight:700!important;color:#1A1208!important;opacity:1!important}
    .field-input,.field-select,.field-textarea{font-size:1.05rem!important;font-weight:400!important;color:#0d0902!important;background:#FFFEF9!important;border:1.5px solid #EDE4D0!important}
    .field-input::placeholder,.field-textarea::placeholder{color:rgba(26,18,8,.55)!important}
    .field-input:focus,.field-select:focus,.field-textarea:focus{border-color:var(--gold)!important;box-shadow:0 0 0 3px rgba(201,146,42,.1)!important;outline:none!important}
    .tab-btn{font-size:.78rem!important;font-weight:600!important;letter-spacing:.08em!important}
    .strength-label{font-size:.72rem!important;font-weight:500!important}
    .input-error-msg{font-size:.72rem!important;font-weight:600!important}
  </style>
</head>
<main>
  <div style="max-width:680px;margin:0 auto;padding-top:48px">
    <a href="<?= $base ?>/src/artistas/perfil/index.php" style="font-family:var(--ff-m);font-size:.78rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#3d2b10;text-decoration:none">← Mi perfil</a>
    <h1 class="page-h1" style="margin-bottom:8px;color:#0d0902">Editar <em>perfil</em></h1>
    <p class="page-lead" style="margin-bottom:32px;font-size:clamp(1.05rem,1.5vw,1.2rem);font-weight:400;color:#1A1208">Actualiza tu información artística y datos de contacto.</p>

    <?php if ($ok): ?><div class="alert alert-ok" style="margin-bottom:20px">✅ <?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-err" style="margin-bottom:20px">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if (isset($artista)): ?>
    <div class="form-card" style="max-width:100%">
      <div style="display:flex;gap:0;border-bottom:1px solid var(--cream-dk);margin-bottom:28px">
        <button class="tab-btn active" data-tab="perfil" style="padding:10px 20px;font-family:var(--ff-m);font-size:.78rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;border:none;background:none;cursor:pointer;border-bottom:2px solid var(--gold);color:var(--gold)">Perfil artístico</button>
        <button class="tab-btn" data-tab="cuenta" style="padding:10px 20px;font-family:var(--ff-m);font-size:.78rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;color:rgba(26,18,8,.55)">Mi cuenta</button>
      </div>
      <div id="tab-perfil">
        <form method="POST" action="guardar.php">
          <input type="hidden" name="tipo" value="perfil"/>
          <div class="form-grid-2">
            <div class="field">
              <label class="field-label">Nombre artístico *</label>
              <input class="field-input" type="text" name="nombre" required value="<?= htmlspecialchars($artista['nombre']) ?>" maxlength="180"/>
            </div>
            <div class="field">
              <label class="field-label">Disciplina *</label>
              <select class="field-select" name="disciplina" required>
                <?php foreach ($disciplinas as $d): ?>
                  <option value="<?= htmlspecialchars($d) ?>" <?= $artista['disciplina'] === $d ? 'selected' : '' ?>><?= htmlspecialchars($d) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="field">
            <label class="field-label">Biografía</label>
            <textarea class="field-textarea" name="bio" rows="4"><?= htmlspecialchars($artista['bio'] ?? '') ?></textarea>
          </div>
          <div class="form-grid-2">
            <div class="field">
              <label class="field-label">Municipio</label>
              <input class="field-input" type="text" name="municipio" value="<?= htmlspecialchars($artista['municipio'] ?? '') ?>" maxlength="100"/>
            </div>
            <div class="field">
              <label class="field-label">Foto de perfil (URL)</label>
              <input class="field-input" type="url" name="foto_url" value="<?= htmlspecialchars($artista['foto_url'] ?? '') ?>"/>
            </div>
          </div>
          <div class="form-grid-2">
            <div class="field">
              <label class="field-label">Instagram</label>
              <input class="field-input" type="text" name="instagram" value="<?= htmlspecialchars($artista['instagram'] ?? '') ?>" placeholder="@usuario"/>
            </div>
            <div class="field">
              <label class="field-label">Sitio web / Facebook</label>
              <input class="field-input" type="text" name="website" value="<?= htmlspecialchars($artista['website'] ?? '') ?>"/>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-gold">Guardar cambios</button>
            <a href="<?= $base ?>/src/artistas/perfil/index.php" class="btn btn-outline">Cancelar</a>
          </div>
        </form>
      </div>
      <div id="tab-cuenta" style="display:none">
        <form method="POST" action="guardar.php" id="artistaCuentaForm">
          <input type="hidden" name="tipo" value="cuenta"/>
          <div class="field">
            <label class="field-label">Nombre completo</label>
            <input class="field-input" type="text" name="nombre_usuario" value="<?= htmlspecialchars($uData['nombre'] ?? '') ?>"/>
          </div>
          <div class="field">
            <label class="field-label">Bio personal</label>
            <textarea class="field-textarea" name="bio_usuario" rows="3"><?= htmlspecialchars($uData['bio'] ?? '') ?></textarea>
          </div>
          <div class="field">
            <label class="field-label">Teléfono</label>
            <input class="field-input" type="tel" name="telefono" value="<?= htmlspecialchars($uData['telefono'] ?? '') ?>"/>
          </div>
          <div class="field">
            <label class="field-label">Nueva contraseña <span style="font-weight:300;text-transform:none">(dejar vacío para no cambiar)</span></label>
            <input class="field-input" type="password" id="artista-password" name="password" placeholder="Mínimo 8 caracteres" autocomplete="new-password"/>
            <div class="strength-bar"><div class="strength-fill" id="artista-strengthFill"></div></div>
            <span class="strength-label" id="artista-strengthLabel"></span>
            <span class="input-error-msg" id="artista-passErr"></span>
          </div>
          <div class="field">
            <label class="field-label">Confirmar nueva contraseña</label>
            <input class="field-input" type="password" id="artista-confirm" name="confirm" placeholder="Repite la contraseña" autocomplete="new-password"/>
            <span class="input-error-msg" id="artista-confirmErr"></span>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-gold" id="artista-saveBtn">Guardar cuenta</button>
          </div>
        </form>
      </div>

    </div>
    <?php endif; ?>
  </div>
</main>
<script src="<?= $base ?>/src/artistas/editar/editar.js"></script>
</body>
</html>