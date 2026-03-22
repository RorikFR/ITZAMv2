<?php
// Forzar respuesta JSON
header('Content-Type: application/json; charset=utf-8');

// Iniciamos la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificamos si el usuario existe en la sesión
if (!isset($_SESSION['idUsuario'])) {
    http_response_code(401); 
    echo json_encode([
        "estatus" => "error", 
        "mensaje" => "Sesión expirada o no autorizada.",
        "codigo" => "SESION_INVALIDA"
    ]);
    exit; 
}

// Límite de inactividad
$tiempo_limite = 30000; 
if (isset($_SESSION['ultima_actividad'])) {
    $tiempo_inactivo = time() - $_SESSION['ultima_actividad'];
    if ($tiempo_inactivo > $tiempo_limite) {
        session_unset();
        session_destroy();
        
        http_response_code(401);
        echo json_encode([
            "estatus" => "error", 
            "mensaje" => "Sesión expirada por inactividad.",
            "codigo" => "TIMEOUT"
        ]);
        exit;
    }
}

// Actualizamos el reloj de actividad
$_SESSION['ultima_actividad'] = time();
?>