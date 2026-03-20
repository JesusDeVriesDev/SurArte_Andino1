<?php
ob_start();
$pageTitle = 'Registro de Artista';
$pageId    = 'artistas';
require_once '../../_layout/head.php';
require_once '../../../config/db.php';

// Redirigir si ya es artista
if ($user && $user['rol'] === 'artista') {
    header('Location: ' . $base . '/src/artistas/perfil/index.php');
    exit;
}
if (!$user) {
    header('Location: ' . $base . '/src/auth/login/index.php');
    exit;
}

$error = $_SESSION['reg_artista_error'] ?? null;
$ok    = $_SESSION['reg_artista_ok']    ?? null;
unset($_SESSION['reg_artista_error'], $_SESSION['reg_artista_ok']);

$disciplinas = ['Barniz de Pasto','Cerámica','Pintura','Escultura','Música Andina','Danza','Literatura',
                'Fotografía','Teatro','Artesanía','Tejido','Orfebrería','Otro'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/artistas/artistas.css"/>
  <style>
    .field-label{font-size:.82rem!important;font-weight:700!important;color:#1A1208!important;opacity:1!important}
    .field-input,.field-select,.field-textarea{font-size:1.05rem!important;font-weight:400!important;color:#0d0902!important;background:#FFFEF9!important;border:1.5px solid #EDE4D0!important}
    .field-input::placeholder,.field-textarea::placeholder{color:rgba(26,18,8,.55)!important}
    .field-input:focus,.field-select:focus,.field-textarea:focus{border-color:var(--gold)!important;box-shadow:0 0 0 3px rgba(201,146,42,.1)!important;outline:none!important}
  </style>
</head>
<main>
  <div style="padding-top:48px;max-width:640px;margin:0 auto">
    <a href="<?= $base ?>/src/artistas/artistas.php" style="font-family:var(--ff-m);font-size:.78rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#3d2b10;text-decoration:none">← Artistas</a>
    <div class="eyebrow" style="margin-top:16px;font-size:.75rem;font-weight:700;color:#5a2d0c">Comunidad creativa</div>
    <h1 class="page-h1" style="margin-bottom:8px;color:#0d0902">Registro de <em>artista</em></h1>
    <p class="page-lead" style="margin-bottom:32px;font-size:clamp(1.05rem,1.5vw,1.2rem);font-weight:400;color:#1A1208">Completa tu perfil artístico. Nuestro equipo lo revisará y verificará pronto.</p>

    <?php if ($error): ?>
    <div class="alert alert-err" style="margin-bottom:20px">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($ok): ?>
    <div class="alert alert-ok" style="margin-bottom:20px">✅ <?= htmlspecialchars($ok) ?></div>
    <?php endif; ?>

    <div class="form-card">
      <form method="POST" action="registro.php" enctype="multipart/form-data">

        <div class="form-grid-2">
          <div class="field">
            <label class="field-label">Nombre artístico *</label>
            <input class="field-input" type="text" name="nombre" required placeholder="Tu nombre como artista" maxlength="180"/>
          </div>
          <div class="field">
            <label class="field-label">Disciplina *</label>
            <select class="field-select" name="disciplina" required>
              <option value="">Selecciona tu disciplina</option>
              <?php foreach ($disciplinas as $d): ?>
                <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="field">
          <label class="field-label">Biografía *</label>
          <textarea class="field-textarea" name="bio" required placeholder="Cuéntanos sobre tu trabajo, técnicas, inspiraciones…" rows="4"></textarea>
        </div>

        <div class="form-grid-2">
          <div class="field">
            <label class="field-label">Municipio</label>
            <input class="field-input" type="text" name="municipio" placeholder="Ej. Pasto, Ipiales…" maxlength="100"/>
          </div>
          <div class="field">
            <label class="field-label">Foto de perfil (URL)</label>
            <input class="field-input" type="url" name="foto_url" placeholder="https://…"/>
          </div>
        </div>

        <div class="form-grid-2">
          <div class="field">
            <label class="field-label">Instagram</label>
            <input class="field-input" type="text" name="instagram" placeholder="@usuario" maxlength="120"/>
          </div>
          <div class="field">
            <label class="field-label">Facebook / Sitio web</label>
            <input class="field-input" type="text" name="website" placeholder="https://…"/>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-gold">Enviar solicitud →</button>
          <a href="<?= $base ?>/src/artistas/artistas.php" class="btn btn-outline">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</main>
</body>
</html>
