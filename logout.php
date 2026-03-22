<?php
session_start();

// Vaciamos todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, borramos también la cookie de sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruimos la sesión.
session_destroy();

// Redirigimos al login
$motivo = $_GET['motivo'] ?? '';
if ($motivo === 'inactividad') {
    header("Location: index.php?timeout=1");
} else {
    header("Location: index.php");
}
exit;
?>