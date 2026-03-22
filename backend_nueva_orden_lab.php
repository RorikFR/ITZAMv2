<?php
// Validacion de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';      

//RBAC
requerir_roles_api(['Médico', 'Enfermería']); 

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// Buscar pacient por CURP
if ($metodo === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'buscar_paciente') {
    $curp = strtoupper(trim($_GET['curp'] ?? ''));
    
    // Validar formato CURP
    if (!preg_match('/^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/', $curp)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Formato de CURP inválido."]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT idPaciente, nombre, apellido_p, apellido_m FROM registro_paciente WHERE curp = :curp LIMIT 1");
    $stmt->execute(['curp' => $curp]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($paciente) {
        echo json_encode(["estatus" => "exito", "datos" => $paciente]);
    } else {
        echo json_encode(["estatus" => "error", "mensaje" => "Paciente no encontrado. Verifique el CURP."]);
    }
    exit;
}


//Guardar orden de laboratorio
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar IDs
    $idPaciente  = filter_var($input['idPaciente'] ?? '', FILTER_VALIDATE_INT);
    $idEstudio   = filter_var($input['estudios_solicitados'] ?? '', FILTER_VALIDATE_INT);
    $idPrioridad = filter_var($input['prioridad'] ?? '', FILTER_VALIDATE_INT);
    
    if (!$idPaciente || !$idEstudio || !$idPrioridad) {
        echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios o el formato es incorrecto."]);
        exit;
    }

    // Sanitización de datos
    $observaciones   = htmlspecialchars(trim($input['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8');
    $diagnostico_pre = htmlspecialchars(trim($input['diagnostico_preliminar'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Validar longitud de texto
    if (mb_strlen($observaciones) > 2000) {
        echo json_encode(["estatus" => "error", "mensaje" => "Las observaciones exceden el límite de 2,000 caracteres."]);
        exit;
    }
    if (mb_strlen($diagnostico_pre) > 5000) {
        echo json_encode(["estatus" => "error", "mensaje" => "El diagnóstico preliminar excede el límite de 5,000 caracteres."]);
        exit;
    }

    // Trazabilidad
    $idPersonalSolicitante = $_SESSION['idUsuario'] ?? null;
    
    if (!$idPersonalSolicitante) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error de sesión: No se pudo identificar al médico solicitante."]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Guardar en DB
        $stmtOrden = $pdo->prepare("
            INSERT INTO registro_laboratorio (
                idPaciente, idPersonal_solicitante, idPrioridad, observaciones, diagnostico_pre
            ) VALUES (
                :idPaciente, :idPersonal_solicitante, :idPrioridad, :observaciones, :diagnostico_pre
            )
        ");
        
        $stmtOrden->execute([
            'idPaciente'             => $idPaciente,
            'idPersonal_solicitante' => $idPersonalSolicitante, // 🔥 Usamos la variable de sesión
            'idPrioridad'            => $idPrioridad,
            'observaciones'          => $observaciones,
            'diagnostico_pre'        => $diagnostico_pre
        ]);

        //Obtener ID
        $idOrdenLab_generado = $pdo->lastInsertId();

        // Guardar en tabla detalle
        $stmtDetalle = $pdo->prepare("
            INSERT INTO laboratorio_detalle (idOrdenLab, idEstudio) 
            VALUES (:idOrdenLab, :idEstudio)
        ");

        $stmtDetalle->execute([
            'idOrdenLab' => $idOrdenLab_generado,
            'idEstudio'  => $idEstudio
        ]);

        $pdo->commit();
        
        echo json_encode(["estatus" => "exito", "mensaje" => "Orden de laboratorio #$idOrdenLab_generado creada correctamente."]);

    } catch (PDOException $e) {
        // Revertir cambios en caso de error
        $pdo->rollBack();
        echo json_encode(["estatus" => "error", "mensaje" => "Error interno al guardar la orden de laboratorio."]);
    }
    exit;
}
?>