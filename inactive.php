<?php
// Iniciamos sesión si no existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Obligamos al navegador a no guardar copias locales de las vistas protegidas.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verificar si el usuario realmente está logueado
if (!isset($_SESSION['idUsuario'])) {
    header("Location: index.php?error=nologin");
    exit;
}

// Configuración de tiempo máximo de inactividad (Ej: 5 minutos = 300 segundos)
$tiempo_limite = 30000; 

// Verificar la inactividad
if (isset($_SESSION['ultima_actividad'])) {
    $tiempo_inactivo = time() - $_SESSION['ultima_actividad'];
    
    if ($tiempo_inactivo > $tiempo_limite) {
        // Tiempo límite. Destruir sesión
        session_unset();
        session_destroy();
        
        // Redirigimos al login con bandera timeout
        header("Location: index.php?timeout=1");
        exit;
    }
}

//Actualizar marca de tiempo
$_SESSION['ultima_actividad'] = time();
?>