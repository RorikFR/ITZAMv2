<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';     

// RBAC
requerir_roles_api(['Administrativo']); 

require 'db_conn.php';

// Manejo de solicitudes
$metodo = $_SERVER['REQUEST_METHOD'];


if ($metodo === 'GET') {
    // Sanitizacion de datos
    $busqueda = isset($_GET['q']) ? filter_var(trim($_GET['q']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    
    if($busqueda) {
        $sql = "SELECT 
                    idPersonal, 
                    nombre, 
                    apellido_p AS apellido_paterno, 
                    apellido_m AS apellido_materno, 
                    cedula AS cedula_profesional, 
                    email_inst AS email_institucional, 
                    telefono AS telefono_celular
                FROM registro_personal
                WHERE cedula LIKE :q
                ORDER BY idPersonal DESC";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['q' => "%$busqueda%"]);
    } else {
        $stmt = $pdo->query("SELECT 
                                idPersonal, 
                                nombre, 
                                apellido_p AS apellido_paterno, 
                                apellido_m AS apellido_materno, 
                                cedula AS cedula_profesional, 
                                email_inst AS email_institucional, 
                                telefono AS telefono_celular
                            FROM registro_personal
                            ORDER BY idPersonal DESC LIMIT 100");
    }
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

//Editar y eliminar registros
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idPersonal = intval($input['idPersonal'] ?? 0); 

    //Eliminar registras
    if ($accion === 'eliminar' && $idPersonal > 0) {
        
        try {
            $stmt = $pdo->prepare("DELETE FROM registro_personal WHERE idPersonal = :idPersonal");
            $stmt->execute(['idPersonal' => $idPersonal]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Personal de salud eliminado exitosamente."
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "No se puede eliminar este personal porque ya tiene consultas o registros asociados en el sistema."
            ]);
        }
        exit; 
    }

    //Editar registros
    if ($accion === 'editar' && $idPersonal > 0) {
        
        //Sanitizar datos 
        $email_institucional = trim($input['email_institucional'] ?? '');
        $telefono_celular = preg_replace('/\D/', '', $input['telefono_celular'] ?? ''); 
        
        // Validar formato email
        if (!empty($email_institucional) && !filter_var($email_institucional, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "El formato del correo electrónico institucional es inválido."
            ]);
            exit;
        }

        if (strlen($telefono_celular) !== 10) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "El teléfono celular debe contener exactamente 10 dígitos numéricos."
            ]);
            exit;
        }
        
        try {
            // Actualizar tabla
            $stmt = $pdo->prepare("UPDATE registro_personal 
                                    SET 
                                        email_inst = :email_institucional,
                                        telefono = :telefono_celular
                                    WHERE idPersonal = :idPersonal");
            
            $stmt->execute([
                'email_institucional' => $email_institucional,
                'telefono_celular'    => $telefono_celular,
                'idPersonal'          => $idPersonal
            ]);
            
            // Obtener datos
            $sqlObtener = "SELECT 
                                idPersonal, 
                                nombre, 
                                apellido_p AS apellido_paterno, 
                                apellido_m AS apellido_materno, 
                                cedula AS cedula_profesional, 
                                email_inst AS email_institucional, 
                                telefono AS telefono_celular
                           FROM registro_personal
                           WHERE idPersonal = :idPersonal";
                           
            $stmtObtener = $pdo->prepare($sqlObtener);
            $stmtObtener->execute(['idPersonal' => $idPersonal]);
            
            $datosActualizados = $stmtObtener->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Personal de salud actualizado correctamente.",
                "datos"   => $datosActualizados
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Ocurrió un error interno al intentar guardar los cambios. Intente nuevamente."
            ]);
        }
        exit;
    }
}
?>