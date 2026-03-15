<?php
$pageTitle = 'Comunidad';
$pageId    = 'comunidad';
require_once '../_layout/head.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/_layout/global.css"/>
  <link rel="stylesheet" href="<?= $base ?>/src/comunidad/comunidad.css"/>
</head>

<main>
  <div class="coming-wrapper">
    <div class="coming-bg"></div>
    <div class="deco-text">Comunidad</div>
    <div class="coming-icon-ring">🤝</div>
    <div class="prox-label">
      <span class="prox-dot"></span>
      Próximamente
    </div>
    <h1 class="coming-title">
      Estamos<br><em>construyendo</em><br>algo grande
    </h1>
    <div class="gold-line-center"></div>
    <p class="coming-desc">
      La sección de Comunidad está en pleno desarrollo. Pronto tendrás un espacio para conectar con artistas, 
      compartir tu trabajo, participar en foros y hacer parte activa de la escena cultural de Nariño.
    </p>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">💬</div>
        <div class="feature-title">Foros</div>
        <div class="feature-text">Debates y conversaciones entre artistas y aficionados</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🖼️</div>
        <div class="feature-title">Portafolios</div>
        <div class="feature-text">Comparte y descubre obras de la comunidad creativa</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🌐</div>
        <div class="feature-title">Red de contactos</div>
        <div class="feature-text">Conecta con otros creadores de la región andina</div>
      </div>
    </div>
    <div class="notify-row">
      <a href="../artistas/artistas.php" class="btn btn-gold" style="font-size:.7rem">
        Ver artistas →
      </a>
      <a href="../eventos/eventos.php" class="btn btn-outline" style="font-size:.7rem">
        Explorar eventos
      </a>
    </div>
  </div>
</main>
</body>
</html>