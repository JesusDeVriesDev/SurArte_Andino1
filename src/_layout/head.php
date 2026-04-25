<?php
// Inicia la sesión solo si no hay una activa — este archivo se incluye en todas las páginas,
// por lo que podría llamarse cuando la sesión ya existe
if (session_status() === PHP_SESSION_NONE) session_start();

// Cada página que incluya head.php puede definir $pageTitle y $pageId antes del include.
// Si no los define, se usan estos valores por defecto para no romper el título ni la nav activa.
$pageTitle = $pageTitle ?? 'Inicio';
$pageId    = $pageId    ?? 'inicio';

// Construye el objeto de usuario para decidir qué mostrar en la navbar (botones de rol, carrito, etc.)
$user = isset($_SESSION['user_id'])
        ? ['nombre' => $_SESSION['nombre'], 'rol' => $_SESSION['rol']]
        : null;

// Detecta el prefijo de la instalación a partir de la URL del script.
// Esto permite que los hrefs funcionen tanto en desarrollo local (ej: /SurArte_Andino_dev)
// como en producción sin tener que hardcodear la ruta base en cada archivo.
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$base   = '';
if (preg_match('#(/SurArte_Andino[^/]*)#i', $script, $m)) {
    $base = $m[1];
}

// Definición centralizada de los ítems del menú de navegación principal.
// Agregar o quitar una sección del sitio solo requiere modificar este array;
// el HTML del topbar y del menú móvil se generan automáticamente a partir de él.
$NAV = [
    ['id'=>'inicio',    'icon'=>'🏔️', 'label'=>'Inicio',    'href'=>$base.'/src/inicio/inicio.php'],
    ['id'=>'artistas',  'icon'=>'🎨', 'label'=>'Artistas',  'href'=>$base.'/src/artistas/artistas.php'],
    ['id'=>'eventos',   'icon'=>'📅', 'label'=>'Eventos',   'href'=>$base.'/src/eventos/eventos.php'],
    ['id'=>'tienda',    'icon'=>'🛍️', 'label'=>'Tienda',    'href'=>$base.'/src/tienda/tienda.php'],
    ['id'=>'comunidad', 'icon'=>'🤝', 'label'=>'Comunidad', 'href'=>$base.'/src/comunidad/comunidad.php'],
];

// Estado de verificación del artista — se lee de sesión para evitar una consulta extra a la BD
// en cada carga de página. Se actualiza en login.php cada vez que el usuario inicia sesión.
$artistaVerificado = $_SESSION['artista_verificado'] ?? false;

