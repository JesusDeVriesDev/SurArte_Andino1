<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$pageTitle = $pageTitle ?? 'Inicio';
$pageId    = $pageId    ?? 'inicio';
$user      = isset($_SESSION['user_id'])
             ? ['nombre' => $_SESSION['nombre'], 'rol' => $_SESSION['rol']]
             : null;

$script = $_SERVER['SCRIPT_NAME'] ?? '';
$base   = '';
if (preg_match('#(/SurArte_Andino)#i', $script, $m)) {
    $base = $m[1];
}

$NAV = [
    ['id'=>'inicio',    'icon'=>'🏔️', 'label'=>'Inicio',    'href'=>$base.'/src/inicio/inicio.php'],
    ['id'=>'artistas',  'icon'=>'🎨', 'label'=>'Artistas',  'href'=>$base.'/src/artistas/artistas.php'],
    ['id'=>'eventos',   'icon'=>'📅', 'label'=>'Eventos',   'href'=>$base.'/src/eventos/eventos.php'],
    ['id'=>'tienda',    'icon'=>'🛍️', 'label'=>'Tienda',    'href'=>$base.'/src/tienda/tienda.php'],
    ['id'=>'comunidad', 'icon'=>'🤝', 'label'=>'Comunidad', 'href'=>$base.'/src/comunidad/comunidad.php'],
];

