<?php
// Solo eventos próximos y activos — los pasados no aparecen en el listado público
$pageTitle = 'Eventos';
$pageId    = 'eventos';
require_once '../_layout/head.php';
require_once '../../config/db.php';

try {
    $eventos = db()->query(
        "SELECT id, titulo, descripcion, categoria, lugar, municipio,
                fecha_inicio, fecha_fin, precio, aforo, imagen_url
         FROM eventos
         WHERE activo = TRUE AND fecha_inicio >= NOW()
         ORDER BY fecha_inicio ASC"
    )->fetchAll();

    // Categorías únicas presentes en los eventos próximos para los filtros de la vista
    $categorias = db()->query(
        "SELECT DISTINCT categoria FROM eventos
         WHERE activo = TRUE AND fecha_inicio >= NOW() AND categoria IS NOT NULL
         ORDER BY categoria"
    )->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $eventos = $categorias = [];
    $dbError = $e->getMessage();
}

// Íconos y etiquetas por categoría para las tarjetas y los filtros
$catIcons  = ['musica'=>'🎵','arte'=>'🎨','artesania'=>'🧵','danza'=>'💃','literatura'=>'📖','otro'=>'✨'];
$catLabels = ['musica'=>'Música','arte'=>'Arte','artesania'=>'Artesanía','danza'=>'Danza','literatura'=>'Literatura','otro'=>'Otro'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/_layout/global.css"/>
</head>
<main>

  <?php if (isset($dbError)): ?>
    <div class="alert alert-err" style="margin-bottom:28px">
      ⚠️ Error de base de datos: <code style="font-family:var(--ff-m);font-size:.85em"><?= htmlspecialchars($dbError) ?></code>
    </div>
  <?php endif; ?>

  <div class="art-hero" style="margin-bottom:0">
    <div class="art-hero-bg"></div>
    <div>
      <h1 class="page-h1" style="font-size:clamp(2.8rem,6vw,5rem);color:#0d0902">Próximos <em>eventos</em></h1>
      <p class="page-lead" style="font-size:clamp(1.05rem,1.6vw,1.25rem);font-weight:400;color:#1A1208">Descubre música, arte, artesanía y más en el sur de Colombia.</p>
    </div>
  </div>

  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:28px 0 32px">
    <input
      id="eventoSearch"
      type="text"
      placeholder="Buscar eventos por título, lugar o municipio…"
      style="flex:1;min-width:200px;background:#FFFEF9;border:1.5px solid #EDE4D0;border-radius:8px;padding:10px 16px;font-family:'Cormorant Garamond',Georgia,serif;font-size:1.05rem;font-weight:700;color:#1A1208;transition:border-color .22s,box-shadow .22s;outline:none;box-shadow:none"
      onfocus="this.style.borderColor='#C9922A';this.style.boxShadow='0 0 0 3px rgba(201,146,42,.1)'"
      onblur="this.style.borderColor='#EDE4D0';this.style.boxShadow='none'"
    />
    <div style="position:relative;flex-shrink:0">
      <select
        id="categoriaSelect"
        style="-webkit-appearance:none;appearance:none;padding:10px 36px 10px 16px;border:1.5px solid #EDE4D0;border-radius:8px;background:#FFFEF9;font-family:'Cormorant Garamond',Georgia,serif;font-size:1.05rem;font-weight:700;color:#1A1208;cursor:pointer;outline:none;transition:border-color .22s,box-shadow .22s;min-width:220px;box-shadow:none"
        onfocus="this.style.borderColor='#C9922A';this.style.boxShadow='0 0 0 3px rgba(201,146,42,.1)'"
        onblur="this.style.borderColor='#EDE4D0';this.style.boxShadow='none'"
      >
        <option value="all">🎭 Todas las categorías (<?= count($eventos) ?>)</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= htmlspecialchars($cat) ?>"><?= ($catIcons[$cat] ?? '✨') . ' ' . ($catLabels[$cat] ?? ucfirst($cat)) ?></option>
        <?php endforeach; ?>
      </select>
      <span style="position:absolute;right:13px;top:50%;transform:translateY(-50%);font-size:.75rem;color:rgba(26,18,8,.4);pointer-events:none">▾</span>
    </div>
  </div>

  <?php if (!empty($eventos)): ?>
  <section style="margin-top:32px">
    <div class="grid-3">
      <?php foreach ($eventos as $ev):
        $dt     = new DateTime($ev['fecha_inicio']);
        $dtFin  = $ev['fecha_fin'] ? new DateTime($ev['fecha_fin']) : null;
        $ic     = $catIcons[$ev['categoria']] ?? '✨';
      ?>
      <div class="card ev-item" data-cat="<?= htmlspecialchars($ev['categoria'] ?? '') ?>" style="background:#fff">

        <?php if ($ev['imagen_url']): ?>
          <div style="width:100%;height:140px;overflow:hidden;border-radius:12px 12px 0 0">
            <img src="<?= htmlspecialchars($ev['imagen_url']) ?>"
                 alt="<?= htmlspecialchars($ev['titulo']) ?>"
                 style="width:100%;height:100%;object-fit:cover" loading="lazy"/>
          </div>
        <?php endif; ?>

        <div class="card-body">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
            <div style="min-width:48px;background:var(--gold);border-radius:10px;padding:9px 7px;text-align:center;flex-shrink:0">
              <div style="font-family:var(--ff-d);font-size:1.35rem;font-weight:900;color:var(--ink);line-height:1"><?= $dt->format('d') ?></div>
              <div style="font-family:var(--ff-m);font-size:.48rem;letter-spacing:.09em;text-transform:uppercase;color:rgba(26,18,8,.55)"><?= $dt->format('M') ?></div>
            </div>
            <div>
              <h3 style="font-family:var(--ff-d);font-size:1.1rem;font-weight:800;color:#0d0902"><?= htmlspecialchars($ev['titulo']) ?></h3>
              <?php if ($ev['lugar'] || $ev['municipio']): ?>
                <div style="font-size:.95rem;font-weight:500;color:#3d2b10;margin-top:2px">
                  📍 <?= htmlspecialchars(trim(($ev['lugar'] ?? '') . ($ev['lugar'] && $ev['municipio'] ? ' · ' : '') . ($ev['municipio'] ?? ''))) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
          <?php if (!empty($ev['descripcion'])): ?>
            <div style="font-size:.95rem;font-weight:500;color:#2a1e0e;line-height:1.55;margin-bottom:8px">
              <?= htmlspecialchars(mb_strimwidth($ev['descripcion'], 0, 120, '…')) ?>
            </div>
          <?php endif; ?>

          <div style="font-size:.95rem;font-weight:500;color:#3d2b10;margin-bottom:4px">
            🕐 <?= $dt->format('d/m/Y H:i') ?>
            <?php if ($dtFin): ?>
              → <?= $dtFin->format('d/m/Y H:i') ?>
            <?php endif; ?>
          </div>

          <?php if ($ev['aforo']): ?>
            <div style="font-size:.95rem;font-weight:500;color:#3d2b10;margin-bottom:8px">
              👥 Aforo: <?= number_format((int)$ev['aforo']) ?> personas
            </div>
          <?php endif; ?>
          <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px">
            <span class="badge badge-clay" style="font-size:.7rem;font-weight:500"><?= $ic ?> <?= $catLabels[$ev['categoria']] ?? ucfirst($ev['categoria'] ?? 'evento') ?></span>
            <?php if ($ev['precio'] > 0): ?>
              <span style="font-family:var(--ff-d);font-size:.9rem;font-weight:700;color:var(--clay)">
                $<?= number_format((float)$ev['precio'], 0, ',', '.') ?>
              </span>
            <?php else: ?>
              <span class="badge badge-green" style="font-size:.9rem;font-weight:500">Gratis</span>
            <?php endif; ?>
          </div>

        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php else: ?>
  <div class="empty" style="margin-top:60px;color:rgb(0, 0, 0)">
    <div class="empty-icon">📅</div>
    <p style="font-size:1.7rem">No hay eventos próximos registrados.</p>
  </div>
  <?php endif; ?>

</main>

<script>
(function() {
  var searchInput = document.getElementById('eventoSearch');
  var selectCat   = document.getElementById('categoriaSelect');
  var cards       = document.querySelectorAll('.ev-item');

  function normalize(str) {
    return (str || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  function filterEventos() {
    var q   = normalize(searchInput ? searchInput.value : '');
    var cat = selectCat ? selectCat.value : 'all';
    var visible = 0;
    cards.forEach(function(card) {
      var txt     = normalize(card.textContent);
      var cardCat = card.getAttribute('data-cat') || '';
      var matchQ  = !q || txt.indexOf(q) !== -1;
      var matchC  = cat === 'all' || cardCat === cat;
      card.style.display = (matchQ && matchC) ? '' : 'none';
      if (matchQ && matchC) visible++;
    });
  }

  if (searchInput) searchInput.addEventListener('input',  filterEventos);
  if (selectCat)   selectCat.addEventListener('change', filterEventos);
}());
</script>
</body>
</html>