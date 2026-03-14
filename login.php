<?php
// 1. Iniciamos sesión de forma segura
session_start();

// Forzamos que la respuesta siempre sea JSON
header('Content-Type: application/json; charset=utf-8');

// require 'conexion.php'; // Tu archivo de conexión PDO a la base de datos
require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recibimos los datos en formato JSON desde el frontend
    $input = json_decode(file_get_contents('php://input'), true);
    
    $identificador = trim($input['username'] ?? ''); // Puede ser el usuario o el correo
    $passwordInput = $input['password'] ?? '';
    $captchaResponse = $input['g-recaptcha-response'] ?? '';

    // --- ESCUDO 1: VALIDACIÓN DE DATOS VACÍOS ---
    if (empty($identificador) || empty($passwordInput)) {
        http_response_code(400); // 400 Bad Request
        echo json_encode(["error" => "Por favor, ingresa tu usuario y contraseña."]);
        exit;
    }

    if (empty($captchaResponse)) {
        http_response_code(400);
        echo json_encode(["error" => "Por favor, completa la verificación de seguridad (reCAPTCHA)."]);
        exit;
    }

    // --- ESCUDO 2: VERIFICACIÓN DE reCAPTCHA CON GOOGLE ---
    // ¡IMPORTANTE! Reemplaza esto con tu CLAVE SECRETA (la que te da Google, NO la pública del HTML)
    $recaptchaSecret = '6LcjXlcsAAAAAMy3sotWNsA6qOclH35BamaCfaui'; 
    $verifyUrl = "https://www.google.com/recaptcha/api/siteverify";
    
    // Armamos la petición hacia los servidores de Google
    $data = [
        'secret' => $recaptchaSecret,
        'response' => $captchaResponse
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    $context  = stream_context_create($options);
    $verifyResponse = file_get_contents($verifyUrl, false, $context);
    $responseData = json_decode($verifyResponse);

    if (!$responseData->success) {
        http_response_code(401); // 401 Unauthorized
        echo json_encode(["error" => "Validación de seguridad fallida. Intenta nuevamente."]);
        exit;
    }

// --- ESCUDO 3: BÚSQUEDA Y BCRYPT (SOLO USUARIO) ---
    try {
        // Buscamos estrictamente por la columna nombre_usuario
        $sql = "SELECT idUsuario, idPersonal, nombre_usuario, rol, contrasena, estatus, foto_perfil
                FROM usuarios_sistema 
                WHERE nombre_usuario = :usuario 
                LIMIT 1";
                
        $stmt = $pdo->prepare($sql);
        
        // Pasamos el parámetro una sola vez. ¡Problema resuelto!
        $stmt->execute([
            'usuario' => $identificador 
        ]);
        
        $usuarioDb = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificamos si el usuario existe Y si la contraseña coincide con el hash
        if ($usuarioDb && password_verify($passwordInput, $usuarioDb['contrasena'])) {
            
            // Verificamos si la cuenta está activa
            if ($usuarioDb['estatus'] !== 'Activo') {
                http_response_code(403); // 403 Forbidden
                echo json_encode(["error" => "Tu cuenta se encuentra inactiva o suspendida. Contacta al administrador."]);
                exit;
            }

            // ¡LOGIN EXITOSO!
            // Regeneramos el ID de sesión para prevenir robos de sesión (Session Fixation)
            session_regenerate_id(true);
            
            // Guardamos las credenciales en la memoria del servidor para validar las demás páginas
            $_SESSION['idUsuario'] = $usuarioDb['idUsuario'];
            $_SESSION['idPersonal'] = $usuarioDb['idPersonal']; // Si es SuperAdmin, esto será NULL
            $_SESSION['nombre_usuario'] = $usuarioDb['nombre_usuario'];
            $_SESSION['rol'] = $usuarioDb['rol']; 
            $_SESSION['foto_perfil'] = $usuarioDb['foto_perfil'];
            
            // Devolvemos la ruta a la que JavaScript debe redirigir al usuario
            http_response_code(200); // 200 OK
            echo json_encode([
                "redirect" => "home.php" 
            ]);
            
        } else {
            // Error genérico. No revelamos si falló el usuario o la contraseña por seguridad.
            http_response_code(401);
            echo json_encode(["error" => "Usuario o contraseña incorrectos."]);
        }

    } catch (PDOException $e) {
        http_response_code(500); 
        // ⚠️ ALERTA: Esto mostrará el error real de SQL en la pantalla
        echo json_encode(["error" => "Error SQL: " . $e->getMessage()]);
    } catch (Error $e) {
        // Esto atrapará errores fatales de PHP (como llamar a una variable que no existe)
        http_response_code(500); 
        echo json_encode(["error" => "Error PHP: " . $e->getMessage()]);
    }
    
    exit;
}
?>