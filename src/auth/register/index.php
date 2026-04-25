<?php
session_start();

// Detecta el prefijo de instalación para construir rutas correctamente en cualquier entorno
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$base   = '';
if (preg_match('#(/SurArte_Andino[^/]*)#i', $script, $m)) {
    $base = $m[1];
}

// Recupera los datos del intento fallido dejados por register.php en sesión:
// el error para mostrarlo y nombre/email para repoblar los campos sin que el usuario los reescriba
$error  = $_SESSION['reg_error']  ?? '';
$nombre = $_SESSION['reg_nombre'] ?? '';
$email  = $_SESSION['reg_email']  ?? '';
unset($_SESSION['reg_error'], $_SESSION['reg_nombre'], $_SESSION['reg_email']);

// Si el usuario ya tiene sesión no necesita registrarse — redirige directo al inicio
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . $base . '/src/inicio/inicio.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Crear cuenta — SurArte Andino</title>

  <!-- Estilos base del proyecto y estilos específicos de la vista de registro -->
  <link rel="stylesheet" href="<?= $base ?>/public/css/main.css"/>
  <link rel="stylesheet" href="<?= $base ?>/src/auth/register/register.css"/>

  <!-- CSS de intro.js para el tour guiado de esta página -->
  <link rel="stylesheet" href="https://unpkg.com/intro.js/introjs.css">
</head>
<body class="auth-body">

<div class="auth-layout">

  <!-- ─── Panel visual izquierdo ─────────────────────────────────────────────
       Decorativo — presenta la marca y los beneficios de registrarse.
       Se oculta en móvil mediante media query en register.css. -->
  <div class="auth-visual">
    <div class="auth-visual-content">
      <div class="auth-brand">SurArte<br><em>Andino</em></div>
      <div class="gold-line"></div>
      <p class="auth-visual-sub">Arte y cultura del sur de Colombia</p>
      <ul class="auth-perks">
        <li class="auth-perk">
          <span class="perk-icon">◈</span>
          <span>Accede a obra exclusiva de artistas locales</span>
        </li>
        <li class="auth-perk">
          <span class="perk-icon">◈</span>
          <span>Guarda tus piezas favoritas</span>
        </li>
        <li class="auth-perk">
          <span class="perk-icon">◈</span>
          <span>Conecta con la comunidad andina</span>
        </li>
      </ul>
    </div>
  </div>

  <!-- ─── Panel del formulario ───────────────────────────────────────────────── -->
  <div class="auth-panel">
    <div class="auth-form-wrap">

      <div class="auth-header">
        <div class="eyebrow">Únete a la comunidad</div>
        <h1 class="auth-title">Crear <em>cuenta</em></h1>
      </div>

      <!-- Mensaje de error de servidor — solo visible si register.php dejó un error en sesión.
           JS lo desvanece automáticamente a los 5 segundos. -->
      <?php if ($error): ?>
        <div class="auth-error" id="serverError">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <!-- novalidate desactiva la validación nativa para usar la de register.js -->
      <form id="registerForm" action="register.php" method="post" novalidate>

        <!-- Campo nombre — se repobla con $nombre si el registro falló -->
        <div class="input-group">
          <label class="input-label" for="nombre">Nombre completo</label>
          <input class="input" type="text" id="nombre" name="nombre"
                 placeholder="Tu nombre" required autocomplete="name"
                 value="<?php echo htmlspecialchars($nombre); ?>"/>
          <span class="input-error-msg" id="nombreErr"></span>
        </div>

        <!-- Campo email — se repobla con $email si el registro falló -->
        <div class="input-group" style="margin-top:1.1rem">
          <label class="input-label" for="email">Correo electrónico</label>
          <input class="input" type="email" id="email" name="email"
                 placeholder="tu@correo.com" required autocomplete="email"
                 value="<?php echo htmlspecialchars($email); ?>"/>
          <span class="input-error-msg" id="emailErr"></span>
        </div>

        <!-- Campo contraseña con toggle de visibilidad y barra de fortaleza -->
        <div class="input-group" style="margin-top:1.1rem">
          <label class="input-label" for="password">Contraseña</label>
          <!-- input-pass-wrap necesita position:relative para el botón toggle -->
          <div class="input-pass-wrap">
            <input class="input" type="password" id="password" name="password"
                   placeholder="Mínimo 8 caracteres" required autocomplete="new-password"/>
            <!-- data-target indica a register.js qué input controla este botón -->
            <button type="button" class="pass-toggle" data-target="password"
                    aria-label="Mostrar contraseña">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <!-- Barra y etiqueta de fortaleza — actualizadas en tiempo real por register.js -->
          <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
          <span class="strength-label" id="strengthLabel"></span>
          <span class="input-error-msg" id="passErr"></span>
        </div>

        <!-- Campo de confirmación de contraseña -->
        <div class="input-group" style="margin-top:1.1rem">
          <label class="input-label" for="confirm">Confirmar contraseña</label>
          <div class="input-pass-wrap">
            <input class="input" type="password" id="confirm" name="confirm"
                   placeholder="Repite tu contraseña" required autocomplete="new-password"/>
            <button type="button" class="pass-toggle" data-target="confirm"
                    aria-label="Mostrar contraseña">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <span class="input-error-msg" id="confirmErr"></span>
        </div>

        <!-- Checkbox de términos — obligatorio para poder enviar el formulario -->
        <div class="terms-row" style="margin-top:1.2rem">
          <label class="auth-check">
            <input type="checkbox" id="terms" name="terms" required/>
            <a class="auth-link" style="margin-left:.25rem">Acepta que usarás la página</a>
          </label>
          <span class="input-error-msg" id="termsErr"></span>
        </div>

        <!-- Botón de submit — JS lo deshabilita al enviar para prevenir doble clic -->
        <button type="submit" class="btn btn-primary"
                style="width:100%;margin-top:1.5rem;justify-content:center"
                id="registerBtn">
          Crear cuenta
        </button>
      </form>

      <!-- Enlace al login para usuarios que ya tienen cuenta -->
      <p class="auth-footer-txt">
        ¿Ya tienes cuenta?
        <a href="<?= $base ?>/src/auth/login/index.php" id="lg-link" class="auth-link">
          Inicia sesión
        </a>
      </p>

    </div>
  </div>
</div>

<!-- Contenedor del toast — gestionado por window.Toast en register.js -->
<div id="toast"><span class="toast-icon"></span><span class="toast-msg"></span></div>

<!-- register.js primero para que window.Toast esté disponible cuando cargue help.js -->
<script src="<?= $base ?>/src/auth/register/register.js"></script>
<script src="https://unpkg.com/intro.js/intro.js"></script>
<script src="<?= $base ?>/src/help.js"></script>
</body>
</html>
