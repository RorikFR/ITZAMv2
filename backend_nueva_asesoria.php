<?php
// RBAC y validaciones de seguridad
require 'seguridad_backend.php';
require 'autorizacion.php';

// Roles permitidos
requerir_roles_api(['Médico', 'Enfermería']);

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// Buscar paciente CURP
if ($metodo === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'buscar_paciente') {
    
    // Limpiamos espacios y forzamos mayúsculas
    $curp = strtoupper(trim($_GET['curp'] ?? ''));
    
    // Verificamos formato de 18 caracteres
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
        echo json_encode(["estatus" => "error", "mensaje" => "Paciente no encontrado. Debe registrarlo primero."]);
    }
    exit;
}

// Guardar asesoría
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar IDs
    $idPaciente = filter_var($input['idPaciente'] ?? '', FILTER_VALIDATE_INT);
    $idMotivo   = filter_var($input['idMotivo'] ?? '', FILTER_VALIDATE_INT);
    
    // Sanitización de entradas
    $comentarios = htmlspecialchars(trim($input['comentarios'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    // Validaciones
    if ($idPaciente === false || $idMotivo === false) {
        echo json_encode(["estatus" => "error", "mensaje" => "Los IDs enviados no son válidos."]);
        exit;
    }
    
    if (empty($comentarios)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Debe ingresar comentarios sobre la asesoría."]);
        exit;
    }
    
    try {
        //Agregar ID del creador 
        $stmt = $pdo->prepare("
            INSERT INTO registro_asesorias (idPaciente, idPersonal, idMotivo, comentarios, fecha_solicitud) 
            VALUES (:idPaciente, :idPersonal, :idMotivo, :comentarios, NOW())
        ");
        
        $stmt->execute([
            'idPaciente'  => $idPaciente,
            'idPersonal'  => $_SESSION['idUsuario'],
            'idMotivo'    => $idMotivo,
            'comentarios' => $comentarios
        ]);
        
        echo json_encode(["estatus" => "exito", "mensaje" => "Asesoría registrada correctamente en el expediente."]);
    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error al guardar en la base de datos."]);
    }
    exit;
}
?>