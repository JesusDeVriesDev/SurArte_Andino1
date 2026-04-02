<?php
$currentPage = $currentPage ?? 'inicio';
$navItems = [
  ['id'=>'inicio',     'icon'=>'🏔️', 'label'=>'Inicio'],
  ['id'=>'artistas',   'icon'=>'🎨', 'label'=>'Artistas'],
  ['id'=>'eventos',    'icon'=>'📅', 'label'=>'Eventos'],
  ['id'=>'tienda',     'icon'=>'🛍️', 'label'=>'Tienda'],
  ['id'=>'comunidad',  'icon'=>'🤝', 'label'=>'Comunidad'],
  ['id'=>'tecnologia', 'icon'=>'💻', 'label'=>'Tech'],
  ['id'=>'roadmap',    'icon'=>'🗓️', 'label'=>'Roadmap'],
];
?>
<div id="loader">
  <div class="ld-logo">SurArte Andino</div>
  <div class="ld-sub">Nariño · Colombia</div>
  <div class="ld-track"><div class="ld-bar"></div></div>
</div>

<div id="mobilebar">
  <a href="/" class="mb-logo">SurArte Andino</a>
  <button class="mb-burger" id="mbBurger" aria-label="Menú">
    <span></span><span></span><span></span>
  </button>
</div>

<div id="mobileMenu">
  <?php foreach ($navItems as $item): ?>
    <a href="/<?= $item['id'] === 'inicio' ? '' : $item['id'] ?>"
       class="mm-item <?= $currentPage === $item['id'] ? 'active' : '' ?>"
       data-page="<?= $item['id'] ?>">
      <span class="mm-icon"><?= $item['icon'] ?></span>
      <span class="mm-name"><?= $item['label'] ?></span>
    </a>
  <?php endforeach; ?>
  <div data-auth="guest">
    <a href="/auth/login" class="btn btn-primary btn-sm">Iniciar sesión</a>
  </div>
  <div data-auth="logged" style="display:none">
    <span data-auth-name></span>
    <button onclick="Auth.logout()" class="btn btn-ghost btn-sm">Salir</button>
  </div>
</div>

<nav id="sidenav">
  <a href="/" class="sn-logo" title="SurArte Andino">S</a>
  <div class="sn-items">
    <?php foreach ($navItems as $item): ?>
      <button class="sn-item <?= $currentPage === $item['id'] ? 'active' : '' ?>"
              data-page="<?= $item['id'] ?>"
              data-tip="<?= $item['label'] ?>">
        <span class="sn-icon"><?= $item['icon'] ?></span>
        <span class="sn-lbl"><?= $item['label'] ?></span>
      </button>
    <?php endforeach; ?>
  </div>
  <div class="sn-bottom">
    <div class="sn-divider"></div>
    <button class="sn-icon-btn" onclick="Router.go('tienda')" title="Carrito" style="position:relative">
      🛒<span class="cart-badge" style="position:absolute;top:-2px;right:-2px;background:var(--color-gold);color:var(--color-ink);font-family:var(--ff-mono);font-size:.5rem;border-radius:50%;width:14px;height:14px;display:flex;align-items:center;justify-content:center"></span>
    </button>
    <div data-auth="guest">
      <button class="sn-icon-btn" onclick="Router.go('login')" title="Iniciar sesión">👤</button>
    </div>
  </div>
</nav>
