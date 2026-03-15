<?php
$pageTitle = 'Eventos';
$pageId    = 'eventos';
require_once '../_layout/head.php';
require_once '../../config/db.php';

try {
    $eventosDestacados = db()->query(
        "SELECT titulo, categoria, lugar, municipio, fecha_inicio, precio
         FROM eventos WHERE activo=true AND fecha_inicio >= NOW()
         ORDER BY fecha_inicio ASC LIMIT 3"
    )->fetchAll();
} catch (PDOException $e) {
    $nArtistas = $nEventos = $nProductos = 0;
    $eventosDestacados = $artistasDestacados = [];
    $dbError = $e->getMessage();
}

$catIcons = ['musica'=>'🎵','arte'=>'🎨','artesania'=>'🧵','danza'=>'💃','literatura'=>'📖','otro'=>'✨'];
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
      ⚠️ No se pudo conectar a la base de datos: <code style="font-family:var(--ff-m);font-size:.85em"><?= htmlspecialchars($dbError) ?></code><br>
      <span style="font-size:.85em">Verifica las credenciales en <code>config/db.php</code></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($eventosDestacados)): ?>
    <section style="margin-top:80px">
      <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:22px">
        <div>
          <div class="eyebrow">Agenda cultural</div>
          <h2 style="font-family:var(--ff-d);font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:900;color:var(--ink)">
            Próximos <em style="font-style:italic;color:var(--clay)">eventos</em>
          </h2>
        </div>
      </div>
      <div class="grid-3">
        <?php foreach ($eventosDestacados as $ev):
          $dt  = new DateTime($ev['fecha_inicio']);
          $ic  = $catIcons[$ev['categoria']] ?? '✨';
        ?>
          <div class="card" style="background:#fff">
            <div class="card-body">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
                <div style="min-width:48px;background:var(--gold);border-radius:10px;padding:9px 7px;text-align:center">
                  <div style="font-family:var(--ff-d);font-size:1.35rem;font-weight:900;color:var(--ink);line-height:1"><?= $dt->format('d') ?></div>
                  <div style="font-family:var(--ff-m);font-size:.48rem;letter-spacing:.09em;text-transform:uppercase;color:rgba(26,18,8,.55)"><?= $dt->format('M') ?></div>
                </div>
                <div>
                  <h3 style="font-family:var(--ff-d);font-size:.95rem;font-weight:700;color:var(--ink)"><?= htmlspecialchars($ev['titulo']) ?></h3>
                  <?php if ($ev['lugar']): ?>
                    <div style="font-size:.8rem;font-weight:300;color:rgba(26,18,8,.42);margin-top:2px">📍 <?= htmlspecialchars($ev['lugar']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between">
                <span class="badge badge-clay"><?= $ic ?> <?= ucfirst($ev['categoria'] ?? 'evento') ?></span>
                <?php if ($ev['precio'] > 0): ?>
                  <span style="font-family:var(--ff-d);font-size:.95rem;font-weight:700;color:var(--clay)">$<?= number_format($ev['precio'],0,',','.') ?></span>
                <?php else: ?>
                  <span class="badge badge-green">Gratis</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</main>
</body>
</html>