// Estado verificado del artista — leído de $_SESSION para no hacer consulta en cada página
$artistaVerificado = $_SESSION['artista_verificado'] ?? false;
// Contador de carrito — leído de $_SESSION, actualizado por JS tras cada acción
$navCartCount = (int)($_SESSION['carrito_count'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title><?= htmlspecialchars($pageTitle) ?> — SurArte Andino</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
:root{
  --gold:#C9922A;--gold-lt:#E8B84B;--gold-pale:#F5E6C8;
  --ink:#1A1208;--ink-mid:#3D2B10;
  --clay:#8B3A1C;--clay-lt:#B5552E;
  --sky:#1D4E6B;--sky-lt:#2E7AA8;
  --cream:#FAF5EC;--cream-dk:#EDE4D0;--white:#FFFEF9;
  --ok:#22c55e;--err:#ef4444;
  --ff-d:'Playfair Display',Georgia,serif;
  --ff-b:'Cormorant Garamond',Georgia,serif;
  --ff-m:'DM Mono',monospace;
  --r:8px;--r-lg:16px;--r-full:9999px;
  --sh:0 4px 24px rgba(26,18,8,.12);
  --sh-lg:0 16px 56px rgba(26,18,8,.22);
  --sh-gold:0 8px 28px rgba(201,146,42,.35);
  --ease:cubic-bezier(.4,0,.2,1);
  --dur:.28s;
  --nav-h:64px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:var(--ff-b);font-size:16px;background:var(--cream);color:var(--ink);min-height:100vh}
a{text-decoration:none;color:inherit}
ul{list-style:none}
button{cursor:pointer;border:none;background:none;font:inherit}
img{max-width:100%;display:block}
::selection{background:var(--gold);color:var(--ink)}
::-webkit-scrollbar{width:4px}::-webkit-scrollbar-thumb{background:var(--gold);border-radius:2px}

#topbar{
  position:sticky;top:0;z-index:500;
  height:var(--nav-h);
  background:rgba(26,18,8,.97);
  backdrop-filter:blur(20px);
  border-bottom:1px solid rgba(201,146,42,.12);
  display:flex;align-items:center;
  padding:0 clamp(16px,4vw,40px);
  gap:28px;
}
.tb-logo{font-family:var(--ff-d);font-size:1.15rem;font-weight:900;color:var(--gold);letter-spacing:-.02em;white-space:nowrap;}
.tb-logo span{font-style:italic;color:rgba(201,146,42,.6)}
.tb-nav{display:flex;align-items:center;gap:4px;flex:1;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none}
.tb-nav::-webkit-scrollbar{display:none}
.tb-link{display:flex;align-items:center;gap:7px;padding:7px 14px;border-radius:var(--r-full);font-family:var(--ff-m);font-size:.62rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(250,245,236,.42);white-space:nowrap;transition:color var(--dur),background var(--dur);}
.tb-link:hover{color:rgba(250,245,236,.85);background:rgba(250,245,236,.07)}
.tb-link.active{color:var(--gold);background:rgba(201,146,42,.12)}
.tb-link .ti{font-size:.9rem}
.tb-right{margin-left:auto;display:flex;align-items:center;gap:10px}
.tb-user{font-family:var(--ff-m);font-size:.62rem;letter-spacing:.09em;color:rgba(250,245,236,.45)}
.btn-sm{padding:7px 16px;border-radius:var(--r-full);font-family:var(--ff-m);font-size:.6rem;letter-spacing:.1em;text-transform:uppercase;transition:all .22s;}
.btn-gold-sm{background:var(--gold);color:var(--ink)}.btn-gold-sm:hover{background:var(--gold-lt)}
.btn-ghost-sm{color:rgba(250,245,236,.45);border:1px solid rgba(250,245,236,.12)}.btn-ghost-sm:hover{color:var(--gold);border-color:rgba(201,146,42,.3)}
.tb-cart-btn{position:relative;display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:var(--r-full);font-family:var(--ff-m);font-size:.6rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(250,245,236,.45);border:1px solid rgba(250,245,236,.12);transition:all .22s;text-decoration:none;}
.tb-cart-btn:hover{color:var(--gold);border-color:rgba(201,146,42,.3)}
.tb-cart-badge{background:var(--gold);color:var(--ink);border-radius:50%;width:16px;height:16px;font-size:.5rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;font-family:var(--ff-m);}
.tb-burger{display:none;flex-direction:column;gap:4px;padding:6px}
.tb-burger span{width:18px;height:1.5px;background:var(--cream);border-radius:1px;transition:transform .3s,opacity .3s}
.tb-burger.open span:nth-child(1){transform:translateY(5.5px) rotate(45deg)}
.tb-burger.open span:nth-child(2){opacity:0}
.tb-burger.open span:nth-child(3){transform:translateY(-5.5px) rotate(-45deg)}
#mobileMenu{display:none;position:fixed;inset:var(--nav-h) 0 0;background:rgba(20,14,6,.98);z-index:490;flex-direction:column;align-items:center;justify-content:center;gap:8px;transform:translateX(-100%);transition:transform .38s var(--ease);}
#mobileMenu.open{transform:translateX(0)}
.mm-link{width:82%;max-width:300px;padding:14px 22px;border-radius:var(--r-lg);display:flex;align-items:center;gap:14px;font-family:var(--ff-d);font-size:1.05rem;font-weight:700;color:var(--cream);transition:background .2s;}
.mm-link:hover,.mm-link.active{background:rgba(201,146,42,.1);color:var(--gold)}
.mm-icon{font-size:1.15rem}
main{min-height:calc(100vh - var(--nav-h));padding:clamp(32px,5vw,60px) clamp(16px,5vw,60px) 80px;max-width:1200px;margin:0 auto;}
.eyebrow{font-family:var(--ff-m);font-size:.62rem;letter-spacing:.2em;text-transform:uppercase;color:var(--clay);display:flex;align-items:center;gap:9px;margin-bottom:10px}
.eyebrow::before{content:'';width:20px;height:1px;background:currentColor}
.page-h1{font-family:var(--ff-d);font-size:clamp(2.4rem,5vw,4.2rem);font-weight:900;line-height:.95;letter-spacing:-.03em;color:var(--ink);margin-bottom:16px}
.page-h1 em{font-style:italic;color:var(--clay)}
.page-lead{font-size:clamp(.95rem,1.3vw,1.1rem);font-weight:300;color:rgba(26,18,8,.52);max-width:520px;line-height:1.82;margin-bottom:36px}
.gold-line{width:42px;height:3px;background:var(--gold);border-radius:2px;margin:20px 0}
.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:var(--r);font-family:var(--ff-m);font-size:.68rem;letter-spacing:.12em;text-transform:uppercase;transition:all var(--dur) var(--ease);border:none;cursor:pointer}
.btn-gold{background:var(--gold);color:var(--ink)}.btn-gold:hover{background:var(--gold-lt);transform:translateY(-2px);box-shadow:var(--sh-gold)}
.btn-outline{background:transparent;border:1px solid rgba(26,18,8,.2);color:var(--ink)}.btn-outline:hover{border-color:var(--gold);color:var(--gold)}
.btn-danger{background:var(--err);color:#fff}.btn-danger:hover{opacity:.85}
.btn:disabled{opacity:.45;cursor:not-allowed;transform:none !important}
.card{background:var(--white,#FFFEF9);border:1px solid var(--cream-dk);border-radius:var(--r-lg);overflow:hidden;transition:box-shadow var(--dur),transform var(--dur),border-color var(--dur)}
.card:hover{box-shadow:var(--sh-lg);transform:translateY(-4px)}
.card-body{padding:20px 22px}
.badge{display:inline-block;padding:3px 10px;border-radius:var(--r-full);font-family:var(--ff-m);font-size:.52rem;letter-spacing:.09em;text-transform:uppercase}
.badge-gold{background:rgba(201,146,42,.12);color:var(--gold)}
.badge-clay{background:rgba(139,58,28,.12);color:var(--clay)}
.badge-sky{background:rgba(29,78,107,.12);color:var(--sky)}
.badge-green{background:rgba(34,197,94,.1);color:#16a34a}
.badge-muted{background:rgba(26,18,8,.06);color:rgba(26,18,8,.45)}
.field{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.field-label{font-family:var(--ff-m);font-size:.6rem;letter-spacing:.13em;text-transform:uppercase;color:rgba(26,18,8,.52)}
.field-input,.field-select,.field-textarea{background:var(--cream-dk);border:1.5px solid transparent;border-radius:var(--r);padding:10px 13px;font-family:var(--ff-b);font-size:.97rem;color:var(--ink);transition:border-color .22s;width:100%}
.field-input:focus,.field-select:focus,.field-textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(201,146,42,.1)}
.field-input::placeholder,.field-textarea::placeholder{color:rgba(26,18,8,.27)}
.field-input.err{border-color:var(--err)}.field-err{font-size:.73rem;color:var(--err);min-height:15px}
.field-textarea{resize:vertical;min-height:100px}
#toast{position:fixed;bottom:22px;right:22px;z-index:9000;background:rgba(26,18,8,.96);border:1px solid rgba(201,146,42,.25);border-radius:14px;padding:12px 18px;display:flex;align-items:center;gap:9px;max-width:320px;transform:translateY(80px);opacity:0;transition:transform .42s var(--ease),opacity .42s;pointer-events:none}
#toast.show{transform:translateY(0);opacity:1}
.toast-icon{font-size:.9rem}.toast-msg{font-size:.88rem;color:var(--cream);font-weight:300}
.alert{padding:11px 16px;border-radius:var(--r);margin-bottom:18px;font-size:.9rem;font-weight:300}
.alert-err{background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.18);color:#b91c1c}
.alert-ok{background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.18);color:#15803d}
.alert-info{background:rgba(29,78,107,.07);border:1px solid rgba(29,78,107,.18);color:var(--sky)}
.grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}
.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.grid-auto{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:20px}
.flex{display:flex}.flex-col{display:flex;flex-direction:column}
.items-center{align-items:center}.justify-between{justify-content:space-between}
.gap-2{gap:8px}.gap-4{gap:16px}.gap-6{gap:24px}
.mt-6{margin-top:24px}.mt-8{margin-top:32px}.mt-12{margin-top:48px}
.text-center{text-align:center}
.empty{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;padding:80px 20px;text-align:center;color:rgba(26,18,8,.35)}
.empty-icon{font-size:2.8rem}.empty p{font-size:1.05rem;font-weight:300}
body::after{content:'';position:fixed;inset:0;pointer-events:none;z-index:9998;opacity:.1;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}
@media(max-width:860px){.tb-nav{display:none}.tb-burger{display:flex}.grid-3{grid-template-columns:repeat(2,1fr)}.grid-4{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.grid-2,.grid-3,.grid-4,.grid-auto{grid-template-columns:1fr}}
</style>
</head>
<body>

<header id="topbar">
  <a class="tb-logo" href="<?= $base ?>/index.php">SurArte <span>Andino</span></a>

  <nav class="tb-nav">
    <?php foreach ($NAV as $n): ?>
      <a class="tb-link <?= $n['id'] === $pageId ? 'active' : '' ?>" href="<?= $n['href'] ?>">
        <span class="ti"><?= $n['icon'] ?></span><?= $n['label'] ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="tb-right">
    <?php if ($user): ?>
      <span class="tb-user">Hola, <?= htmlspecialchars(explode(' ', $user['nombre'])[0]) ?></span>

      <?php if ($user['rol'] === 'admin'): ?>
        <a class="btn-sm btn-ghost-sm" href="<?= $base ?>/src/admin/admin.php">⚙️ Admin</a>
      <?php endif; ?>

      <?php if ($user['rol'] === 'artista'): ?>
        <?php if ($artistaVerificado): ?>
          <a class="btn-sm btn-ghost-sm" href="<?= $base ?>/src/artistas/perfil/index.php">🎨 Mi perfil</a>
        <?php else: ?>
          <button class="btn-sm btn-ghost-sm"
            onclick="toast('Aún no estás verificado. Un administrador revisará tu perfil pronto.','warn')">
            🎨 Mi perfil
          </button>
        <?php endif; ?>
      <?php endif; ?>

      <a class="btn-sm btn-ghost-sm" href="<?= $base ?>/src/perfil/index.php">👤 Cuenta</a>

      <!-- Carrito -->
      <a class="tb-cart-btn" href="<?= $base ?>/src/tienda/tienda.php" id="navCartLink">
        🛒
        <?php if ($navCartCount > 0): ?>
          <span class="tb-cart-badge" id="navCartBadge"><?= $navCartCount ?></span>
        <?php else: ?>
          <span class="tb-cart-badge" id="navCartBadge" style="display:none">0</span>
        <?php endif; ?>
      </a>

      <a class="btn-sm btn-ghost-sm" href="<?= $base ?>/src/auth/logout.php">Salir</a>
    <?php else: ?>
      <a class="btn-sm btn-ghost-sm" href="<?= $base ?>/src/auth/login/index.php">Iniciar sesión</a>
      <a class="btn-sm btn-gold-sm"  href="<?= $base ?>/src/auth/register/index.php">Registrarse</a>
    <?php endif; ?>
  </div>

  <button class="tb-burger" id="tbBurger"><span></span><span></span><span></span></button>
</header>

<div id="mobileMenu">
  <?php foreach ($NAV as $n): ?>
    <a class="mm-link <?= $n['id'] === $pageId ? 'active' : '' ?>" href="<?= $n['href'] ?>">
      <span class="mm-icon"><?= $n['icon'] ?></span><?= $n['label'] ?>
    </a>
  <?php endforeach; ?>
  <div style="margin-top:16px;display:flex;flex-direction:column;gap:8px;width:82%;max-width:300px">
    <?php if ($user): ?>
      <a class="btn btn-outline" style="justify-content:center" href="<?= $base ?>/src/auth/logout.php">Cerrar sesión</a>
    <?php else: ?>
      <a class="btn btn-gold"    style="justify-content:center" href="<?= $base ?>/src/auth/login/index.php">Iniciar sesión</a>
      <a class="btn btn-outline" style="justify-content:center" href="<?= $base ?>/src/auth/register/index.php">Registrarse</a>
    <?php endif; ?>
  </div>
</div>

<div id="toast"><span class="toast-icon"></span><span class="toast-msg"></span></div>

<script>
window.APP_BASE = '<?= $base ?>';
const burger = document.getElementById('tbBurger');
const menu   = document.getElementById('mobileMenu');
burger.addEventListener('click', () => {
  burger.classList.toggle('open');
  menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
  menu.classList.toggle('open');
});

let _tt;
function toast(msg, type = 'ok') {
  const icons = { ok: '✅', err: '❌', warn: '⚠️', info: 'ℹ️' };
  document.querySelector('#toast .toast-icon').textContent = icons[type] || '✅';
  document.querySelector('#toast .toast-msg').textContent  = msg;
  document.getElementById('toast').classList.add('show');
  clearTimeout(_tt);
  _tt = setTimeout(() => document.getElementById('toast').classList.remove('show'), 3800);
}

// Actualizar badge del carrito en navbar desde JS
function updateNavCartBadge(count) {
  const badge = document.getElementById('navCartBadge');
  if (!badge) return;
  badge.textContent = count;
  badge.style.display = count > 0 ? 'inline-flex' : 'none';
}
</script>
