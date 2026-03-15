<?php
$pageTitle = 'Inicio';
$pageId    = 'inicio';
require_once '../_layout/head.php';
require_once '../../config/db.php';
try {
    $nArtistas  = db()->query("SELECT COUNT(*) FROM artistas")->fetchColumn();
    $nEventos   = db()->query("SELECT COUNT(*) FROM eventos WHERE activo=true AND fecha_inicio >= NOW()")->fetchColumn();
    $nProductos = db()->query("SELECT COUNT(*) FROM productos WHERE activo=true")->fetchColumn();
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

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;min-height:calc(100vh - 200px);padding-top:20px">
    <div>
      <div style="display:inline-flex;align-items:center;gap:7px;background:rgba(201,146,42,.08);border:1px solid rgba(201,146,42,.18);border-radius:999px;padding:5px 14px;font-family:var(--ff-m);font-size:.6rem;letter-spacing:.16em;text-transform:uppercase;color:var(--gold);margin-bottom:28px">
        <span style="width:5px;height:5px;background:var(--ok);border-radius:50%;box-shadow:0 0 5px var(--ok);animation:blink 1.8s ease-in-out infinite"></span>
        Nariño, Colombia
      </div>
      <h1 style="font-family:var(--ff-d);font-size:clamp(3rem,7vw,5.8rem);font-weight:900;line-height:.9;letter-spacing:-.04em;color:var(--ink);margin-bottom:24px">
        Sur<br>Arte<br><em style="font-style:italic;color:var(--clay)">Andino</em>
      </h1>
      <p style="font-size:clamp(.95rem,1.4vw,1.15rem);font-weight:300;color:rgb(0, 0, 0);max-width:440px;line-height:1.82;margin-bottom:36px">
        La plataforma que conecta artistas, artesanos y amantes de la cultura de Nariño con el mundo. Eventos en tiempo real, marketplace de arte y comunidad colaborativa.
      </p>
      <div style="display:flex;flex-wrap:wrap;gap:11px;">
        <a style= "color:rgb(255, 255, 255)" href="../artistas/artistas.php" class="btn btn-gold">Explorar artistas →</a>
        <a href="../eventos/eventos.php"  class="btn btn-outline">Ver eventos</a>
        <?php if (!$user): ?>
          <a href="../auth/register/index.php" class="btn btn-outline">Únete gratis</a>
        <?php endif; ?>
      </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:14px">
      <?php foreach ([
        ['🎨', $nArtistas,  'Artistas registrados'],
        ['📅', $nEventos,   'Eventos próximos'],
        ['🛍️', $nProductos, 'Obras en la tienda'],
        ['🏔️', 'Nariño',   'Colombia · Sur Andino'],
      ] as [$ic, $n, $lbl]): ?>
        <div style="background:#fff;border:1px solid var(--cream-dk);border-radius:14px;padding:20px 24px;display:flex;align-items:center;gap:18px;transition:border-color .25s,box-shadow .25s,transform .25s" class="stat-card">
          <span style="font-size:1.6rem;flex-shrink:0"><?= $ic ?></span>
          <div>
            <div style="font-family:var(--ff-d);font-size:1.9rem;font-weight:900;color:var(--gold);line-height:1"><?= $n ?></div>
            <div style="font-family:var(--ff-m);font-size:.8rem;letter-spacing:.12em;text-transform:uppercase;color:rgb(0, 0, 0);margin-top:3px"><?= $lbl ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>
</body>
</html>