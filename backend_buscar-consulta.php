<?php
header('Content-Type: application/json');

// FORZAR A PHP A MOSTRAR ERRORES (Quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// ==========================================
// --- LEER DATOS (BÚSQUEDA GENERAL PARA DATATABLES) ---
// ==========================================
if ($metodo === 'GET') {
    
    // Consulta optimizada con JOIN al nuevo catálogo de Tipos de Consulta
    $sql = "SELECT 
                c.idConsulta, 
                p.nombre, 
                p.apellido_p,
                p.apellido_m,
                p.curp, 
                p.fecha_nac, 
                p.genero, 
                tc.nombre_tipo AS tipo_consulta, -- Texto para mostrar en la tabla
                c.idTipoConsulta                 -- ID numérico para el modal de edición
            FROM registro_consultas c
            INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
            LEFT JOIN cat_tipo_consulta tc ON c.idTipoConsulta = tc.idTipoConsulta
            ORDER BY c.idConsulta DESC";
            
    try {
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error en la base de datos al consultar."]);
    }
    exit;
}

// ==========================================
// --- EDITAR O ELIMINAR ---
// ==========================================
if ($metodo === 'POST') {
    
    // Recibimos los datos JSON del frontend
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    
    $accion = $input['accion'] ?? '';
    $idConsulta = $input['idConsulta'] ?? 0;

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idConsulta > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM registro_consultas WHERE idConsulta = :idConsulta");
            $stmt->execute(['idConsulta' => $idConsulta]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Registro eliminado permanentemente del sistema."
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "No se puede eliminar la consulta por restricciones en la base de datos."
            ]);
        }
        exit;
    }

    // --- LÓGICA DE EDICIÓN ---
    if ($accion === 'editar' && $idConsulta > 0) {
        
        // 🔥 AHORA RECIBIMOS EL ID NUMÉRICO DEL TIPO DE CONSULTA
        $idTipoConsulta = $input['idTipoConsulta'] ?? ''; 
        
        if (empty($idTipoConsulta)) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Debe seleccionar un tipo de consulta válido."
            ]);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE registro_consultas 
                SET idTipoConsulta = :idTipoConsulta 
                WHERE idConsulta = :idConsulta");
            
            $stmt->execute([
                'idTipoConsulta' => $idTipoConsulta, 
                'idConsulta'     => $idConsulta
            ]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Tipo de consulta actualizado correctamente."
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Error de base de datos al actualizar la consulta."
            ]);
        }
        exit;
    }
}
?>