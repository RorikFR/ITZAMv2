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
        echo json_encode(["estatus" => "error", "mensaje" => "Paciente no encontrado. Verifique el CURP o registre al paciente primero."]);
    }
    exit;
}

// --- POST: GUARDAR LA NUEVA CONSULTA ---
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validamos que exista un paciente seleccionado
    if (empty($input['idPaciente']) || empty($input['tipo_consulta'])) {
        echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos críticos (Paciente o Tipo de Consulta)."]);
        exit;
    }

    // 💡 NOTA DE ARQUITECTURA: En un sistema real, el idPersonal y idUnidad se sacan de la sesión del usuario que inició sesión.
    // Como aún no hemos conectado las variables de sesión globales, usaremos IDs fijos (1 y 2) para que el registro no falle.
    $idPersonal = $_SESSION['idPersonal'] ?? 1; 
    $idUnidad = $_SESSION['idUnidad'] ?? 2;     

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
            'idPaciente'     => $input['idPaciente'],
            'idPersonal'     => $idPersonal,
            'idTipoConsulta' => $input['tipo_consulta'],
            'peso'           => $input['peso'],
            'talla'          => $input['talla'],
            'temperatura'    => $input['temperatura'],
            'freq_card'      => $input['frecuencia'],
            'sat_oxigeno'    => $input['saturacion'],
            'presion_arte'   => $input['presion_arterial'],
            'fecha_sintomas' => $input['inicio_sintomas'],
            'sintomas'       => $input['sintomas'],
            'alergias'       => $input['alergias'],
            'antecedentes'   => $input['antecedentes'],
            'habitos'        => $input['habitos'],
            'diagnostico'    => $input['diagnostico'],
            'tratamiento'    => $input['tratamiento'],
            'idUnidad'       => $idUnidad
        ]);

        // Después de hacer el $stmt->execute(...) de la consulta
        $idNuevaConsulta = $pdo->lastInsertId();
        
        // ¡Crucial! Enviamos el idConsulta de regreso al navegador
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Expediente clínico actualizado exitosamente.",
            "idConsulta" => $idNuevaConsulta // <-- El JavaScript leerá este dato
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error de base de datos. Comuníquese con soporte."]);
    }
    exit;
}
?>