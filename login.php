<?php
// 1. Iniciamos sesión de forma segura
session_start();

// Forzamos que la respuesta siempre sea JSON
header('Content-Type: application/json; charset=utf-8');

// require 'conexion.php'; // Tu archivo de conexión PDO a la base de datos
require 'db_conn.php';

// Establecer la zona horaria (Ajusta esto según la configuración de tu servidor si es necesario)
date_default_timezone_set('America/Mexico_City');

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
    // ¡IMPORTANTE! Reemplaza esto con tu CLAVE SECRETA
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

// --- ESCUDO 3: BÚSQUEDA Y BCRYPT CON PROTECCIÓN DE FUERZA BRUTA ---
    try {
        // Renombramos los parámetros a :usuario1 y :usuario2
        $sql = "SELECT u.idUsuario, u.idPersonal, u.nombre_usuario, u.rol, u.contrasena, u.estatus, u.foto_perfil, u.intentos_fallidos, u.bloqueado_hasta, p.idUnidad
                FROM usuarios_sistema u
                LEFT JOIN registro_personal p ON u.idPersonal = p.idPersonal
                WHERE u.nombre_usuario = :usuario1 OR u.email = :usuario2 
                LIMIT 1";
                
        $stmt = $pdo->prepare($sql);
        
        // Le pasamos el mismo $identificador a ambos parámetros
        $stmt->execute([
            'usuario1' => $identificador,
            'usuario2' => $identificador
        ]);
        
        $usuarioDb = $stmt->fetch(PDO::FETCH_ASSOC);


        // Si el usuario existe, procesamos las validaciones
        if ($usuarioDb) {
            
            // --- VERIFICACIÓN DE BLOQUEO TEMPORAL ---
            if ($usuarioDb['bloqueado_hasta'] !== null) {
                $fecha_bloqueo = new DateTime($usuarioDb['bloqueado_hasta']);
                $ahora = new DateTime();

                if ($ahora < $fecha_bloqueo) {
                    $diferencia = $ahora->diff($fecha_bloqueo);
                    $minutos_restantes = ($diferencia->i) + ($diferencia->h * 60) + 1;
                    
                    http_response_code(401);
                    echo json_encode(["error" => "Demasiados intentos. Cuenta bloqueada por seguridad. Intenta en {$minutos_restantes} minuto(s)."]);
                    exit;
                } else {
                    // El castigo ya pasó. Reseteamos la cuenta para darle una nueva oportunidad.
                    $stmtReset = $pdo->prepare("UPDATE usuarios_sistema SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE idUsuario = :id");
                    $stmtReset->execute(['id' => $usuarioDb['idUsuario']]);
                    $usuarioDb['intentos_fallidos'] = 0;
                }
            }

            // --- VERIFICAR CONTRASEÑA ---
            if (password_verify($passwordInput, $usuarioDb['contrasena'])) {
                
                // Verificamos si la cuenta está suspendida por un administrador
                if ($usuarioDb['estatus'] !== 'Activo') {
                    http_response_code(403); // 403 Forbidden
                    echo json_encode(["error" => "Tu cuenta se encuentra inactiva o suspendida. Contacta al administrador."]);
                    exit;
                }

                // --- LOGIN EXITOSO ---
                // Limpiamos los intentos fallidos
                $stmtExito = $pdo->prepare("UPDATE usuarios_sistema SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE idUsuario = :id");
                $stmtExito->execute(['id' => $usuarioDb['idUsuario']]);

                session_regenerate_id(true);
                
                $_SESSION['idUsuario'] = $usuarioDb['idUsuario'];
                $_SESSION['idPersonal'] = $usuarioDb['idPersonal'];
                $_SESSION['nombre_usuario'] = $usuarioDb['nombre_usuario'];
                $_SESSION['rol'] = $usuarioDb['rol']; 
                $_SESSION['foto_perfil'] = $usuarioDb['foto_perfil'];
                $_SESSION['idUnidad'] = $usuarioDb['idUnidad'];
                
                http_response_code(200); // 200 OK
                echo json_encode([
                    "redirect" => "home.php" 
                ]);
                
            } else {
                // --- FALLO LA CONTRASEÑA ---
                $intentos_actuales = $usuarioDb['intentos_fallidos'] + 1;
                
                if ($intentos_actuales >= 3) {
                    // Bloqueo de 15 minutos
                    $stmtLock = $pdo->prepare("UPDATE usuarios_sistema SET intentos_fallidos = :intentos, bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE idUsuario = :id");
                    $stmtLock->execute(['intentos' => $intentos_actuales, 'id' => $usuarioDb['idUsuario']]);
                    
                    http_response_code(401);
                    echo json_encode(["error" => "Demasiados intentos fallidos. Por seguridad, tu cuenta ha sido bloqueada por 15 minutos."]);
                } else {
                    // Solo sumamos el error
                    $stmtFail = $pdo->prepare("UPDATE usuarios_sistema SET intentos_fallidos = :intentos WHERE idUsuario = :id");
                    $stmtFail->execute(['intentos' => $intentos_actuales, 'id' => $usuarioDb['idUsuario']]);
                    
                    http_response_code(401);
                    echo json_encode(["error" => "Usuario o contraseña incorrectos."]);
                }
            }
        } else {
            // Error genérico si el usuario no existe
            http_response_code(401);
            echo json_encode(["error" => "Usuario o contraseña incorrectos."]);
        }

    } catch (PDOException $e) {
        http_response_code(500); 
        // 🛡️ ESCUDO: Mensaje genérico para ocultar estructura de BD
        // En producción, usa error_log($e->getMessage()); para guardar el error real en tu servidor.
        echo json_encode(["error" => "Error interno del servidor. Contacte a soporte técnico."]);
    } catch (Error $e) {
        http_response_code(500); 
        // 🛡️ ESCUDO: Mensaje genérico para ocultar fallos de PHP
        echo json_encode(["error" => "Error interno del sistema. Contacte a soporte técnico."]);
    }
    
    exit;
}
?>