<?php
// Validacion de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';      

// RBAC
requerir_roles_api(['Médico', 'Enfermería']); 

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    
    try {
        $sql = "SELECT 
                    l.idOrdenLab AS idOrdenLaboratorio, 
                    l.idPersonal_solicitante,
                    p.nombre AS nombre_paciente, 
                    p.apellido_p AS apellido_paterno,
                    p.apellido_m AS apellido_materno, 
                    p.curp, 
                    p.genero, 
                    CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_solicitante,
                    
                    cp.nombre_prioridad AS prioridad, 
                    l.idPrioridad, 
                    
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
                
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error de base de datos al cargar ordenes."]);
    }
    exit;
}

if ($metodo === 'POST') {
    // Recibimos los datos JSON del frontend
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    
    $accion = filter_var($input['accion'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $idOrdenLab = filter_var($input['idOrdenLaboratorio'] ?? 0, FILTER_VALIDATE_INT); 

    // Trazabilidad
    if ($idOrdenLab > 0 && ($accion === 'editar' || $accion === 'eliminar')) {
        $stmtAutor = $pdo->prepare("SELECT idPersonal_solicitante FROM registro_laboratorio WHERE idOrdenLab = :id");
        $stmtAutor->execute(['id' => $idOrdenLab]);
        $autor = $stmtAutor->fetch(PDO::FETCH_ASSOC);

        // Validar rol del usuario
        if (!$autor || ($autor['idPersonal_solicitante'] != $_SESSION['idUsuario'] && $_SESSION['rol'] !== 'Administrador')) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Acceso denegado: Solo el médico solicitante puede modificar o cancelar esta orden."
            ]);
            exit;
    }

    // Eliminar registros
    if ($accion === 'eliminar' && $idOrdenLab > 0) {
        
        try {
            $pdo->beginTransaction();

            // Eliminar registros hijos 
            $stmtDetalle = $pdo->prepare("DELETE FROM laboratorio_detalle WHERE idOrdenLab = :idOrdenLab");
            $stmtDetalle->execute(['idOrdenLab' => $idOrdenLab]);
            
            // Eliminar orden principal
            $stmt = $pdo->prepare("DELETE FROM registro_laboratorio WHERE idOrdenLab = :idOrdenLab");
            $stmt->execute(['idOrdenLab' => $idOrdenLab]);
            
            $pdo->commit();

            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Orden de laboratorio eliminada exitosamente."
            ]);
        } catch (PDOException $e) {
            // Revertir cambios
            $pdo->rollBack();
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Error interno al intentar eliminar la orden. Es posible que existan restricciones en la BD."
            ]);
        }
        exit;
    }

    // Editar registros
    if ($accion === 'editar' && $idOrdenLab > 0) {
        
        // Recibir IDs y sanitizar
        $idPrioridad = filter_var($input['idPrioridad'] ?? 0, FILTER_VALIDATE_INT);
        $idEstudioNuevo = filter_var($input['idEstudioNuevo'] ?? 0, FILTER_VALIDATE_INT); 
        $idEstudioViejo = filter_var($input['idEstudioViejo'] ?? 0, FILTER_VALIDATE_INT); 
        
        if (empty($idPrioridad) || empty($idEstudioNuevo) || empty($idEstudioViejo)) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Faltan datos obligatorios para la actualización o su formato es incorrecto."
            ]);
            exit;
        }
        
        try {
            $pdo->beginTransaction();

            // Actualizar tabla principal
            $stmtPrincipal = $pdo->prepare("UPDATE registro_laboratorio 
                SET idPrioridad = :idPrioridad 
                WHERE idOrdenLab = :idOrdenLab");
            $stmtPrincipal->execute([
                'idPrioridad' => $idPrioridad, 
                'idOrdenLab'  => $idOrdenLab
            ]);

            // Actualizar tabla secundaria
            $stmtDetalle = $pdo->prepare("UPDATE laboratorio_detalle 
                SET idEstudio = :idEstudioNuevo 
                WHERE idOrdenLab = :idOrdenLab AND idEstudio = :idEstudioViejo");
            $stmtDetalle->execute([
                'idEstudioNuevo' => $idEstudioNuevo, 
                'idOrdenLab'     => $idOrdenLab,
                'idEstudioViejo' => $idEstudioViejo
            ]);
            
            // Confirmar cambios
            $pdo->commit();
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Orden de laboratorio actualizada correctamente."
            ]);
            
        } catch (PDOException $e) {
            // Revertir cambios 
            $pdo->rollBack();
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Error interno de base de datos al intentar actualizar."
            ]);
        }
        exit;
        }
    }
}
?>