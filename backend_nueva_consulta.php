<?php

require 'seguridad_backend.php';
require 'autorizacion.php';

// Solo personal clínico puede registrar consultas
requerir_roles_api(['Médico', 'Enfermería']);

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// Buscar paciente por CURP
if ($metodo === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'buscar_paciente') {
    $curp = strtoupper(trim($_GET['curp'] ?? ''));
    
    // Validación de formato CURP rápido
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
        echo json_encode(["estatus" => "error", "mensaje" => "Paciente no encontrado. Verifique el CURP o registre al paciente primero."]);
    }
    exit;
}

// Guardar consulta
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validacion de IDs
    $idPaciente     = filter_var($input['idPaciente'] ?? '', FILTER_VALIDATE_INT);
    $idTipoConsulta = filter_var($input['tipo_consulta'] ?? '', FILTER_VALIDATE_INT);
    
    if (!$idPaciente || !$idTipoConsulta) {
        echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos críticos (Paciente o Tipo de Consulta) o su formato es incorrecto."]);
        exit;
    }

    // Sanitizar datos de entrada
    $sintomas    = htmlspecialchars(trim($input['sintomas'] ?? ''), ENT_QUOTES, 'UTF-8');
    $diagnostico = htmlspecialchars(trim($input['diagnostico'] ?? ''), ENT_QUOTES, 'UTF-8');
    $tratamiento = htmlspecialchars(trim($input['tratamiento'] ?? ''), ENT_QUOTES, 'UTF-8');
    $alergias    = htmlspecialchars(trim($input['alergias'] ?? ''), ENT_QUOTES, 'UTF-8');
    $antecedentes= htmlspecialchars(trim($input['antecedentes'] ?? ''), ENT_QUOTES, 'UTF-8');
    $habitos     = htmlspecialchars(trim($input['habitos'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Validar longitud de texto máxima
    if (mb_strlen($sintomas) > 5000 || mb_strlen($diagnostico) > 5000 || mb_strlen($tratamiento) > 5000) {
        echo json_encode(["estatus" => "error", "mensaje" => "Uno de los campos de texto excede el límite de 5,000 caracteres."]);
        exit;
    }

    // Validar entradas de valores numericos
    $peso        = filter_var($input['peso'] ?? null, FILTER_VALIDATE_FLOAT);
    $talla       = filter_var($input['talla'] ?? null, FILTER_VALIDATE_FLOAT);
    $temperatura = filter_var($input['temperatura'] ?? null, FILTER_VALIDATE_FLOAT);
    $frecuencia  = filter_var($input['frecuencia'] ?? null, FILTER_VALIDATE_INT);
    $saturacion  = filter_var($input['saturacion'] ?? null, FILTER_VALIDATE_INT);
    
    //Sanitizar entrada
    $presionArterial = htmlspecialchars(trim($input['presion_arterial'] ?? ''), ENT_QUOTES, 'UTF-8');
    $fechaSintomas   = trim($input['inicio_sintomas'] ?? null);
    
    //Validar variables de sesión
    $idPersonal = $_SESSION['idUsuario'] ?? null; 
    $idUnidad   = $_SESSION['idUnidad'] ?? null; 

    if (!$idPersonal || !$idUnidad) {
        echo json_encode([
            "estatus" => "error", 
            "mensaje" => "Error de sesión: No se pudo identificar al médico tratante o su Unidad Médica. Por favor, cierre sesión y vuelva a ingresar."
        ]);
        exit; 
    }    

    if (!$idPersonal) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error de sesión. No se puede identificar al médico tratante."]);
        exit;
    }

    //Enviar a DB
    try {
        $stmt = $pdo->prepare("
            INSERT INTO registro_consultas (
                idPaciente, idPersonal, idTipoConsulta, peso, talla, temperatura, 
                freq_card, sat_oxigeno, presion_arte, fecha_sintomas, sintomas, 
                alergias, antecedentes, habitos, diagnostico, tratamiento, idUnidad, fecha_consulta
            ) VALUES (
                :idPaciente, :idPersonal, :idTipoConsulta, :peso, :talla, :temperatura, 
                :freq_card, :sat_oxigeno, :presion_arte, :fecha_sintomas, :sintomas, 
                :alergias, :antecedentes, :habitos, :diagnostico, :tratamiento, :idUnidad, NOW()
            )
        ");
        
        $stmt->execute([
            'idPaciente'     => $idPaciente,
            'idPersonal'     => $idPersonal,
            'idTipoConsulta' => $idTipoConsulta,
            'peso'           => $peso,
            'talla'          => $talla,
            'temperatura'    => $temperatura,
            'freq_card'      => $frecuencia,
            'sat_oxigeno'    => $saturacion,
            'presion_arte'   => $presionArterial,
            'fecha_sintomas' => $fechaSintomas,
            'sintomas'       => $sintomas,
            'alergias'       => $alergias,
            'antecedentes'   => $antecedentes,
            'habitos'        => $habitos,
            'diagnostico'    => $diagnostico,
            'tratamiento'    => $tratamiento,
            'idUnidad'       => $idUnidad
        ]);

        $idNuevaConsulta = $pdo->lastInsertId();
        
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Expediente clínico actualizado exitosamente.",
            "idConsulta" => $idNuevaConsulta
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error de base de datos. Comuníquese con soporte."]);
    }
    exit;
}
?>