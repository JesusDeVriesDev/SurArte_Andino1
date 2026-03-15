<?php
$pageTitle  = 'Artistas';
$pageId     = 'artistas';
$flashWarn  = $_SESSION['_flash_warn'] ?? null;
unset($_SESSION['_flash_warn']);
require_once '../_layout/head.php';
require_once '../../config/db.php';

try {
    $artistas = db()->query(
        "SELECT id, nombre, disciplina, municipio, bio, foto_url
         FROM artistas
         WHERE verificado = TRUE
         ORDER BY creado_en DESC"
    )->fetchAll();

    $disciplinas = db()->query(
        "SELECT DISTINCT disciplina FROM artistas WHERE verificado = TRUE AND disciplina IS NOT NULL ORDER BY disciplina"
    )->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $artistas = $disciplinas = [];
    $dbError = $e->getMessage();
}

$catIcons = ['musica'=>'🎵','arte'=>'🎨','artesania'=>'🧵','danza'=>'💃','literatura'=>'📖',
             'pintura'=>'🖼️','escultura'=>'🗿','fotografia'=>'📷','teatro'=>'🎭','otro'=>'✨',
             'Barniz de Pasto'=>'🎨','Cerámica Contemporánea'=>'🏺','Música Andina'=>'🎵'];
$emojis = ['🎨','🏺','🪇','🧵','🖼️','💃','📷','🎭','🌺','🦜','🌿','✨'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/artistas/artistas.css"/>
</head>
<main>

  <?php if ($flashWarn ?? null): ?>
  <div class="alert alert-info" style="margin-bottom:20px;display:flex;align-items:center;gap:10px">
    ⏳ <?= htmlspecialchars($flashWarn) ?>
  </div>
  <?php endif; ?>

  <?php if (isset($dbError)): ?>
  <div class="alert alert-err" style="margin-bottom:28px">
    ⚠️ Error de base de datos: <code><?= htmlspecialchars($dbError) ?></code>
  </div>
  <?php endif; ?>

  <div class="art-hero">
    <div class="art-hero-bg"></div>
    <div>
      <div class="eyebrow">Comunidad creativa</div>
      <h1 class="page-h1">Artistas <em>verificados</em></h1>
      <p class="page-lead">Descubre los creadores del sur de Colombia. Cada perfil ha sido revisado y verificado por nuestro equipo.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;padding-top:52px">
      <?php if ($user && $user['rol'] === 'artista'): ?>
        <a href="<?= $base ?>/src/artistas/perfil/index.php" class="btn btn-gold" style="font-size:.7rem">Mi perfil →</a>
      <?php elseif (!$user || ($user['rol'] !== 'artista' && $user['rol'] !== 'admin')): ?>
        <a href="<?= $base ?>/src/artistas/registro/index.php" class="btn btn-gold" style="font-size:.7rem">Registrarme como artista →</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($disciplinas)): ?>
  <div class="art-filters">
    <button class="filter-pill active" data-disc="all">🎭 Todos (<?= count($artistas) ?>)</button>
    <?php foreach ($disciplinas as $d): ?>
    <button class="filter-pill" data-disc="<?= htmlspecialchars($d) ?>">
      <?= $catIcons[$d] ?? '✨' ?> <?= htmlspecialchars($d) ?>
    </button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($artistas)): ?>
  <div class="art-grid" id="artistasGrid">
    <?php foreach ($artistas as $i => $a): ?>
    <a href="<?= $base ?>/src/artistas/perfil/index.php?id=<?= urlencode($a['id']) ?>" class="art-card" data-disc="<?= htmlspecialchars($a['disciplina'] ?? '') ?>">
      <div class="art-card-img">
        <?php if (!empty($a['foto_url'])): ?>
          <img src="<?= htmlspecialchars($a['foto_url']) ?>" alt="<?= htmlspecialchars($a['nombre']) ?>" loading="lazy"/>
        <?php else: ?>
          <div class="art-card-placeholder"><?= $emojis[$i % count($emojis)] ?></div>
        <?php endif; ?>
        <div class="art-card-verif" title="Artista verificado">✓</div>
      </div>
      <div class="art-card-body">
        <div class="art-card-name"><?= htmlspecialchars($a['nombre']) ?></div>
        <?php if ($a['disciplina']): ?>
          <span class="badge badge-sky" style="margin-bottom:6px;display:inline-block"><?= htmlspecialchars($a['disciplina']) ?></span>
        <?php endif; ?>
        <?php if ($a['municipio']): ?>
          <div class="art-card-loc">📍 <?= htmlspecialchars($a['municipio']) ?></div>
        <?php endif; ?>
        <?php if ($a['bio']): ?>
          <div class="art-card-bio"><?= htmlspecialchars(mb_substr($a['bio'], 0, 80)) ?>…</div>
        <?php endif; ?>
        <div class="art-card-cta">Ver perfil →</div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <?php else: ?>
  <div class="empty" style="margin-top:60px">
    <div class="empty-icon">🎨</div>
    <p>Aún no hay artistas verificados. ¡Sé el primero!</p>
    <?php if (!$user): ?>
    <a href="<?= $base ?>/src/artistas/registro/index.php" class="btn btn-gold" style="margin-top:16px">Registrarme como artista →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if (!$user || ($user['rol'] !== 'artista' && $user['rol'] !== 'admin')): ?>
  <div class="art-cta-banner">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse 50% 70% at 100% 50%,rgba(139,58,28,.3) 0%,transparent 65%);pointer-events:none"></div>
    <div style="position:relative">
      <h2 style="font-family:var(--ff-d);font-size:clamp(1.8rem,3.5vw,3rem);font-weight:900;color:#fff;line-height:1.1">
        ¿Eres artista<br>de <em style="font-style:italic;color:var(--gold)">Nariño?</em>
      </h2>
      <p style="font-size:1rem;font-weight:300;color:rgba(250,245,236,.45);margin-top:10px;max-width:360px">
        Crea tu perfil gratuito, sube tu portafolio y vende tu arte directamente.
      </p>
    </div>
    <div style="position:relative;display:flex;gap:10px;flex-wrap:wrap">
      <a href="<?= $base ?>/src/artistas/registro/index.php" class="btn btn-gold" style="font-size:.72rem">Registrarme ahora →</a>
    </div>
  </div>
  <?php endif; ?>

</main>
<script src="<?= $base ?>/src/artistas/artistas.js"></script>
</body>
</html>
