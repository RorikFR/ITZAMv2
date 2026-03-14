<?php
header('Content-Type: application/json; charset=utf-8');

require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = trim($input['email'] ?? '');

    // Validación estricta de correo del lado del servidor usando el filtro nativo de PHP
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Formato de correo inválido."]);
        exit;
    }

    try {
        // 1. Buscamos al usuario por su correo electrónico
        $stmt = $pdo->prepare("SELECT nombre_usuario, email FROM usuarios_sistema WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $usuarioDb = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuarioDb) {
            
            // 2. Preparamos el correo electrónico para el Administrador de ITZAM
            $correoAdmin = "admin@itzam.com.mx"; 
            $asunto = "ITZAM: Solicitud de restablecimiento de contraseña";
            
            // Cuerpo del correo (Más completo gracias a tu sugerencia)
            $mensaje = "Hola Administrador,\n\n";
            $mensaje .= "Se ha solicitado un restablecimiento de contraseña desde el portal de inicio de sesión.\n\n";
            $mensaje .= "--- DATOS DE LA CUENTA ---\n";
            $mensaje .= "Nombre de usuario: " . $usuarioDb['nombre_usuario'] . "\n";
            $mensaje .= "Correo registrado: " . $usuarioDb['email'] . "\n";
            $mensaje .= "--------------------------\n\n";
            $mensaje .= "Por favor, ingresa al panel de Administración de ITZAM para asignarle una nueva contraseña temporal.\n\n";
            $mensaje .= "Sistema ITZAM.";
            
            $headers = "From: noreply@itzam-sistema.com" . "\r\n" .
                       "Reply-To: noreply@itzam-sistema.com" . "\r\n" .
                       "X-Mailer: PHP/" . phpversion();

            // 3. Enviamos el correo
            $correoEnviado = mail($correoAdmin, $asunto, $mensaje, $headers);

            if ($correoEnviado) {
                echo json_encode(["estatus" => "exito", "mensaje" => "Se envió tu solicitud al administrador. Tu contraseña será restablecida en breve."]);
            } else {
                echo json_encode(["estatus" => "error", "mensaje" => "No se pudo notificar al administrador en este momento. Intenta más tarde."]);
            }
            
        } else {
            // Mensaje de rechazo si el correo no existe en la base de datos
            echo json_encode(["estatus" => "error", "mensaje" => "No encontramos ninguna cuenta asociada a este correo electrónico."]);
        }

    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error interno en la base de datos."]);
    }
    
    exit;
}
?>