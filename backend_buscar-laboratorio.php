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
    l.idOrdenLab AS idOrdenLaboratorio, 
    p.nombre AS nombre_paciente, 
    p.apellido_p AS apellido_paterno, 
    p.apellido_m AS apellido_materno, 
    p.curp, 
    p.genero, 
    CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_solicitante,
    l.prioridad, 
    d.estudio_solicitado AS estudio_requerido, 
    l.diagnostico_pre AS diagnostico_preliminar
FROM registro_laboratorio l
INNER JOIN registro_paciente p ON l.idPaciente = p.idPaciente
INNER JOIN registro_personal m ON l.idPersonal_solicitante = m.idPersonal
INNER JOIN laboratorio_detalle d ON l.idOrdenLab = d.idOrdenLab
WHERE p.curp LIKE :q
ORDER BY l.idOrdenLab DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['q' => "%$busqueda%"]);
    } else {
        // Si no hay búsqueda, traemos los últimos 20 registros
        $stmt = $pdo->query("SELECT 
    l.idOrdenLab AS idOrdenLaboratorio, 
    p.nombre AS nombre_paciente, 
    p.apellido_p AS apellido_paterno, 
    p.apellido_m AS apellido_materno, 
    p.curp, 
    p.genero, 
    CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_solicitante,
    l.prioridad, 
    d.estudio_solicitado AS estudio_requerido, 
    l.diagnostico_pre AS diagnostico_preliminar
FROM registro_laboratorio l
INNER JOIN registro_paciente p ON l.idPaciente = p.idPaciente
INNER JOIN registro_personal m ON l.idPersonal_solicitante = m.idPersonal
INNER JOIN laboratorio_detalle d ON l.idOrdenLab = d.idOrdenLab
ORDER BY l.idOrdenLab DESC 
LIMIT 20");
    }
    
    echo json_encode($stmt->fetchAll());
}

// --- EDITAR O ELIMINAR ---
if ($metodo === 'POST') {
    // Recibimos los datos JSON del frontend
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    // Asegúrate de que tu JavaScript mande esta variable exactamente con este nombre
    $idOrdenLab = $input['idOrdenLaboratorio'] ?? 0; 

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idOrdenLab > 0) {
        
        // PASO 1: Eliminar los registros hijos en la tabla pivote (1FN)
        // Esto evita el error de restricción de llave foránea (Foreign Key Constraint Fails)
        $stmtDetalle = $pdo->prepare("DELETE FROM laboratorio_detalle WHERE idOrdenLab = :idOrdenLab");
        $stmtDetalle->execute(['idOrdenLab' => $idOrdenLab]);
        
        // PASO 2: Ahora sí, eliminamos la orden principal de laboratorio
        $stmt = $pdo->prepare("DELETE FROM registro_laboratorio WHERE idOrdenLab = :idOrdenLab");
        $stmt->execute(['idOrdenLab' => $idOrdenLab]);
        
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Orden de laboratorio eliminada exitosamente."
        ]);
        exit; // Detenemos la ejecución para no procesar más abajo
    }

// Asegúrate de extraer la variable correcta que envía tu frontend
    $idOrdenLab = $input['idOrdenLaboratorio'] ?? 0;

    // --- LÓGICA DE EDICIÓN ---
    if ($accion === 'editar' && $idOrdenLab > 0) {
        // Recibimos los campos específicos para el laboratorio
        $prioridad = $input['prioridad'] ?? '';
        $estudio_requerido = $input['estudio_requerido'] ?? ''; 
        
        // PASO 1: Actualizar la tabla principal (prioridad)
        $stmtPrincipal = $pdo->prepare("UPDATE registro_laboratorio 
            SET prioridad = :prioridad 
            WHERE idOrdenLab = :idOrdenLab");
        
        $stmtPrincipal->execute([
            'prioridad'  => $prioridad, 
            'idOrdenLab' => $idOrdenLab
        ]);

        // PASO 2: Actualizar la tabla detalle (estudio solicitado)
        $stmtDetalle = $pdo->prepare("UPDATE laboratorio_detalle 
            SET estudio_solicitado = :estudio_requerido 
            WHERE idOrdenLab = :idOrdenLab");
        
        $stmtDetalle->execute([
            'estudio_requerido' => $estudio_requerido, 
            'idOrdenLab'        => $idOrdenLab
        ]);
        
        // PASO 3: OBTENER la información actualizada para devolverla al frontend
        // Usamos los 3 INNER JOIN y el CONCAT_WS para el médico
        $sqlObtener = "SELECT 
                            l.idOrdenLab AS idOrdenLaboratorio, 
                            p.nombre AS nombre_paciente, 
                            p.apellido_p AS apellido_paterno, 
                            p.apellido_m AS apellido_materno, 
                            p.curp, 
                            p.genero, 
                            CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_solicitante,
                            l.prioridad, 
                            d.estudio_solicitado AS estudio_requerido, 
                            l.diagnostico_pre AS diagnostico_preliminar
                       FROM registro_laboratorio l
                       INNER JOIN registro_paciente p ON l.idPaciente = p.idPaciente
                       INNER JOIN registro_personal m ON l.idPersonal_solicitante = m.idPersonal
                       INNER JOIN laboratorio_detalle d ON l.idOrdenLab = d.idOrdenLab
                       WHERE l.idOrdenLab = :idOrdenLab 
                       AND d.estudio_solicitado = :estudio_requerido";
                       
        $stmtObtener = $pdo->prepare($sqlObtener);
        
        // Pasamos ambos parámetros para asegurar que traemos la fila exacta
        $stmtObtener->execute([
            'idOrdenLab'        => $idOrdenLab,
            'estudio_requerido' => $estudio_requerido
        ]);
        
        $datosActualizados = $stmtObtener->fetch(PDO::FETCH_ASSOC);
        
        // Devolvemos el mensaje de éxito y la fila completa con los datos frescos
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Orden de laboratorio actualizada correctamente.",
            "datos"   => $datosActualizados
        ]);
        exit;
    }
}
?>