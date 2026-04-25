<?php
session_start();

// Detecta el prefijo de instalación a partir de la URL del script para construir
// la redirección correctamente, tanto en desarrollo local como en producción con subdirectorio
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$base   = '';
if (preg_match('#(/SurArte_Andino[^/]*)#i', $script, $m)) {
    $base = $m[1];
}

// Solo destruye la sesión si hay un usuario activo
// Esto evita errores en caso de doble clic o acceso directo a la URL sin sesión activa
if (!empty($_SESSION['user_id'])) {
    session_unset();    // Elimina todas las variables de la sesión actual
    session_destroy();  // Destruye completamente la sesión en el servidor
}

// Redirige a la página de inicio — el usuario queda como visitante anónimo
header('Location: ' . $base . '/src/inicio/inicio.php');
exit;
