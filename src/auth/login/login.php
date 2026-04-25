<?php
// Inicia la sesión si aún no hay una activa — necesario para leer y escribir $_SESSION
if (session_status() === PHP_SESSION_NONE) session_start();

// Carga la conexión a la base de datos a través del helper centralizado
require_once __DIR__ . '/../../../config/db.php';

// Solo se procesa este script cuando el formulario hace POST; cualquier otra petición redirige al formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

// Limpia espacios al inicio/fin del correo — la contraseña NO se limpia para no alterar el hash
$email    = trim($_POST['email']    ?? '');
$password =      $_POST['password'] ?? '';

// Conserva el correo en sesión para repoblarlo en el formulario si el login falla
$_SESSION['login_email'] = $email;

// Validación básica del lado del servidor: ambos campos son obligatorios
if (!$email || !$password) {
    $_SESSION['login_error'] = 'Por favor, completa todos los campos.';
    header('Location: index.php'); exit;
}

try {
    // Busca al usuario por correo; LIMIT 1 evita lecturas innecesarias si hubiera duplicados
    $stmt = db()->prepare(
        "SELECT id, nombre, password, rol, activo FROM usuarios WHERE email = ? LIMIT 1"
    );
    $stmt->execute([$email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u) {
        // El correo no existe en la base de datos
        $_SESSION['login_error'] = 'El correo electrónico no está registrado.';
    } elseif (!$u['activo']) {
        // El administrador desactivó la cuenta — no se permite el acceso
        $_SESSION['login_error'] = 'Esta cuenta ha sido desactivada.';
    } elseif (!password_verify($password, $u['password'])) {
        // La contraseña no coincide con el hash almacenado en la BD
        $_SESSION['login_error'] = 'La contraseña es incorrecta.';
    } else {
        // Credenciales correctas: limpia los datos temporales del intento fallido anterior
        unset($_SESSION['login_error'], $_SESSION['login_email']);

        // Almacena los datos esenciales del usuario en sesión para que estén disponibles en todo el sitio
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['nombre']  = $u['nombre'];
        $_SESSION['rol']     = $u['rol'];

        // Por defecto el artista no está verificado hasta confirmarlo en la BD
        $_SESSION['artista_verificado'] = false;

        // Si el usuario es artista, consulta si ya pasó el proceso de verificación por el admin
        if ($u['rol'] === 'artista') {
            $av = db()->prepare(
                "SELECT verificado FROM artistas WHERE usuario_id = ?::uuid LIMIT 1"
            );
            $av->execute([$u['id']]);
            $row = $av->fetch(PDO::FETCH_ASSOC);
            // Guarda el estado real de verificación para controlarlo en la navbar y perfil
            $_SESSION['artista_verificado'] = ($row && $row['verificado'] == true);
        }

        // Obtiene la cantidad de ítems en el carrito para mostrar el badge en la navbar
        // Se envuelve en try/catch por si la tabla aún no existe en entornos de desarrollo
        try {
            $cc = db()->prepare(
                "SELECT COALESCE(SUM(cantidad),0) FROM carrito_items WHERE usuario_id = ?::uuid"
            );
            $cc->execute([$u['id']]);
            $_SESSION['carrito_count'] = (int)$cc->fetchColumn();
        } catch (Exception $e) {
            // Si falla la consulta del carrito no se interrumpe el login, simplemente arranca en 0
            $_SESSION['carrito_count'] = 0;
        }

        // Login exitoso: redirige al dashboard principal
        header('Location: ../../inicio/inicio.php'); exit;
    }
} catch (PDOException $e) {
    // Error de base de datos — mensaje genérico al usuario para no exponer detalles internos
    $_SESSION['login_error'] = 'Error de conexión. Intenta más tarde.';
}

// Si llegó hasta aquí es porque algo falló — vuelve al formulario con el error en sesión
header('Location: index.php'); exit;
