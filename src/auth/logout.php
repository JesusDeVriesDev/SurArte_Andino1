<?php
session_start();
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$base   = '';
if (preg_match('#(/SurArte_Andino)#i', $script, $m)) {
    $base = $m[1];
}
if (!empty($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
}
header('Location: ' . $base . '/src/inicio/inicio.php');
exit;
