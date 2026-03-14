<?php
session_start();
// Vaciamos todas las variables de sesión
$_SESSION = [];
// Destruimos la sesión en el servidor
session_destroy();
// Redirigimos al inicio de sesión
header("Location: index.php"); 
exit;
?>