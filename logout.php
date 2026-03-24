<?php
date_default_timezone_set('America/Mexico_City');

session_start();


// Vaciamos todas las variables de sesión
$_SESSION = array();


// Destruimos la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruimos el archivo físico de la sesión
session_destroy();


// Forzamos al navegador a no guardar en caché las páginas anteriores (inhabilitar botón hacia atrás)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//Redirección
$motivo = filter_var($_GET['motivo'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($motivo === 'inactividad') {
    header("Location: index.php?timeout=1");
} else {
    header("Location: index.php");
}
exit;
?>