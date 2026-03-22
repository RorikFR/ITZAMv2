<?php
// autorizacion.php

/**
 * 🛡️ Función para proteger VISTAS (Páginas HTML como consultas.php)
 */
function requerir_roles($roles_permitidos) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $rol_actual = $_SESSION['rol'] ?? '';

    // 🔥 LA LLAVE MAESTRA: Si es Administrador, entra a TODO sin restricciones
    if ($rol_actual === 'Administrador') {
        return; // Detiene la validación y le da paso libre
    }
    
    // Si NO es Administrador, verificamos si su rol está en la lista permitida
    if (!in_array($rol_actual, $roles_permitidos)) {
        header("Location: home.php?error=acceso_denegado");
        exit;
    }
}

/**
 * 🛑 Función para proteger BACKENDS (Archivos JSON como backend_guardar.php)
 */
function requerir_roles_api($roles_permitidos) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $rol_actual = $_SESSION['rol'] ?? '';

    // 🔥 LA LLAVE MAESTRA: La API también lo deja pasar a todo
    if ($rol_actual === 'Administrador') {
        return; 
    }
    
    // Si NO es Administrador, aplicamos la restricción estricta
    if (!in_array($rol_actual, $roles_permitidos)) {
        http_response_code(403); // 403: Prohibido
        echo json_encode([
            "estatus" => "error", 
            "mensaje" => "Acceso denegado: Tu perfil de $rol_actual no tiene privilegios para realizar esta acción."
        ]);
        exit;
    }
}
?>