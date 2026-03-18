<?php
session_start();
header('Content-Type: application/json');
require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// --- GET: BUSCAR PACIENTE POR CURP ---
if ($metodo === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'buscar_paciente') {
    $curp = $_GET['curp'] ?? '';
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

// --- GET: OBTENER LISTA DE MÉDICOS ---
if ($metodo === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'obtener_medicos') {
    try {
        // Buscamos a todo el personal que tenga una cédula registrada (para filtrar a los que no son médicos)
        $stmt = $pdo->query("SELECT cedula, nombre, apellido_p, apellido_m FROM registro_personal WHERE cedula IS NOT NULL AND cedula != ''");
        $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["estatus" => "exito", "datos" => $medicos]);
    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error al cargar médicos"]);
    }
    exit;
}

// --- POST: GUARDAR LA ORDEN DE LABORATORIO (MAESTRO-DETALLE) ---
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['idPaciente']) || empty($input['estudios_solicitados']) || empty($input['prioridad']) || empty($input['medico'])) {
        echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios."]);
        exit;
    }

    try {
        // Iniciamos la transacción (Todo o nada)
        $pdo->beginTransaction();

        // 1. Traducir la Cédula a idPersonal_solicitante
        $stmtMed = $pdo->prepare("SELECT idPersonal FROM registro_personal WHERE cedula = :cedula LIMIT 1");
        $stmtMed->execute(['cedula' => $input['medico']]);
        $medico = $stmtMed->fetch(PDO::FETCH_ASSOC);

        if (!$medico) {
            // Si la cédula es falsa, abortamos
            $pdo->rollBack();
            echo json_encode(["estatus" => "error", "mensaje" => "No existe un médico registrado con esa cédula profesional."]);
            exit;
        }

        // 2. Insertar en la tabla MAESTRO (registro_laboratorio)
        $stmtOrden = $pdo->prepare("
            INSERT INTO registro_laboratorio (
                idPaciente, idPersonal_solicitante, idPrioridad, observaciones, diagnostico_pre
            ) VALUES (
                :idPaciente, :idPersonal_solicitante, :idPrioridad, :observaciones, :diagnostico_pre
            )
        ");
        
        $stmtOrden->execute([
            'idPaciente'             => $input['idPaciente'],
            'idPersonal_solicitante' => $medico['idPersonal'],
            'idPrioridad'            => $input['prioridad'],
            'observaciones'          => $input['observaciones'],
            'diagnostico_pre'        => $input['diagnostico_preliminar']
        ]);

        // 3. Obtener el ID autoincrementable que MySQL acaba de generar
        $idOrdenLab_generado = $pdo->lastInsertId();

        // 4. Insertar en la tabla DETALLE (laboratorio_detalle)
        $stmtDetalle = $pdo->prepare("
            INSERT INTO laboratorio_detalle (idOrdenLab, idEstudio) 
            VALUES (:idOrdenLab, :idEstudio)
        ");

        $stmtDetalle->execute([
            'idOrdenLab' => $idOrdenLab_generado,
            'idEstudio'  => $input['estudios_solicitados']
        ]);

        // Si todo salió bien, confirmamos la transacción
        $pdo->commit();
        
        echo json_encode(["estatus" => "exito", "mensaje" => "Orden de laboratorio #$idOrdenLab_generado creada correctamente."]);

    } catch (PDOException $e) {
        // Si hay error (ej. se cae la base de datos a la mitad), deshacemos todo
        $pdo->rollBack();
        echo json_encode(["estatus" => "error", "mensaje" => "Error de base de datos."]);
    }
    exit;
}
?>