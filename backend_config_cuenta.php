<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'seguridad_backend.php';
require 'autorizacion.php';     


//RBAC
requerir_roles_api(['Administrador', 'Administrativo', 'Médico', 'Enfermería']);

//Conexion a DB
require 'db_conn.php';

//Obtener idUsuario logeado
$idLogueado = $_SESSION['idUsuario'];

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    
    $sql = "SELECT 
                idUsuario, 
                nombre_usuario AS 'Nombre de usuario', 
                email AS 'Email', 
                fecha_creacion AS 'Fecha de registro' 
            FROM usuarios_sistema 
            WHERE idUsuario = :id"; 

    try {
        $stmt = $pdo->prepare($sql);

        $stmt->execute(['id' => $idLogueado]); 
        
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($datos);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error al obtener la información de tu cuenta."]);
    }
    exit;
}
 
//Editar registros
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $accion = $input['accion'] ?? '';

    // Editar correo
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

    // Modificar contraseña 
    if ($accion === 'cambiar_password') {
        $passwordActual = trim($input['passwordActual'] ?? '');
        $passwordNuevo = trim($input['passwordNuevo'] ?? '');

        if ($passwordActual === '' || $passwordNuevo === '') {
            echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos de la contraseña."]);
            exit;
        }

        try {
            // Obtener contraseña actual con idUsuario
            $stmt = $pdo->prepare("SELECT contrasena FROM usuarios_sistema WHERE idUsuario = :id LIMIT 1");
            $stmt->execute(['id' => $idLogueado]);
            $usuarioDb = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuarioDb || !password_verify($passwordActual, $usuarioDb['contrasena'])) {
                echo json_encode(["estatus" => "error", "mensaje" => "La contraseña actual es incorrecta."]);
                exit;
            }

            $nuevoHash = password_hash($passwordNuevo, PASSWORD_BCRYPT);

            // Actualizamos la contraseña
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