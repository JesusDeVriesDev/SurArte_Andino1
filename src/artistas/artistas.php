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
      <h1 class="page-h1" style="font-size:clamp(2.8rem,6vw,5rem);color:#0d0902">Artistas <em>verificados</em></h1>
      <p class="page-lead" style="font-size:clamp(1.05rem,1.6vw,1.25rem);font-weight:400;color:#1A1208;max-width:520px">Descubre los creadores del sur de Colombia. Cada perfil ha sido revisado y verificado por nuestro equipo.</p>
    <div style="display:flex;gap:10px;flex-wrap:wrap;padding-top:52px">
      <?php if ($user && $user['rol'] === 'artista'): ?>
        <a href="<?= $base ?>/src/artistas/perfil/index.php" class="btn btn-gold" style="font-size:.7rem">Mi perfil →</a>
      <?php elseif (!$user || ($user['rol'] !== 'artista' && $user['rol'] !== 'admin')): ?>
        <a href="<?= $base ?>/src/artistas/registro/index.php" class="btn btn-gold" style="font-size:.7rem">Registrarme como artista →</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="art-filtros" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:32px;width:100%;box-sizing:border-box">
    <input
      id="artistaSearch"
      type="text"
      placeholder="Buscar por nombre, disciplina o municipio…"
      style="flex:1;min-width:0;width:100%;background:#FFFEF9;border:1.5px solid #EDE4D0;border-radius:8px;padding:10px 16px;font-family:'Cormorant Garamond',Georgia,serif;font-size:1rem;font-weight:600;color:#000000;transition:border-color .22s,box-shadow .22s;outline:none;box-shadow:none;box-sizing:border-box"
      onfocus="this.style.borderColor='rgb(201, 146, 42)';this.style.boxShadow='0 0 0 3px rgba(201,146,42,.1)'"
      onblur="this.style.borderColor='#EDE4D0';this.style.boxShadow='none'"
    />
    <div style="position:relative;min-width:0;width:100%;max-width:260px;flex-shrink:1;box-sizing:border-box">
      <select
        id="disciplinaSelect"
        style="-webkit-appearance:none;appearance:none;padding:10px 36px 10px 16px;border:1.5px solid #EDE4D0;border-radius:8px;background:#FFFEF9;font-family:'Cormorant Garamond',Georgia,serif;font-size:1rem;font-weight:600;color:#1A1208;cursor:pointer;outline:none;transition:border-color .22s,box-shadow .22s;width:100%;box-shadow:none;box-sizing:border-box"
        onfocus="this.style.borderColor='rgb(201, 146, 42)';this.style.boxShadow='0 0 0 3px rgba(201,146,42,.1)'"
        onblur="this.style.borderColor='#EDE4D0';this.style.boxShadow='none'"
      >
        <option value="all">Todas las disciplinas (<?= count($artistas) ?>)</option>
        <?php foreach ($disciplinas as $d): ?>
          <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
        <?php endforeach; ?>
      </select>
      <span style="position:absolute;right:13px;top:50%;transform:translateY(-50%);font-size:.75rem;color:rgba(26,18,8,.4);pointer-events:none">▾</span>
    </div>
  </div>

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
        <div class="art-card-name" style="font-size:1.15rem;color:#0d0902;font-weight:800"><?= htmlspecialchars($a['nombre']) ?></div>
        <?php if ($a['disciplina']): ?>
          <span class="badge badge-sky" style="margin-bottom:6px;display:inline-block;font-size:.65rem"><?= htmlspecialchars($a['disciplina']) ?></span>
        <?php endif; ?>
        <?php if ($a['municipio']): ?>
          <div class="art-card-loc" style="font-size:.65rem;color:#000000;font-weight:600">📍 <?= htmlspecialchars($a['municipio']) ?></div>
        <?php endif; ?>
        <?php if ($a['bio']): ?>
          <div class="art-card-bio" style="font-size:.92rem;font-weight:400;color:#000000"><?= htmlspecialchars(mb_substr($a['bio'], 0, 80)) ?>…</div>
        <?php endif; ?>
        <div class="art-card-cta" style="font-weight:1000">Ver perfil →</div>
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
<script>
(function () {
  var searchInput = document.getElementById('artistaSearch');
  var selectDisc  = document.getElementById('disciplinaSelect');
  var cards       = document.querySelectorAll('.art-card');
  var grid        = document.getElementById('artistasGrid');

  if (!searchInput && !selectDisc) return;

  function normalize(str) {
    return (str || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  function filterCards() {
    var q    = normalize(searchInput ? searchInput.value : '');
    var disc = selectDisc ? selectDisc.value : 'all';
    var visible = 0;

    cards.forEach(function(card) {
      var txt      = normalize(card.textContent);
      var cardDisc = card.getAttribute('data-disc') || '';
      var matchQ   = !q || txt.indexOf(q) !== -1;
      var matchD   = disc === 'all' || cardDisc === disc;
      card.style.display = (matchQ && matchD) ? '' : 'none';
      if (matchQ && matchD) visible++;
    });

    var noRes = grid ? grid.querySelector('.art-no-results') : null;
    if (!noRes && grid) {
      noRes = document.createElement('div');
      noRes.className = 'art-no-results';
      noRes.style.cssText = 'display:none;grid-column:1/-1;text-align:center;padding:60px 0;color:rgba(26,18,8,.38);font-size:.95rem';
      noRes.textContent = 'No se encontraron artistas con ese criterio.';
      grid.appendChild(noRes);
    }
    if (noRes) noRes.style.display = visible === 0 ? 'block' : 'none';
  }

  if (searchInput) searchInput.addEventListener('input',  filterCards);
  if (selectDisc)  selectDisc.addEventListener('change', filterCards);
}());
</script>
</body>
</html>