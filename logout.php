<?php
session_start();

// Si existe sesión, destruirla
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}
session_destroy();

// Redirigir a la página de inicio (antes era index.jsp)
header('Location: index.php');
exit;

