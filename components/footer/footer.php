<?php
/** SurArte Andino — Footer global */
?>
<footer id="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="footer-logo">SurArte Andino</div>
      <p class="footer-tagline">La plataforma digital del sur de Colombia.<br>Arte, cultura y comunidad de Nariño para el mundo.</p>
      <div class="footer-social">
        <a href="#" class="social-btn" title="Instagram">📸</a>
        <a href="#" class="social-btn" title="Facebook">👍</a>
        <a href="#" class="social-btn" title="YouTube">▶️</a>
        <a href="#" class="social-btn" title="TikTok">🎵</a>
      </div>
    </div>
    <div class="footer-col">
      <h5>Plataforma</h5>
      <ul>
        <li><a href="/artistas">Artistas</a></li>
        <li><a href="/eventos">Eventos</a></li>
        <li><a href="/tienda">Tienda</a></li>
        <li><a href="/comunidad">Comunidad</a></li>
        <li><a href="/roadmap">Roadmap</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h5>Artistas</h5>
      <ul>
        <li><a href="/auth/register?tipo=artista">Registrarse</a></li>
        <li><a href="/comunidad">Foro</a></li>
        <li><a href="/eventos">Crear evento</a></li>
        <li><a href="/tienda">Vender arte</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h5>Legal</h5>
      <ul>
        <li><a href="/legal/privacidad">Privacidad</a></li>
        <li><a href="/legal/terminos">Términos</a></li>
        <li><a href="/legal/habeas-data">Habeas Data</a></li>
        <li><a href="/contacto">Contacto</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© <?= date('Y') ?> SurArte Andino · Pasto, Nariño, Colombia</span>
    <span>v<?= APP_VERSION ?? '1.0.0' ?></span>
  </div>
</footer>

<!-- Toast global -->
<div id="toast"><span class="toast-icon"></span><span class="toast-msg"></span></div>

<!-- Page indicator dots -->
<div id="pgdots">
  <?php foreach (['inicio','artistas','eventos','tienda','comunidad','tecnologia','roadmap'] as $pg): ?>
    <div class="pgdot <?= ($currentPage ?? 'inicio') === $pg ? 'on' : '' ?>"
         onclick="Router.go('<?= $pg ?>')" title="<?= ucfirst($pg) ?>"></div>
  <?php endforeach; ?>
</div>
