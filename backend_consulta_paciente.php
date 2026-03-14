<?php
header('Content-Type: application/json');

//DEV ONLY - quitar en prod.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

// 2. MANEJO DE SOLICITUDES
$metodo = $_SERVER['REQUEST_METHOD'];

// --- LEER DATOS (BÚSQUEDA POR CURP) ---
if ($metodo === 'GET') {
    // Capturamos lo que el usuario escriba en la barra de búsqueda y limpiamos espacios
    $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if ($busqueda) {
        // --- CASO 1: BÚSQUEDA ACTIVA ---
        $sql = "SELECT 
                    p.idPaciente, 
                    p.curp, 
                    p.nombre, 
                    p.apellido_p AS apellido_paterno, 
                    p.apellido_m AS apellido_materno, 
                    p.fecha_nac, 
                    p.genero, 
                    IF(p.es_indigena = 1, 'Sí', 'No') AS indigena, 
                    IF(p.es_afrodesc = 1, 'Sí', 'No') AS afrodescendencia, 
                    p.nacionalidad, 
                    p.telefono, 
                    p.email AS correo_electronico
                FROM registro_paciente p
                WHERE p.curp LIKE :q
                ORDER BY p.idPaciente DESC";
        
        $stmt = $pdo->prepare($sql);
        // Usamos los comodines % por si ingresan solo una parte del CURP
        $stmt->execute(['q' => "%$busqueda%"]);
        
    } else {
        // --- CASO 2: CARGA INICIAL (SIN BÚSQUEDA) ---
        // Traemos los últimos 20 expedientes registrados
        $sql = "SELECT 
                    p.idPaciente, 
                    p.curp, 
                    p.nombre, 
                    p.apellido_p AS apellido_paterno, 
                    p.apellido_m AS apellido_materno, 
                    p.fecha_nac, 
                    p.genero, 
                    IF(p.es_indigena = 1, 'Sí', 'No') AS indigena, 
                    IF(p.es_afrodesc = 1, 'Sí', 'No') AS afrodescendencia, 
                    p.nacionalidad, 
                    p.telefono, 
                    p.email AS correo_electronico
                FROM registro_paciente p
                ORDER BY p.idPaciente DESC 
                LIMIT 20";
        
        // Como no hay variables dinámicas, podemos usar query() directo
        $stmt = $pdo->query($sql);
    }
    
    // Devolvemos los datos limpios al frontend
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
 

// --- EDITAR O ELIMINAR ---
if ($metodo === 'POST') {
    // Recibimos los datos JSON del frontend
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';

    // Extraemos la llave primaria del paciente
    $idPaciente = $input['idPaciente'] ?? 0; 

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idPaciente > 0) {
        
        try {
            // Eliminamos el registro de la tabla maestra
            $stmt = $pdo->prepare("DELETE FROM registro_paciente WHERE idPaciente = :idPaciente");
            $stmt->execute(['idPaciente' => $idPaciente]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Expediente del paciente eliminado exitosamente."
            ]);
            
        } catch (PDOException $e) {
            // El motor bloquea la eliminación si hay historial clínico (recetas, consultas, etc.)
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "No se puede eliminar este expediente porque el paciente ya tiene consultas, recetas o estudios asociados en ITZAM."
            ]);
        }
        
        exit; 
    }

    // --- LÓGICA DE EDICIÓN ---
    if ($accion === 'editar' && $idPaciente > 0) {
        
        // Recibimos los campos de contacto a editar
        $telefono = $input['telefono'] ?? '';
        $correo_electronico = $input['correo_electronico'] ?? '';
        
        // PASO 1: Actualizar la tabla
        $stmt = $pdo->prepare("UPDATE registro_paciente 
            SET 
                telefono = :telefono,
                email = :correo_electronico
            WHERE idPaciente = :idPaciente");
        
        $stmt->execute([
            'telefono'           => $telefono,
            'correo_electronico' => $correo_electronico,
            'idPaciente'         => $idPaciente
        ]);
        
        // PASO 2: Obtener la información actualizada para la interfaz
        // Usamos la misma consulta de lectura con sus funciones IF() para los booleanos
        $sqlObtener = "SELECT 
                            p.idPaciente, 
                            p.curp, 
                            p.nombre, 
                            p.apellido_p AS apellido_paterno, 
                            p.apellido_m AS apellido_materno, 
                            p.fecha_nac, 
                            p.genero, 
                            IF(p.es_indigena = 1, 'Sí', 'No') AS indigena, 
                            IF(p.es_afrodesc = 1, 'Sí', 'No') AS afrodescendencia, 
                            p.nacionalidad, 
                            p.telefono, 
                            p.email AS correo_electronico
                       FROM registro_paciente p
                       WHERE p.idPaciente = :idPaciente";
                       
        $stmtObtener = $pdo->prepare($sqlObtener);
        $stmtObtener->execute(['idPaciente' => $idPaciente]);
        
        // Usamos fetch() porque es un solo paciente
        $datosActualizados = $stmtObtener->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Datos de contacto del paciente actualizados correctamente.",
            "datos"   => $datosActualizados
        ]);
        
        exit;
    }
}

?>