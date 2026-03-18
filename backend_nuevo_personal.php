<?php
session_start();
header('Content-Type: application/json');

// DEV ONLY - quitar en producción
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // --- ESCUDO 1 Y 2: TIPOS DE DATOS Y SANITIZACIÓN ---
    $curp         = trim(strtoupper($input['curp'] ?? ''));
    $cedula       = preg_replace('/\D/', '', $input['cedula'] ?? ''); // Extraemos solo números
    $nombre       = strip_tags(trim($input['nombre'] ?? ''));
    $apellido_p   = strip_tags(trim($input['apellido_paterno'] ?? ''));
    $apellido_m   = strip_tags(trim($input['apellido_materno'] ?? ''));
    
    $idPuesto     = filter_var($input['puesto'] ?? '', FILTER_VALIDATE_INT);
    $idUnidad     = filter_var($input['unidad'] ?? '', FILTER_VALIDATE_INT);
    
    // Opcionales
    $idEspecialidad = !empty($input['especialidad']) ? filter_var($input['especialidad'], FILTER_VALIDATE_INT) : null;
    $cedula_esp     = !empty($input['cedula_especialidad']) ? preg_replace('/\D/', '', $input['cedula_especialidad']) : null;
    
    $email_inst     = filter_var($input['email_institucional'] ?? '', FILTER_VALIDATE_EMAIL);
    $email_personal = filter_var($input['email_personal'] ?? '', FILTER_VALIDATE_EMAIL);
    $telefono       = preg_replace('/\D/', '', $input['telefono'] ?? ''); // Solo números
    
    // Validación estricta
    if (empty($curp) || empty($nombre) || empty($apellido_p) || empty($cedula) || !$idPuesto || !$idUnidad) {
        echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios o selección inválida en el formulario."]);
        exit;
    }

    // --- ESCUDO 3: LONGITUDES Y PATRONES ---
    if (!preg_match('/^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/', $curp)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Formato de CURP inválido."]); exit;
    }
    if (strlen($cedula) < 7 || strlen($cedula) > 8) {
        echo json_encode(["estatus" => "error", "mensaje" => "La Cédula Profesional debe tener 7 u 8 dígitos."]); exit;
    }
    if (!empty($telefono) && strlen($telefono) !== 10) {
        echo json_encode(["estatus" => "error", "mensaje" => "El teléfono debe ser exactamente de 10 dígitos."]); exit;
    }

    try {
        // --- ESCUDO 4: PREVENCIÓN DE DUPLICADOS ---
        $stmtCheck = $pdo->prepare("SELECT idPersonal FROM registro_personal WHERE curp = :curp OR cedula = :cedula LIMIT 1");
        $stmtCheck->execute([
            'curp' => $curp,
            'cedula' => $cedula
        ]);
        
        if ($stmtCheck->fetch()) {
            echo json_encode(["estatus" => "error", "mensaje" => "Ya existe un miembro del personal registrado con este CURP o Cédula Profesional."]);
            exit;
        }

        // --- INSERCIÓN DE DATOS ---
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
            'cedula_esp'     => empty($cedula_esp) ? null : $cedula_esp,
            'idUnidad'       => $idUnidad,
            'email_inst'     => $email_inst ? $email_inst : null,
            'email_personal' => $email_personal ? $email_personal : null,
            'telefono'       => empty($telefono) ? null : $telefono
        ]);
        
        echo json_encode(["estatus" => "exito", "mensaje" => "Personal médico registrado exitosamente."]);
        
    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error SQL: " . $e->getMessage()]);
    }
    exit;
}
?>