<?php
header('Content-Type: application/json');

// FORZAR A PHP A MOSTRAR ERRORES (Quitar cuando ya funcione)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

// 2. MANEJO DE SOLICITUDES
$metodo = $_SERVER['REQUEST_METHOD'];

// --- LEER DATOS (BÚSQUEDA) ---
if ($metodo === 'GET') {
    $busqueda = isset($_GET['q']) ? $_GET['q'] : '';
    
    // Si hay texto, buscamos por Paciente O Curp
    if($busqueda) {
        $sql = "SELECT 
    c.idConsulta, 
    p.nombre, 
    p.apellido_p,
    p.apellido_m,
    p.curp, 
    p.fecha_nac, 
    p.genero, 
    c.tipo_consulta
FROM registro_consultas c
INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
WHERE p.curp LIKE :q
ORDER BY c.idConsulta DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['q' => "%$busqueda%"]);
    } else {
        // Si no hay búsqueda, traemos los últimos 20 registros
        $stmt = $pdo->query("SELECT 
    c.idConsulta, 
    p.nombre, 
    p.apellido_p,
    p.apellido_m,
    p.curp, 
    p.fecha_nac, 
    p.genero, 
    c.tipo_consulta
FROM  registro_consultas c
INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
ORDER BY c.idConsulta DESC 
LIMIT 20");
    }
    
    echo json_encode($stmt->fetchAll());
}

// --- EDITAR O ELIMINAR ---
if ($metodo === 'POST') {
    // Recibimos los datos JSON del frontend
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idConsulta = $input['idConsulta'] ?? 0; // Cambiado a idConsulta

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idConsulta > 0) {
        $stmt = $pdo->prepare("DELETE FROM registro_consultas WHERE idConsulta = :idConsulta");
        $stmt->execute(['idConsulta' => $idConsulta]);
        
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Registro eliminado exitosamente."
        ]);
        exit; // Detenemos la ejecución para no procesar más abajo
    }

    // --- LÓGICA DE EDICIÓN ---
    if ($accion === 'editar' && $idConsulta > 0) {
        // Recibimos los campos específicos para las consultas
        $curp = $input['curp'] ?? '';
        $tipo_consulta = $input['tipo_consulta'] ?? ''; 
        
        $stmt = $pdo->prepare("UPDATE registro_consultas 
            SET 
                tipo_consulta = :tipo_consulta 
            WHERE idConsulta = :idConsulta");
        
        $stmt->execute([
            'tipo_consulta' => $tipo_consulta, 
            'idConsulta'    => $idConsulta
        ]);
        
        // PASO 3: OBTENER la información actualizada para devolverla al frontend
        $sqlObtener = "SELECT 
                            c.idConsulta, 
                            p.nombre, 
                            p.apellido_p AS apellido_paterno, 
                            p.apellido_m AS apellido_materno, 
                            p.curp, 
                            p.fecha_nac, 
                            p.genero, 
                            c.tipo_consulta 
                       FROM registro_consultas c
                       INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                       WHERE c.idConsulta = :idConsulta";
                       
        $stmtObtener = $pdo->prepare($sqlObtener);
        $stmtObtener->execute(['idConsulta' => $idConsulta]);
        $datosActualizados = $stmtObtener->fetch(PDO::FETCH_ASSOC);
        
        // Devolvemos el mensaje de éxito y la fila completa con los datos frescos
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Consulta médica actualizada correctamente.",
            "datos"   => $datosActualizados
        ]);
        exit;
    }
}
?>