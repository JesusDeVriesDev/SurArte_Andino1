<?php
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

    $categorias = db()->query(
        "SELECT DISTINCT categoria FROM eventos
         WHERE activo = TRUE AND fecha_inicio >= NOW() AND categoria IS NOT NULL
         ORDER BY categoria"
    )->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $eventos = $categorias = [];
    $dbError = $e->getMessage();
}

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
      <div class="eyebrow">Agenda cultural</div>
      <h1 class="page-h1">Próximos <em>eventos</em></h1>
      <p class="page-lead">Descubre música, arte, artesanía y más en el sur de Colombia.</p>
    </div>
  </div>

  <?php if (!empty($categorias)): ?>
  <div class="art-filters">
    <button class="filter-pill active" data-cat="all">🎭 Todos (<?= count($eventos) ?>)</button>
    <?php foreach ($categorias as $cat):
      $n = count(array_filter($eventos, fn($e) => $e['categoria'] === $cat));
    ?>
    <button class="filter-pill" data-cat="<?= htmlspecialchars($cat) ?>">
      <?= $catIcons[$cat] ?? '✨' ?> <?= $catLabels[$cat] ?? ucfirst($cat) ?> (<?= $n ?>)
    </button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

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

          <!-- Fecha + título -->
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
            <div style="min-width:48px;background:var(--gold);border-radius:10px;padding:9px 7px;text-align:center;flex-shrink:0">
              <div style="font-family:var(--ff-d);font-size:1.35rem;font-weight:900;color:var(--ink);line-height:1"><?= $dt->format('d') ?></div>
              <div style="font-family:var(--ff-m);font-size:.48rem;letter-spacing:.09em;text-transform:uppercase;color:rgba(26,18,8,.55)"><?= $dt->format('M') ?></div>
            </div>
            <div>
              <h3 style="font-family:var(--ff-d);font-size:.95rem;font-weight:700;color:var(--ink)"><?= htmlspecialchars($ev['titulo']) ?></h3>
              <?php if ($ev['lugar'] || $ev['municipio']): ?>
                <div style="font-size:.8rem;font-weight:300;color:rgba(26,18,8,.42);margin-top:2px">
                  📍 <?= htmlspecialchars(trim(($ev['lugar'] ?? '') . ($ev['lugar'] && $ev['municipio'] ? ' · ' : '') . ($ev['municipio'] ?? ''))) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Descripción -->
          <?php if (!empty($ev['descripcion'])): ?>
            <div style="font-size:.8rem;color:rgba(26,18,8,.55);line-height:1.5;margin-bottom:8px">
              <?= htmlspecialchars(mb_strimwidth($ev['descripcion'], 0, 120, '…')) ?>
            </div>
          <?php endif; ?>

          <!-- Horario -->
          <div style="font-size:.78rem;color:rgba(26,18,8,.45);margin-bottom:4px">
            🕐 <?= $dt->format('d/m/Y H:i') ?>
            <?php if ($dtFin): ?>
              → <?= $dtFin->format('d/m/Y H:i') ?>
            <?php endif; ?>
          </div>

          <!-- Aforo -->
          <?php if ($ev['aforo']): ?>
            <div style="font-size:.78rem;color:rgba(26,18,8,.45);margin-bottom:8px">
              👥 Aforo: <?= number_format((int)$ev['aforo']) ?> personas
            </div>
          <?php endif; ?>

          <!-- Categoría + precio -->
          <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px">
            <span class="badge badge-clay"><?= $ic ?> <?= $catLabels[$ev['categoria']] ?? ucfirst($ev['categoria'] ?? 'evento') ?></span>
            <?php if ($ev['precio'] > 0): ?>
              <span style="font-family:var(--ff-d);font-size:.95rem;font-weight:700;color:var(--clay)">
                $<?= number_format((float)$ev['precio'], 0, ',', '.') ?>
              </span>
            <?php else: ?>
              <span class="badge badge-green">Gratis</span>
            <?php endif; ?>
          </div>

        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php else: ?>
  <div class="empty" style="margin-top:60px">
    <div class="empty-icon">📅</div>
    <p>No hay eventos próximos registrados.</p>
  </div>
  <?php endif; ?>

</main>

<script>
document.querySelectorAll('.filter-pill').forEach(pill => {
  pill.addEventListener('click', () => {
    document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
    pill.classList.add('active');
    const cat = pill.dataset.cat;
    document.querySelectorAll('.ev-item').forEach(card => {
      card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
    });
  });
});
</script>
</body>
</html>