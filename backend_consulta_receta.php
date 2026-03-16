<?php
header('Content-Type: application/json');

//DEV ONLY - quitar en prod.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

// 2. MANEJO DE SOLICITUDES
$metodo = $_SERVER['REQUEST_METHOD'];

// --- LEER DATOS (BÚSQUEDA) ---
if ($metodo === 'GET') {
    $busqueda = isset($_GET['q']) ? $_GET['q'] : '';
    
    // Si hay texto, buscamos por curp
    if($busqueda) {
        $sql = "SELECT 
                    r.idReceta, 
                    p.curp AS curp_paciente, 
                    i.nombre AS medicamento, 
                    d.dosis, 
                    d.cantidad_surtir, 
                    r.indicaciones_generales, 
                    r.prox_consulta, 
                    CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico
                FROM registro_receta r
                INNER JOIN registro_consultas c ON r.idConsulta = c.idConsulta
                INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
                INNER JOIN receta_detalle d ON r.idReceta = d.idReceta                    
                INNER JOIN inventario_medicamentos i ON d.idMed = i.idMed
                WHERE p.curp LIKE :q
                ORDER BY r.idReceta DESC";
       
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['q' => "%$busqueda%"]);
    } else {
        // Si no hay búsqueda, traemos los últimos 20 registros
        $stmt = $pdo->query("SELECT 
                                r.idReceta, 
                                p.curp AS curp_paciente, 
                                i.nombre AS medicamento, 
                                d.dosis, 
                                d.cantidad_surtir, 
                                r.prox_consulta, 
                                CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico
                            FROM registro_receta r
                            INNER JOIN registro_consultas c ON r.idConsulta = c.idConsulta
                            INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                            INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
                            INNER JOIN receta_detalle d ON r.idReceta = d.idReceta
                            INNER JOIN inventario_medicamentos i ON d.idMed = i.idMed
                            ORDER BY r.idReceta DESC");
    }
    
    echo json_encode($stmt->fetchAll());
}

// --- EDITAR O ELIMINAR ---
if ($metodo === 'POST') {
    // Recibimos los datos JSON del frontend
    $input = json_decode(file_get_contents('php://input'), true);
    
$accion = $input['accion'] ?? '';

    $idReceta = $input['idReceta'] ?? 0; 

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idReceta > 0) {
        
        try {
            // Eliminar los medicamentos asociados en la tabla detalle 
            $stmtDetalle = $pdo->prepare("DELETE FROM receta_detalle WHERE idReceta = :idReceta");
            $stmtDetalle->execute(['idReceta' => $idReceta]);
            
            //Eliminar registro
            $stmtPrincipal = $pdo->prepare("DELETE FROM registro_receta WHERE idReceta = :idReceta");
            $stmtPrincipal->execute(['idReceta' => $idReceta]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Receta médica y sus medicamentos eliminados exitosamente."
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Ocurrió un error en la base de datos al intentar eliminar la receta."
            ]);
        }
        
        exit; 
    }

    $idReceta = $input['idReceta'] ?? 0;

    // --- LÓGICA DE EDICIÓN ---
    if ($accion === 'editar' && $idReceta > 0) {
        
        //Campo a editar
        $prox_consulta = $input['prox_consulta'] ?? '';
        
        // Actualizar la tabla  
        $stmt = $pdo->prepare("UPDATE registro_receta 
            SET prox_consulta = :prox_consulta
            WHERE idReceta = :idReceta");
        
        $stmt->execute([
            'prox_consulta' => $prox_consulta,
            'idReceta'      => $idReceta
        ]);
        
        // Obtener la información actualizada para devolverla al frontend
        $sqlObtener = "SELECT 
                            r.idReceta, 
                            p.curp AS curp_paciente, 
                            i.nombre AS medicamento, 
                            d.dosis, 
                            d.cantidad_surtir, 
                            r.indicaciones_generales, 
                            r.prox_consulta, 
                            CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_que_receta
                       FROM registro_receta r
                       INNER JOIN registro_consultas c ON r.idConsulta = c.idConsulta
                       INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                       INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
                       INNER JOIN receta_detalle d ON r.idReceta = d.idReceta
                       INNER JOIN inventario_medicamentos i ON d.idMed = i.idMed
                       WHERE r.idReceta = :idReceta";
                       
        $stmtObtener = $pdo->prepare($sqlObtener);
        $stmtObtener->execute(['idReceta' => $idReceta]);
        
        $datosActualizados = $stmtObtener->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Fecha de próxima consulta actualizada correctamente.",
            "datos"   => $datosActualizados
        ]);
        exit;
    }
}
?>