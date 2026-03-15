<?php
session_start();

$script = $_SERVER['SCRIPT_NAME'] ?? '';
$base   = '';
if (preg_match('#(/SurArte_Andino)#i', $script, $m)) {
    $base = $m[1];
}

$error = $_SESSION['login_error'] ?? '';
$savedEmail = $_SESSION['login_email'] ?? '';
unset($_SESSION['login_error'], $_SESSION['login_email']);

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . $base . '/src/inicio/inicio.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Iniciar sesión — SurArte Andino</title>
  <link rel="stylesheet" href="<?= $base ?>/public/css/main.css"/>
  <link rel="stylesheet" href="<?= $base ?>/src/auth/login/login.css"/>
  <style>
    .auth-error {
      background: #fef2f2;
      border: 1px solid #fca5a5;
      color: #b91c1c;
      border-radius: 8px;
      padding: .75rem 1rem;
      font-size: .9rem;
      margin-bottom: 1.2rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }
    .auth-error svg { flex-shrink: 0; }
    .input--error { border-color: #f87171 !important; }
  </style>
</head>
<body class="auth-body">
<div class="auth-layout">

  <div class="auth-visual">
    <div class="auth-visual-content">
      <div class="auth-brand">SurArte<br><em>Andino</em></div>
      <p class="auth-visual-sub">Arte y cultura del sur de Colombia</p>
    </div>
  </div>

  <div class="auth-panel">
    <div class="auth-form-wrap">
      <div class="auth-header">
        <div class="eyebrow">Bienvenido de vuelta</div>
        <h1 class="auth-title">Iniciar <em>sesión</em></h1>
      </div>

      <?php if ($error): ?>
        <div class="auth-error" id="serverError">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form id="loginForm" action="login.php" method="post" novalidate>
        <div class="input-group">
          <label class="input-label" for="email">Correo electrónico</label>
          <input class="input <?php echo $error ? 'input--error' : ''; ?>"
                 type="email" id="email" name="email"
                 placeholder="tu@correo.com" required
                 value="<?php echo htmlspecialchars($savedEmail); ?>"/>
          <span class="input-error-msg" id="emailErr"></span>
        </div>

        <div class="input-group" style="margin-top:1.2rem">
          <label class="input-label" for="password">Contraseña</label>
          <div style="position:relative">
            <input class="input" type="password" id="password" name="password"
                   placeholder="••••••••" required/>
            <button type="button" id="togglePass"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:inherit;padding:0">
              <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <span class="input-error-msg" id="passErr"></span>
        </div>

        <div class="auth-meta">
          <label class="auth-check">
            <input type="checkbox" id="remember"/> Recordarme
          </label>
          <a class="auth-link" onclick="Toast.show('Recuperar contraseña próximamente')">
            ¿Olvidaste tu contraseña?
          </a>
        </div>

        <button type="submit" class="btn btn-primary"
                style="width:100%;margin-top:1.5rem;justify-content:center" id="loginBtn">
          Iniciar sesión
        </button>
      </form>

      <div class="auth-divider"><span>o continúa con</span></div>
      <div class="social-auth-row">
        <button type="button" class="btn btn-outline social-auth-btn"
                onclick="Toast.show('Google OAuth próximamente')">
          <svg width="18" height="18" viewBox="0 0 24 24">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
          Google
        </button>
      </div>

      <p class="auth-footer-txt">
        ¿No tienes cuenta?
        <a href="<?= $base ?>/src/auth/register/index.php" class="auth-link">Regístrate aquí</a>
      </p>
    </div>
  </div>
</div>

<div id="toast"><span class="toast-icon"></span><span class="toast-msg"></span></div>
<script src="<?= $base ?>/src/auth/login/login.js"></script>
</body>
</html>