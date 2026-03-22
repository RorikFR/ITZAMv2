<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php';
require 'autorizacion.php';      


//RBAC
requerir_roles_api(['Administrativo']); 

require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    //Validaciones de datos y sanitizacion
    $curp         = trim(strtoupper($input['curp'] ?? ''));
    $cedula       = preg_replace('/\D/', '', $input['cedula'] ?? '');
    
    // Sanitizamos texto y eliminamos espacios
    $nombre       = preg_replace('/\s+/', ' ', strip_tags(trim($input['nombre'] ?? '')));
    $apellido_p   = preg_replace('/\s+/', ' ', strip_tags(trim($input['apellido_paterno'] ?? '')));
    $apellido_m   = preg_replace('/\s+/', ' ', strip_tags(trim($input['apellido_materno'] ?? '')));
    
    $idPuesto     = filter_var($input['puesto'] ?? '', FILTER_VALIDATE_INT);
    $idUnidad     = filter_var($input['unidad'] ?? '', FILTER_VALIDATE_INT);
    
    $idEspecialidad = !empty($input['especialidad']) ? filter_var($input['especialidad'], FILTER_VALIDATE_INT) : null;
    $cedula_esp     = !empty($input['cedula_especialidad']) ? preg_replace('/\D/', '', $input['cedula_especialidad']) : null;
    
    $email_inst     = trim($input['email_institucional'] ?? '');
    $email_personal = trim($input['email_personal'] ?? '');
    $telefono       = preg_replace('/\D/', '', $input['telefono'] ?? ''); 
    
    // Validar datos completos
    if (empty($curp) || empty($nombre) || empty($apellido_p) || empty($cedula) || !$idPuesto || !$idUnidad || empty($email_inst) || empty($email_personal) || empty($telefono)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios o la selección de catálogos es inválida."]);
        exit;
    }

    // Validaciones regex formatos
    if (!preg_match('/^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/', $curp)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Formato de CURP inválido."]); exit;
    }
    if (strlen($cedula) < 7 || strlen($cedula) > 8) {
        echo json_encode(["estatus" => "error", "mensaje" => "La Cédula Profesional debe tener 7 u 8 dígitos."]); exit;
    }
    if ($cedula_esp && (strlen($cedula_esp) < 7 || strlen($cedula_esp) > 8)) {
        echo json_encode(["estatus" => "error", "mensaje" => "La Cédula de Especialidad debe tener 7 u 8 dígitos."]); exit;
    }
    if (strlen($telefono) !== 10) {
        echo json_encode(["estatus" => "error", "mensaje" => "El teléfono celular debe contener exactamente 10 dígitos numéricos."]); exit;
    }
    if (!filter_var($email_inst, FILTER_VALIDATE_EMAIL) || !filter_var($email_personal, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["estatus" => "error", "mensaje" => "El formato de los correos electrónicos es inválido."]); exit;
    }

    try {
        //Evitar registros duplicados
        $stmtCheck = $pdo->prepare("SELECT idPersonal FROM registro_personal WHERE curp = :curp OR cedula = :cedula LIMIT 1");
        $stmtCheck->execute([
            'curp' => $curp,
            'cedula' => $cedula
        ]);
        
        if ($stmtCheck->fetch()) {
            echo json_encode(["estatus" => "error", "mensaje" => "Ya existe un miembro del personal registrado con este CURP o Cédula Profesional."]);
            exit;
        }

        // Insertar datos
        $stmt = $pdo->prepare("
            INSERT INTO registro_personal (
                curp, cedula, nombre, apellido_p, apellido_m, 
                idPuesto, idEspecialidad, cedula_esp, idUnidad, 
                email_inst, email_personal, telefono
            ) VALUES (
                :curp, :cedula, :nombre, :apellido_p, :apellido_m, 
                :idPuesto, :idEspecialidad, :cedula_esp, :idUnidad, 
                :email_inst, :email_personal, :telefono
            )
        ");
        
        $stmt->execute([
            'curp'           => $curp,
            'cedula'         => $cedula,
            'nombre'         => $nombre,
            'apellido_p'     => $apellido_p,
            'apellido_m'     => empty($apellido_m) ? null : $apellido_m,
            'idPuesto'       => $idPuesto,
            'idEspecialidad' => $idEspecialidad,
            'cedula_esp'     => $cedula_esp,
            'idUnidad'       => $idUnidad,
            'email_inst'     => $email_inst,
            'email_personal' => $email_personal,
            'telefono'       => $telefono
        ]);
        
        echo json_encode(["estatus" => "exito", "mensaje" => "Personal médico registrado exitosamente en el sistema."]);
        
    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Ocurrió un error interno al registrar al personal. Verifique los datos."]);
    }
    exit;
} else {
    echo json_encode(["estatus" => "error", "mensaje" => "Método no permitido."]);
    exit;
}
?>