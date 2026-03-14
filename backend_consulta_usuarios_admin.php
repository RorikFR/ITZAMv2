<?php
header('Content-Type: application/json');

//DEV ONLY - quitar en prod.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

// 2. MANEJO DE SOLICITUDES
$metodo = $_SERVER['REQUEST_METHOD'];


// --- LÓGICA DE LECTURA DE USUARIOS Y BÚSQUEDA (GET) ---
if ($metodo === 'GET') {
    
    $busqueda = $_GET['q'] ?? '';
    
    $sql = "SELECT 
                idUsuario, 
                nombre_usuario AS 'Nombre de usuario', 
                email AS 'Email', 
                estatus AS 'Estatus', 
                rol AS 'Rol', 
                fecha_creacion AS 'Fecha de creación', 
                COALESCE(fecha_suspension, 'N/A') AS 'Fecha de suspensión' 
            FROM usuarios_sistema "; 

    // Búsqueda exclusiva por nombre de usuario
    if ($busqueda !== '') {
        $sql .= " WHERE nombre_usuario LIKE :busqueda ";
    }
    
    $sql .= " ORDER BY idUsuario DESC"; 

    try {
        $stmt = $pdo->prepare($sql);
        
        // Ejecutamos con un solo parámetro limpio
        if ($busqueda !== '') {
            $stmt->execute(['busqueda' => '%' . $busqueda . '%']);
        } else {
            $stmt->execute();
        }
        
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($datos);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Error al obtener la lista de usuarios.",
            "detalle" => $e->getMessage() 
        ]);
    }
    
    exit;
}
 

// --- LÓGICA DE EDICIÓN Y SUSPENSIÓN (MÓDULO DE USUARIOS) ---
if ($metodo === 'POST') {
    // Recibimos los datos JSON del frontend
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idUsuario = $input['idUsuario'] ?? 0; 

    // Verificación de seguridad básica
    if ($idUsuario <= 0) {
        echo json_encode(["estatus" => "error", "mensaje" => "ID de usuario no válido."]);
        exit;
    }

    // --- 1. LÓGICA DE SUSPENSIÓN RÁPIDA (Botón Rojo) ---
    if ($accion === 'suspender') {
        try {
            // Actualizamos el estatus y registramos la fecha. 
            // COALESCE evita sobreescribir la fecha si el usuario ya estaba suspendido.
            $stmt = $pdo->prepare("UPDATE usuarios_sistema 
                                   SET estatus = 'Suspendido', 
                                       fecha_suspension = COALESCE(fecha_suspension, NOW()) 
                                   WHERE idUsuario = :id");
            
            $stmt->execute(['id' => $idUsuario]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "El usuario ha sido suspendido correctamente."
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Ocurrió un error al intentar suspender al usuario."
            ]);
        }
        exit; 
    }

    // --- 2. LÓGICA DE EDICIÓN COMPLETA (Modal) ---
    if ($accion === 'editar') {
        
        $email = trim($input['email'] ?? '');
        $rol = $input['rol'] ?? '';
        $estatus = $input['estatus'] ?? '';

        // --- ESCUDO DE VALIDACIÓN ---
        if ($email === '' || $rol === '' || $estatus === '') {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "El correo, el rol y el estatus son obligatorios."
            ]);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "El formato del correo electrónico es inválido."
            ]);
            exit;
        }

        try {
            // Actualizamos los datos. 
            // El CASE de SQL maneja automáticamente si debemos poner NULL o la fecha actual en la suspensión.
            $sql = "UPDATE usuarios_sistema 
                    SET 
                        email = :email,
                        rol = :rol,
                        estatus = :estatus,
                        fecha_suspension = CASE 
                            WHEN :estatus_check1 = 'Activo' THEN NULL 
                            WHEN :estatus_check2 = 'Suspendido' THEN COALESCE(fecha_suspension, NOW())
                        END
                    WHERE idUsuario = :id";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                'email'          => $email,
                'rol'            => $rol,
                'estatus'        => $estatus,
                'estatus_check1' => $estatus, // Pasamos la variable para el CASE
                'estatus_check2' => $estatus, // Pasamos la variable para el CASE
                'id'             => $idUsuario
            ]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Cuenta de usuario actualizada correctamente."
            ]);
            
        } catch (PDOException $e) {
            // Protección por si intentan poner un correo que ya está en uso por otra persona
            if ($e->getCode() == 23000) { 
                echo json_encode([
                    "estatus" => "error", 
                    "mensaje" => "Ese correo electrónico ya está registrado en otra cuenta."
                ]);
            } else {
                echo json_encode([
                    "estatus" => "error", 
                    "mensaje" => "Error de base de datos al actualizar el usuario."
                ]);
            }
        }
        exit;
    }
}
?>