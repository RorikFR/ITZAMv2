<?php
// 1. Iniciamos la sesión ANTES de enviar cualquier cabecera
session_start();
header('Content-Type: application/json');

// DEV ONLY - quitar en prod.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. ESCUDO DE SESIÓN: Si no hay un usuario logueado, lo pateamos
if (!isset($_SESSION['idUsuario'])) {
    http_response_code(401);
    echo json_encode(["error" => "No tienes autorización. Inicia sesión primero."]);
    exit;
}

require 'db_conn.php';

// Atrapamos el ID real del usuario desde la memoria del servidor
$idLogueado = $_SESSION['idUsuario'];

$metodo = $_SERVER['REQUEST_METHOD'];

// --- LÓGICA DE LECTURA DE MI CUENTA (GET) ---
if ($metodo === 'GET') {
    
    // Agregamos el WHERE para que solo traiga SU propia información
    $sql = "SELECT 
                idUsuario, 
                nombre_usuario AS 'Nombre de usuario', 
                email AS 'Email', 
                fecha_creacion AS 'Fecha de registro' 
            FROM usuarios_sistema 
            WHERE idUsuario = :id"; 

    try {
        $stmt = $pdo->prepare($sql);
        // Usamos la variable de sesión
        $stmt->execute(['id' => $idLogueado]); 
        
        // Usamos fetchAll para que devuelva un arreglo (aunque sea de 1 elemento), 
        // así no se rompe tu forEach en el JavaScript del frontend.
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($datos);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error al obtener la información de tu cuenta."]);
    }
    exit;
}
 
// --- LÓGICA DE EDICIÓN DE MI CUENTA (POST) ---
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $accion = $input['accion'] ?? '';

    // --- EDICIÓN DE CORREO ---
    if ($accion === 'editar') {
        $email = trim($input['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["estatus" => "error", "mensaje" => "Formato de correo electrónico inválido."]);
            exit;
        }

        try {
            $sql = "UPDATE usuarios_sistema SET email = :email WHERE idUsuario = :id";
            $stmt = $pdo->prepare($sql);
            
            // Usamos $idLogueado en lugar del ID del JSON
            $stmt->execute(['email' => $email, 'id' => $idLogueado]);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Tu correo electrónico ha sido actualizado."]);
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                echo json_encode(["estatus" => "error", "mensaje" => "Ese correo ya está en uso por otra persona."]);
            } else {
                echo json_encode(["estatus" => "error", "mensaje" => "Error al actualizar el correo."]);
            }
        }
        exit;
    }

    // --- CAMBIO DE CONTRASEÑA ---
    if ($accion === 'cambiar_password') {
        $passwordActual = trim($input['passwordActual'] ?? '');
        $passwordNuevo = trim($input['passwordNuevo'] ?? '');

        if ($passwordActual === '' || $passwordNuevo === '') {
            echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos de la contraseña."]);
            exit;
        }

        try {
            // Buscamos la contraseña actual usando el ID de la sesión
            $stmt = $pdo->prepare("SELECT contrasena FROM usuarios_sistema WHERE idUsuario = :id LIMIT 1");
            $stmt->execute(['id' => $idLogueado]);
            $usuarioDb = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuarioDb || !password_verify($passwordActual, $usuarioDb['contrasena'])) {
                echo json_encode(["estatus" => "error", "mensaje" => "La contraseña actual es incorrecta."]);
                exit;
            }

            $nuevoHash = password_hash($passwordNuevo, PASSWORD_BCRYPT);

            // Actualizamos la contraseña del usuario logueado
            $stmtUpdate = $pdo->prepare("UPDATE usuarios_sistema SET contrasena = :hash WHERE idUsuario = :id");
            $stmtUpdate->execute(['hash' => $nuevoHash, 'id' => $idLogueado]);

            echo json_encode(["estatus" => "exito", "mensaje" => "Tu contraseña ha sido actualizada correctamente."]);
            
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error interno al actualizar la contraseña."]);
        }
        exit;
    }
}
?>