// Contador del carrito leído de sesión — JS lo actualiza con updateNavCartBadge() tras cada acción
// (agregar, eliminar, vaciar) sin necesidad de recargar la página.
$navCartCount = (int)($_SESSION['carrito_count'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<!-- El título de la pestaña combina el nombre de la página con la marca del sitio -->
<title><?= htmlspecialchars($pageTitle) ?> — SurArte Andino</title>

<!-- Fuentes del sistema de diseño: Playfair Display (títulos), Cormorant Garamond (cuerpo), DM Mono (etiquetas/código) -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet"/>

<!-- Estilos globales del sistema: variables, reset, topbar, componentes y utilidades -->
<link rel="stylesheet" href="<?= $base ?>/src/_layout/head.css"/>

<!-- CSS de intro.js para los tours de onboarding -->
<link rel="stylesheet" href="https://unpkg.com/intro.js/introjs.css">
</head>
<body>

<!-- ─── Topbar ──────────────────────────────────────────────────────────────── -->
<header id="topbar">
  <!-- Logo que lleva siempre al index raíz -->
  <a class="tb-logo" href="<?= $base ?>/index.php">SurArte <span>Andino</span></a>

  <!-- Navegación principal — generada desde el array $NAV.
       La clase 'active' se asigna comparando el id del ítem con $pageId de la página actual. -->
  <nav class="tb-nav">
    <?php foreach ($NAV as $n): ?>
      <a id="<?= $n['id'] ?>" class="tb-link <?= $n['id'] === $pageId ? 'active' : '' ?>" href="<?= $n['href'] ?>">
        <span class="ti"><?= $n['icon'] ?></span><?= $n['label'] ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- Zona derecha: nombre de usuario, botones de rol, carrito y logout -->
  <div class="tb-right">
    <?php if ($user): ?>
      <!-- Muestra solo el primer nombre para no saturar la navbar en pantallas medianas -->
      <span class="tb-user"><?= htmlspecialchars(explode(' ', $user['nombre'])[0]) ?></span>

      <!-- Botón de admin — solo visible si el usuario tiene rol 'admin' -->
      <?php if ($user['rol'] === 'admin'): ?>
        <a class="btn-sm btn-ghost-sm tb-rol-btn" href="<?= $base ?>/src/admin/admin.php">⚙️ Admin</a>
      <?php endif; ?>

      <!-- Botón de perfil de artista — si no está verificado, el clic muestra un toast de aviso
           en lugar de redirigir, para no llevar al artista a una página incompleta -->
      <?php if ($user['rol'] === 'artista'): ?>
        <?php if ($artistaVerificado): ?>
          <a class="btn-sm btn-ghost-sm tb-rol-btn" href="<?= $base ?>/src/artistas/perfil/index.php">🎨 Mi perfil</a>
        <?php else: ?>
          <button class="btn-sm btn-ghost-sm tb-rol-btn"
            onclick="toast('Aún no estás verificado. Un administrador revisará tu perfil pronto.','warn')">
            🎨 Mi perfil
          </button>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Enlace a la cuenta del usuario — disponible para todos los roles -->
      <a class="btn-sm btn-ghost-sm tb-rol-btn" href="<?= $base ?>/src/perfil/index.php">👤 Cuenta</a>

      <!-- Botón del carrito — siempre visible independientemente del rol.
           El badge solo se renderiza si hay ítems; de lo contrario queda oculto con display:none
           para que JS pueda mostrarlo sin recargar la página. -->
      <a class="tb-cart-btn" href="<?= $base ?>/src/tienda/tienda.php" id="navCartLink">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <?php if ($navCartCount > 0): ?>
          <span class="tb-cart-badge" id="navCartBadge"><?= $navCartCount ?></span>
        <?php else: ?>
          <span class="tb-cart-badge" id="navCartBadge" style="display:none">0</span>
        <?php endif; ?>
      </a>

      <a class="btn-sm btn-ghost-sm" href="<?= $base ?>/src/auth/logout.php">Salir</a>

    <?php else: ?>
      <!-- Usuarios no autenticados ven los botones de login y registro.
           Se ocultan en móvil (tb-auth-btn) y se muestran en el menú hamburguesa. -->
      <a class="btn-sm btn-ghost-sm tb-auth-btn" href="<?= $base ?>/src/auth/login/index.php">Iniciar sesión</a>
      <a class="btn-sm btn-gold-sm  tb-auth-btn"  href="<?= $base ?>/src/auth/register/index.php">Registrarse</a>
    <?php endif; ?>
  </div>

  <!-- Botón hamburguesa — solo visible en ≤1100px, controla el menú móvil vía JS -->
  <button class="tb-burger" id="tbBurger" aria-label="Menú"><span></span><span></span><span></span></button>
</header>


<!-- ─── Menú móvil (overlay) ────────────────────────────────────────────────── -->
<!-- Se activa con la clase .open que JS agrega/quita al hacer clic en el burger -->
<div id="mobileMenu">

  <!-- Los mismos ítems de navegación que el topbar, en formato de lista grande para touch -->
  <?php foreach ($NAV as $n): ?>
    <a class="mm-link <?= $n['id'] === $pageId ? 'active' : '' ?>" href="<?= $n['href'] ?>">
      <span class="mm-icon"><?= $n['icon'] ?></span><?= $n['label'] ?>
    </a>
  <?php endforeach; ?>

  <!-- Separador visual entre nav y sección de cuenta -->
  <div style="width:85%;max-width:320px;height:1px;background:rgba(201,146,42,.15);margin:10px 0"></div>

  <!-- Sección de cuenta en el menú móvil — replica la lógica de roles del topbar -->
  <?php if ($user): ?>
    <?php if ($user['rol'] === 'admin'): ?>
      <a class="mm-link" href="<?= $base ?>/src/admin/admin.php">
        <span class="mm-icon">⚙️</span>Panel Admin
      </a>
    <?php endif; ?>

    <?php if ($user['rol'] === 'artista'): ?>
      <?php if ($artistaVerificado): ?>
        <a class="mm-link" href="<?= $base ?>/src/artistas/perfil/index.php">
          <span class="mm-icon">🎨</span>Mi perfil
        </a>
      <?php else: ?>
        <!-- Cierra el menú móvil antes de mostrar el toast para que no quede abierto detrás -->
        <button class="mm-link" style="text-align:left"
          onclick="document.getElementById('mobileMenu').classList.remove('open');document.getElementById('tbBurger').classList.remove('open');toast('Aún no estás verificado. Un administrador revisará tu perfil pronto.','warn')">
          <span class="mm-icon">🎨</span>Mi perfil
        </button>
      <?php endif; ?>
    <?php endif; ?>

    <a class="mm-link" href="<?= $base ?>/src/perfil/index.php">
      <span class="mm-icon">👤</span>Mi cuenta
    </a>

  <?php else: ?>
    <!-- Botones de auth en el menú móvil para usuarios no autenticados -->
    <a class="mm-link" href="<?= $base ?>/src/auth/login/index.php">
      <span class="mm-icon">🔑</span>Iniciar sesión
    </a>
    <a class="mm-link" href="<?= $base ?>/src/auth/register/index.php">
      <span class="mm-icon">✨</span>Registrarse
    </a>
  <?php endif; ?>
</div>


<!-- Contenedor del toast — el contenido y la visibilidad los maneja la función toast() en JS -->
<div id="toast"><span class="toast-icon"></span><span class="toast-msg"></span></div>


<script>
// Expone la ruta base al JS del cliente para que los scripts puedan construir URLs dinámicas
window.APP_BASE = '<?= $base ?>';

// ─── Menú hamburguesa ────────────────────────────────────────────────────────
// Toggle del menú móvil: alterna la clase .open en el burger y en el overlay del menú
const burger = document.getElementById('tbBurger');
const menu   = document.getElementById('mobileMenu');
burger.addEventListener('click', () => {
  burger.classList.toggle('open');
  // Alterna entre flex y none en lugar de solo toggle de .open porque el menú
  // necesita display:flex para centrar los ítems cuando está visible
  menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
  menu.classList.toggle('open');
});

// ─── Función global de toast ─────────────────────────────────────────────────
// Disponible en todas las páginas desde el momento en que head.php se carga.
// type puede ser: 'ok', 'err', 'warn', 'info'
let _tt; // Guarda el timeout para cancelarlo si se dispara otro toast antes de que el anterior desaparezca
function toast(msg, type = 'ok') {
  const icons = { ok: '✅', err: '❌', warn: '⚠️', info: 'ℹ️' };
  document.querySelector('#toast .toast-icon').textContent = icons[type] || '✅';
  document.querySelector('#toast .toast-msg').textContent  = msg;
  document.getElementById('toast').classList.add('show');
  clearTimeout(_tt);
  // El timeout de 3800ms debe coincidir con la transición CSS del toast para evitar parpadeos
  _tt = setTimeout(() => document.getElementById('toast').classList.remove('show'), 3800);
}

// ─── Actualización del badge del carrito ─────────────────────────────────────
// Llamada por los scripts de tienda/carrito cada vez que el usuario agrega o elimina un ítem,
// sin necesidad de recargar la página ni de hacer una nueva petición al servidor.
function updateNavCartBadge(count) {
  const badge = document.getElementById('navCartBadge');
  if (!badge) return;
  badge.textContent = count;
  badge.style.display = count > 0 ? 'inline-flex' : 'none';
}
</script>

<!-- intro.js para los tours guiados de onboarding -->
<script src="https://unpkg.com/intro.js/intro.js"></script>
<!-- Script del tour de navegación — se ejecuta en todas las páginas que usen head.php -->
<script src="<?= $base ?>/src/help.js"></script>
</body>
</html>
