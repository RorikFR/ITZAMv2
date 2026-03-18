<?php
session_start();
header('Content-Type: application/json');

// DEV ONLY - quitar en prod.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// --- LÓGICA DE LECTURA (GET) ---
if ($metodo === 'GET') {
    $busqueda = $_GET['q'] ?? '';
    
    $sql = "SELECT 
                idUsuario, 
                nombre_usuario AS 'Nombre de usuario', 
                email AS 'Email', 
                estatus AS 'Estatus', 
                rol AS 'Rol', 
                fecha_creacion AS 'Fecha de creación', 
                fecha_suspension AS 'Fecha de suspensión' 
            FROM usuarios_sistema "; 

    if ($busqueda !== '') {
        $sql .= " WHERE nombre_usuario LIKE :busqueda ";
    }
    
    $sql .= " ORDER BY idUsuario DESC"; 

    try {
        $stmt = $pdo->prepare($sql);
        if ($busqueda !== '') {
            $stmt->execute(['busqueda' => '%' . $busqueda . '%']);
        } else {
            $stmt->execute();
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error al obtener la lista de usuarios.", "detalle" => $e->getMessage()]);
    }
    exit;
}

// --- LÓGICA DE EDICIÓN Y SUSPENSIÓN (POST) ---
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idUsuario = filter_var($input['idUsuario'] ?? 0, FILTER_VALIDATE_INT); 

    if ($idUsuario <= 0) {
        echo json_encode(["estatus" => "error", "mensaje" => "ID de usuario no válido."]);
        exit;
    }

    // --- 1. SUSPENSIÓN RÁPIDA ---
    if ($accion === 'suspender') {
        try {
            $stmt = $pdo->prepare("UPDATE usuarios_sistema 
                                   SET estatus = 'Suspendido', 
                                       fecha_suspension = COALESCE(fecha_suspension, NOW()) 
                                   WHERE idUsuario = :id");
            $stmt->execute(['id' => $idUsuario]);
            echo json_encode(["estatus" => "exito", "mensaje" => "Usuario suspendido correctamente."]);
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error al suspender usuario."]);
        }
        exit; 
    }

    // --- 2. EDICIÓN COMPLETA (INCLUYE BCRYPT) ---
    if ($accion === 'editar') {
        // --- PASO 1 Y 2: TIPOS Y SANITIZACIÓN ---
        $email   = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $rol     = strip_tags(trim($input['rol'] ?? ''));
        $estatus = strip_tags(trim($input['estatus'] ?? ''));
        $nuevaPass = $input['nueva_contrasena'] ?? ''; // La recibimos plana

        if (!$email || empty($rol) || empty($estatus)) {
            echo json_encode(["estatus" => "error", "mensaje" => "Datos obligatorios inválidos o incompletos."]);
            exit;
        }

        // --- PASO 3: VALIDACIÓN DE LONGITUDES ---
        if (strlen($rol) > 50 || strlen($estatus) > 20) {
            echo json_encode(["estatus" => "error", "mensaje" => "Longitud de campos excedida."]);
            exit;
        }

        try {
            // Construcción dinámica del Query para no tocar la contraseña si no se envió una nueva
            $sql = "UPDATE usuarios_sistema SET email = :email, rol = :rol, estatus = :estatus";
            
            $params = [
                'email'   => $email,
                'rol'     => $rol,
                'estatus' => $estatus,
                'id'      => $idUsuario
            ];

            // ¿Hay nueva contraseña? Aplicamos BCrypt
            if (!empty($nuevaPass)) {
                if (strlen($nuevaPass) < 8) {
                    echo json_encode(["estatus" => "error", "mensaje" => "La nueva contraseña debe tener al menos 8 caracteres."]);
                    exit;
                }
                $sql .= ", contrasena = :pass";
                $params['pass'] = password_hash($nuevaPass, PASSWORD_DEFAULT); // 🔥 BCrypt nativo
            }

            // Lógica de fecha de suspensión automática
            $sql .= ", fecha_suspension = CASE 
                        WHEN :est_check1 = 'Activo' THEN NULL 
                        WHEN :est_check2 = 'Suspendido' THEN COALESCE(fecha_suspension, NOW()) 
                        ELSE fecha_suspension 
                      END";
            
            $params['est_check1'] = $estatus;
            $params['est_check2'] = $estatus;

            $sql .= " WHERE idUsuario = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Usuario actualizado correctamente."]);
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                echo json_encode(["estatus" => "error", "mensaje" => "Este correo ya está registrado en otra cuenta."]);
            } else {
                echo json_encode(["estatus" => "error", "mensaje" => "Error SQL: " . $e->getMessage()]);
            }
        }
        exit;
    }
}
?>