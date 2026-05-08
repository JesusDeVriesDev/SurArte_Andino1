<?php
$pageTitle = 'Soporte';
$pageId    = 'soporte';
require_once '../_layout/head.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/_layout/global.css"/>
  <link rel="stylesheet" href="soporte.css"/>
</head>

<main class="soporte-main">

  <!-- ── Hero del módulo ─────────────────────────────────── -->
  <div class="soporte-hero">
    <div class="soporte-hero__tag">
      <span class="dot"></span>Centro de Ayuda
    </div>
    <h1 class="soporte-hero__title">Soporte &amp; <em>Guías</em></h1>
    <p class="soporte-hero__sub">
      Todo lo que necesitas para aprovechar SurArte Andino al máximo.<br>
      Videos, manuales y recursos de acceso rápido.
    </p>

    <!-- Anclas de navegación rápida -->
    <nav class="soporte-anchors">
      <a href="#videos-guia"    class="anchor-chip">▶ Videos de guía</a>
      <a href="#manuales"       class="anchor-chip">📚 Manuales de usuario</a>
      <a href="#video-manual"   class="anchor-chip">🎬 Video del manual</a>
    </nav>
  </div>


  <!-- ══════════════════════════════════════════════════════
       SECCIÓN 1 — Videos de guía de acceso rápido (5 videos)
       ══════════════════════════════════════════════════════ -->
  <section id="videos-guia" class="soporte-section">
    <div class="section-header">
      <span class="section-icon">▶</span>
      <div>
        <h2 class="section-title">Videos de guía rápida</h2>
        <p class="section-desc">Aprende los flujos principales en minutos con estos tutoriales en video.</p>
      </div>
    </div>

    <div class="videos-grid">

      <?php
      // ──────────────────────────────────────────────────────────────────────
      // Reemplaza cada SRC con el enlace de embebido de Google Drive.
      // Formato: https://drive.google.com/file/d/FILE_ID/preview
      // ──────────────────────────────────────────────────────────────────────
      $videos_guia = [
        ['titulo' => 'Editar información de usuario',   'desc' => 'Cómo configurar tu perfil inicial.',          'src' => 'https://drive.google.com/file/d/1ae5hPyuHtPhk4S9PgHqhh_Eg4ZWJiNtO/preview'],
        ['titulo' => 'Explorar eventos',  'desc' => 'Navega el catálogo de eventos cercanos.',  'src' => 'https://drive.google.com/file/d/1DMf6xW7oo9cwR9cvvrNF4-KkN6Q8RL5J/preview'],
        ['titulo' => 'Explorar comunidad',         'desc' => 'Participa en foros, conecta con otros artistas y más.', 'src' => 'https://drive.google.com/file/d/1TeKatM44kTcwjukXniz9CAy7ufSF5TKD/preview'],
        ['titulo' => 'Explorar artista',        'desc' => 'Navega el catálogo de artistas y descubre su trabajo.',       'src' => 'https://drive.google.com/file/d/12oKs_4F_3TQhc5bd519RZUICFRdSrTNr/preview'],
        ['titulo' => 'Explorar tienda',     'desc' => 'Navega el catálogo de productos y realiza compras.',        'src' => 'https://drive.google.com/file/d/1U6lH5l_EnRhU8wDN3jdchZUb0eCfYBGp/preview'],
      ];
      foreach ($videos_guia as $i => $v):
      ?>
      <div class="video-card" style="--delay:<?= $i * 0.08 ?>s">
        <div class="video-card__frame">
          <iframe
            src="<?= htmlspecialchars($v['src']) ?>"
            allow="autoplay"
            allowfullscreen
            loading="lazy"
            title="<?= htmlspecialchars($v['titulo']) ?>">
          </iframe>
        </div>
        <div class="video-card__body">
          <span class="video-card__num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
          <h3 class="video-card__title"><?= htmlspecialchars($v['titulo']) ?></h3>
          <p  class="video-card__desc"><?= htmlspecialchars($v['desc']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>


  <!-- ══════════════════════════════════════════════════════
       SECCIÓN 2 — Guía PDF de Drive
       ══════════════════════════════════════════════════════ -->
  <section id="manuales" class="soporte-section">
    <div class="section-header">
      <span class="section-icon">📚</span>
      <div>
        <h2 class="section-title">Manuales de usuario</h2>
        <p class="section-desc">Documentación técnica detallada organizada por módulo.</p>
      </div>
    </div>

    <div class="pdf-viewer-wrap">
      <div class="pdf-topbar">
        <span class="pdf-label">📄 Mi cuenta.pdf</span>
        <!-- Reemplaza PDF_GUIA_ID con el ID real del archivo en Drive -->
        <a class="btn-sm btn-gold-sm"
           href="https://drive.google.com/file/d/1NbwyeATHLYvUFc_a-WHMBMPaugdcHBpG/view"
           target="_blank" rel="noopener">
          Abrir en Drive ↗
        </a>
      </div>
      <div class="pdf-frame-container">
        <!-- Formato embebido Drive: https://drive.google.com/file/d/FILE_ID/preview -->
        <iframe
          src="https://drive.google.com/file/d/1NbwyeATHLYvUFc_a-WHMBMPaugdcHBpG/preview"
          class="pdf-frame"
          allow="autoplay"
          loading="lazy"
          title="Guía de uso de Mi Cuenta">
        </iframe>
      </div>
    </div>
    <div class="pdf-viewer-wrap">
      <div class="pdf-topbar">
        <span class="pdf-label">📄 Registro de nuevo usuario.pdf</span>
        <!-- Reemplaza PDF_GUIA_ID con el ID real del archivo en Drive -->
        <a class="btn-sm btn-gold-sm"
           href="https://drive.google.com/file/d/1_VwhRhEwIb1TOfM59LD8LMgIAcC_Vnv2/view"
           target="_blank" rel="noopener">
          Abrir en Drive ↗
        </a>
      </div>
      <div class="pdf-frame-container">
        <!-- Formato embebido Drive: https://drive.google.com/file/d/FILE_ID/preview -->
        <iframe
          src="https://drive.google.com/file/d/1_VwhRhEwIb1TOfM59LD8LMgIAcC_Vnv2/preview"
          class="pdf-frame"
          allow="autoplay"
          loading="lazy"
          title="Guía de uso Registro de nuevo usuario">
        </iframe>
      </div>
    </div>
    <div class="pdf-viewer-wrap">
      <div class="pdf-topbar">
        <span class="pdf-label">📄 Iniciar sesión.pdf</span>
        <!-- Reemplaza PDF_GUIA_ID con el ID real del archivo en Drive -->
        <a class="btn-sm btn-gold-sm"
           href="https://drive.google.com/file/d/1Q9zVHgSvRftTH__MB8g_6J4N5O0ady6e/view"
           target="_blank" rel="noopener">
          Abrir en Drive ↗
        </a>
      </div>
      <div class="pdf-frame-container">
        <!-- Formato embebido Drive: https://drive.google.com/file/d/FILE_ID/preview -->
        <iframe
          src="https://drive.google.com/file/d/1Q9zVHgSvRftTH__MB8g_6J4N5O0ady6e/preview"
          class="pdf-frame"
          allow="autoplay"
          loading="lazy"
          title="Guía de uso Iniciar sesión">
        </iframe>
      </div>
    </div>
  </section>
  <!-- ══════════════════════════════════════════════════════
       SECCIÓN 4 — Video del manual
       ══════════════════════════════════════════════════════ -->
  <section id="video-manual" class="soporte-section soporte-section--last">
    <div class="section-header">
      <span class="section-icon">🎬</span>
      <div>
        <h2 class="section-title">Video del manual completo</h2>
        <p class="section-desc">Recorrido en video por todas las funcionalidades de la plataforma.</p>
      </div>
    </div>

    <div class="video-manual-wrap">
      <div class="video-manual-frame">
        <!-- Reemplaza VIDEO_MANUAL_ID con el ID real del video en Drive -->
        <iframe
          src="https://www.youtube.com/embed/mA6UYeewKL4"
          allow="autoplay"
          allowfullscreen
          loading="lazy"
          title="Video manual completo SurArte Andino">
        </iframe>
      </div>
      <div class="video-manual-info">
        <h3>Manual completo en video</h3>
        <p>Este video cubre en profundidad todas las secciones de la plataforma: registro, perfil de artista, tienda, eventos y comunidad. Ideal para nuevos usuarios o como referencia de consulta.</p>
        <a class="btn btn-gold"
           style="font-weight:550;color:#fff;display:inline-flex;align-items:center;gap:8px;margin-top:8px"
           href="https://youtu.be/mA6UYeewKL4?si=C4rTrZuxq1636dkd"
           target="_blank" rel="noopener">
          Ver en pantalla completa ↗
        </a>
      </div>
    </div>
  </section>

</main>

<script>
// ── Toggle de manuales PDF ────────────────────────────────────────────────────
// Carga el iframe solo cuando se abre por primera vez (lazy load real).
function toggleManual(index) {
  const viewer  = document.getElementById('manual-frame-' + index);
  const btn     = viewer.previousElementSibling;
  const iframe  = viewer.querySelector('iframe');
  const isOpen  = !viewer.hidden;

  // Cierra todos los demás paneles abiertos
  document.querySelectorAll('.manual-card__viewer').forEach((v, i) => {
    if (i !== index && !v.hidden) {
      v.hidden = true;
      v.previousElementSibling.setAttribute('aria-expanded', 'false');
      v.previousElementSibling.querySelector('.manual-card__chevron').style.transform = '';
    }
  });

  // Toggle del panel actual
  viewer.hidden = isOpen;
  btn.setAttribute('aria-expanded', String(!isOpen));
  btn.querySelector('.manual-card__chevron').style.transform = isOpen ? '' : 'rotate(180deg)';

  // Lazy load: copia data-src → src solo la primera vez
  if (!isOpen && iframe && !iframe.src && iframe.dataset.src) {
    iframe.src = iframe.dataset.src;
  }
}

// ── Animación de entrada al hacer scroll ─────────────────────────────────────
if ('IntersectionObserver' in window) {
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
    });
  }, { threshold: 0.08 });
  document.querySelectorAll('.video-card, .manual-card, .pdf-viewer-wrap, .video-manual-wrap')
    .forEach(el => obs.observe(el));
}
</script>
</body>
</html>