<?php
header('Content-Type: application/json');

// FORZAR A PHP A MOSTRAR ERRORES (Quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// ==========================================
// --- LEER DATOS (CARGA INICIAL PARA DATATABLES) ---
// ==========================================
if ($metodo === 'GET') {
    
    // Consulta optimizada con JOINs a los nuevos catálogos 3FN
    $sql = "SELECT 
                l.idOrdenLab AS idOrdenLaboratorio, 
                p.nombre AS nombre_paciente, 
                p.apellido_p AS apellido_paterno, 
                p.apellido_m AS apellido_materno, 
                p.curp, 
                p.genero, 
                CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_solicitante,
                
                -- Datos del catálogo de prioridades
                cp.nombre_prioridad AS prioridad, 
                l.idPrioridad, 
                
                -- Datos del catálogo de estudios
                ce.nombre_estudio AS estudio_requerido, 
                d.idEstudio,
                
                l.diagnostico_pre AS diagnostico_preliminar
            FROM registro_laboratorio l
            INNER JOIN registro_paciente p ON l.idPaciente = p.idPaciente
            INNER JOIN registro_personal m ON l.idPersonal_solicitante = m.idPersonal
            INNER JOIN laboratorio_detalle d ON l.idOrdenLab = d.idOrdenLab
            LEFT JOIN cat_prioridad_lab cp ON l.idPrioridad = cp.idPrioridad
            LEFT JOIN cat_estudios_laboratorio ce ON d.idEstudio = ce.idEstudio
            ORDER BY l.idOrdenLab DESC";
            
    try {
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error de base de datos al cargar ordenes."]);
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
    $idOrdenLab = $input['idOrdenLaboratorio'] ?? 0; 

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idOrdenLab > 0) {
        
        try {
            // PASO 1: Eliminar los registros hijos en la tabla pivote
            $stmtDetalle = $pdo->prepare("DELETE FROM laboratorio_detalle WHERE idOrdenLab = :idOrdenLab");
            $stmtDetalle->execute(['idOrdenLab' => $idOrdenLab]);
            
            // PASO 2: Ahora sí, eliminamos la orden principal de laboratorio
            $stmt = $pdo->prepare("DELETE FROM registro_laboratorio WHERE idOrdenLab = :idOrdenLab");
            $stmt->execute(['idOrdenLab' => $idOrdenLab]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Orden de laboratorio eliminada exitosamente."
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Error interno al intentar eliminar la orden."
            ]);
        }
        exit;
    }

    // --- LÓGICA DE EDICIÓN ---
    if ($accion === 'editar' && $idOrdenLab > 0) {
        
        // 🔥 AHORA RECIBIMOS LOS IDs, NO LOS TEXTOS
        $idPrioridad = $input['idPrioridad'] ?? 0;
        $idEstudioNuevo = $input['idEstudioNuevo'] ?? 0; 
        $idEstudioViejo = $input['idEstudioViejo'] ?? 0; // Necesitamos saber cuál estudio estamos reemplazando
        
        if (empty($idPrioridad) || empty($idEstudioNuevo) || empty($idEstudioViejo)) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Faltan datos obligatorios para la actualización."
            ]);
            exit;
        }
        
        try {
            // PASO 1: Actualizar la tabla principal (prioridad) usando la nueva Foránea
            $stmtPrincipal = $pdo->prepare("UPDATE registro_laboratorio 
                SET idPrioridad = :idPrioridad 
                WHERE idOrdenLab = :idOrdenLab");
            
            $stmtPrincipal->execute([
                'idPrioridad' => $idPrioridad, 
                'idOrdenLab'  => $idOrdenLab
            ]);

            // PASO 2: Actualizar la tabla detalle (reemplazando un estudio específico)
            // Esto es crucial porque la Llave Primaria es (idOrdenLab + idEstudio)
            $stmtDetalle = $pdo->prepare("UPDATE laboratorio_detalle 
                SET idEstudio = :idEstudioNuevo 
                WHERE idOrdenLab = :idOrdenLab AND idEstudio = :idEstudioViejo");
            
            $stmtDetalle->execute([
                'idEstudioNuevo' => $idEstudioNuevo, 
                'idOrdenLab'     => $idOrdenLab,
                'idEstudioViejo' => $idEstudioViejo
            ]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Orden de laboratorio actualizada correctamente."
            ]);
            
        } catch (PDOException $e) {
            // Si intenta actualizar un estudio por otro que YA existe en esa misma orden, dará error de PK duplicada
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Error de base de datos. Es posible que el paciente ya tenga este estudio asignado en esta misma orden."
            ]);
        }
        exit;
    }
}
?>