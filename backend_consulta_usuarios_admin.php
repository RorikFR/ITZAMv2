<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';      

//RBAC
requerir_roles_api(['Administrador']); 

//Conexion a DB
require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$mi_id_sesion = $_SESSION['idUsuario'] ?? 0; //Obtener id de usuario actual

//Carga de datos
if ($metodo === 'GET') {
    $busqueda = filter_var(trim($_GET['q'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    $sql = "SELECT 
                idUsuario, 
                nombre_usuario AS 'Nombre de usuario', 
                email AS 'Email', 
                estatus AS 'Estatus', 
                rol AS 'Rol', 
                DATE_FORMAT(fecha_creacion, '%d/%m/%Y') AS 'Fecha de creación', 
                DATE_FORMAT(fecha_suspension, '%d/%m/%Y') AS 'Fecha de suspensión' 
            FROM usuarios_sistema "; 

    try {
        if ($busqueda !== '') {
            $sql .= " WHERE nombre_usuario LIKE :busqueda ORDER BY idUsuario DESC LIMIT 200";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['busqueda' => '%' . $busqueda . '%']);
        } else {
            $sql .= " ORDER BY idUsuario DESC LIMIT 200";
            $stmt = $pdo->query($sql);
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error interno al obtener la lista de usuarios."]);
    }
    exit;
}

//Editar y eliminar datos
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idUsuario = filter_var($input['idUsuario'] ?? 0, FILTER_VALIDATE_INT); 

    if ($idUsuario <= 0) {
        echo json_encode(["estatus" => "error", "mensaje" => "ID de usuario no válido."]);
        exit;
    }

    //Suspender usuario (soft-delete)
    if ($accion === 'suspender') {
        // Bloquear suspension del propio usuario (self-lockout)
        if ($idUsuario == $mi_id_sesion) {
            echo json_encode(["estatus" => "error", "mensaje" => "Por medidas de seguridad, no puedes suspender tu propia cuenta de Administrador."]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE usuarios_sistema 
                                   SET estatus = 'Suspendido', 
                                       fecha_suspension = COALESCE(fecha_suspension, NOW()) 
                                   WHERE idUsuario = :id");
            $stmt->execute(['id' => $idUsuario]);
            echo json_encode(["estatus" => "exito", "mensaje" => "Usuario suspendido correctamente."]);
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error interno al suspender usuario."]);
        }
        exit; 
    }

    // Modificar contraseña usuario
    if ($accion === 'cambiar_password_unico') {
        $nuevaPass = $input['nueva_pass'] ?? '';
        
        if (strlen($nuevaPass) < 8) {
            echo json_encode(["estatus" => "error", "mensaje" => "La nueva contraseña debe tener al menos 8 caracteres."]);
            exit;
        }

        try {
            $hash = password_hash($nuevaPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios_sistema SET contrasena = :pass WHERE idUsuario = :id");
            $stmt->execute(['pass' => $hash, 'id' => $idUsuario]);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Contraseña actualizada de forma segura."]);
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error al actualizar la contraseña."]);
        }
        exit;
    }

    // Editar perfil de usuario
    if ($accion === 'editar') {
        $email   = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $rol     = strip_tags(trim($input['rol'] ?? ''));
        $estatus = strip_tags(trim($input['estatus'] ?? ''));

        if (!$email || empty($rol) || empty($estatus)) {
            echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios o el correo es inválido."]);
            exit;
        }

        $roles_validos = ['Administrador', 'Médico', 'Enfermería', 'Administrativo'];
        if (!in_array($rol, $roles_validos) || !in_array($estatus, ['Activo', 'Suspendido'])) {
            echo json_encode(["estatus" => "error", "mensaje" => "Parámetros de rol o estatus no permitidos."]);
            exit;
        }

        // Bloquear cambio de rol de administrador
        if ($idUsuario == $mi_id_sesion) {
            if ($rol !== 'Administrador') {
                echo json_encode(["estatus" => "error", "mensaje" => "Debe existir por lo menos un administrador en el sistema."]); exit;
            }
            if ($estatus === 'Suspendido') {
                echo json_encode(["estatus" => "error", "mensaje" => "No puedes suspender tu propia cuenta desde aquí."]); exit;
            }
        }

        try {
            if ($rol === 'Administrador') {
                // Validar si existe otro administrador
                $stmtAdmin = $pdo->prepare("SELECT COUNT(*) FROM usuarios_sistema WHERE rol = 'Administrador' AND idUsuario != :id");
                $stmtAdmin->execute(['id' => $idUsuario]);
                if ($stmtAdmin->fetchColumn() >= 1) {
                    echo json_encode(["estatus" => "error", "mensaje" => "Límite alcanzado: Ya existe otra cuenta de Administrador en el sistema."]);
                    exit;
                }
            }

            // Actualizar datos
            $sql = "UPDATE usuarios_sistema SET 
                        email = :email, 
                        rol = :rol, 
                        estatus = :estatus,
                        fecha_suspension = CASE 
                            WHEN :est_check1 = 'Activo' THEN NULL 
                            WHEN :est_check2 = 'Suspendido' THEN COALESCE(fecha_suspension, NOW()) 
                            ELSE fecha_suspension 
                        END
                    WHERE idUsuario = :id";
            
            $params = [
                'email'      => $email,
                'rol'        => $rol,
                'estatus'    => $estatus,
                'est_check1' => $estatus,
                'est_check2' => $estatus,
                'id'         => $idUsuario
            ];

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Perfil de usuario actualizado correctamente."]);
            
        } catch (PDOException $e) {
            //Email duplicado
            if ($e->getCode() == 23000) { 
                echo json_encode(["estatus" => "error", "mensaje" => "Este correo ya está asignado a otro usuario en el sistema."]);
            } else {
                echo json_encode(["estatus" => "error", "mensaje" => "Ocurrió un error en la base de datos."]);
            }
        }
        exit;
    }
}
?>