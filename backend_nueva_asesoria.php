<?php
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
        echo json_encode(["estatus" => "error", "mensaje" => "Paciente no encontrado. Debe registrarlo primero."]);
    }
    exit;
}

// --- POST: GUARDAR LA NUEVA ASESORÍA ---
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $idPaciente = $input['idPaciente'] ?? '';
    $idMotivo = $input['idMotivo'] ?? '';
    $comentarios = $input['comentarios'] ?? '';
    
    if (empty($idPaciente) || empty($idMotivo) || empty($comentarios)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Todos los campos obligatorios deben estar llenos."]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO registro_asesorias (idPaciente, idMotivo, comentarios, fecha_solicitud) 
            VALUES (:idPaciente, :idMotivo, :comentarios, NOW())
        ");
        
        $stmt->execute([
            'idPaciente' => $idPaciente,
            'idMotivo' => $idMotivo,
            'comentarios' => $comentarios
        ]);
        
        echo json_encode(["estatus" => "exito", "mensaje" => "Asesoría registrada correctamente."]);
    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error al guardar en la base de datos."]);
    }
    exit;
}
?>