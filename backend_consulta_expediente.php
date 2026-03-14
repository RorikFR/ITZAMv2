<?php
header('Content-Type: application/json');

//DEV ONLY - quitar en prod.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

// 2. MANEJO DE SOLICITUDES
$metodo = $_SERVER['REQUEST_METHOD'];


// --- LEER CATÁLOGOS PARA SELECTS ---
if ($metodo === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'cargar_catalogos') {
    
    // 1. Obtenemos las unidades médicas
    $stmtUnidades = $pdo->query("SELECT idUnidad, nombre FROM registro_unidad ORDER BY nombre ASC");
    $unidades = $stmtUnidades->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Obtenemos a los médicos (Concatenamos su nombre para que se vea bien en el select)
    $stmtMedicos = $pdo->query("SELECT 
                                    idPersonal, 
                                    CONCAT_WS(' ', nombre, apellido_p, apellido_m) AS nombre_completo 
                                FROM registro_personal 
                                ORDER BY nombre ASC");
    $medicos = $stmtMedicos->fetchAll(PDO::FETCH_ASSOC);
    
    // Devolvemos ambos arreglos en un solo JSON
    echo json_encode([
        "unidades" => $unidades,
        "medicos"  => $medicos
    ]);
    exit;
}

// --- LEER DATOS (BÚSQUEDA POR CURP) ---
if ($metodo === 'GET') {
    // Capturamos lo que el usuario escriba en la barra de búsqueda y limpiamos espacios
    $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    if ($busqueda) {
        // --- CASO 1: BÚSQUEDA ACTIVA ---
        $sql = "SELECT 
                    c.idConsulta, 
                    c.idPersonal,    /* <-- ¡Agrega esta línea! */
                    c.idUnidad,      /* <-- ¡Y esta línea! */
                    CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS nombre_paciente, 
                    p.curp, 
                    u.nombre AS unidad_medica, 
                    c.tipo_consulta, 
                    c.fecha_consulta, 
                    CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_que_atendio, 
                    m.cedula AS cedula_medico
                FROM registro_consultas c
                INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
                INNER JOIN registro_unidad u ON c.idUnidad = u.idUnidad
                WHERE p.curp LIKE :q
                ORDER BY c.fecha_consulta DESC";
        
        $stmt = $pdo->prepare($sql);
        // Usamos los comodines % por si ingresan solo una parte del CURP
        $stmt->execute(['q' => "%$busqueda%"]);
        
    } else {
        // --- CASO 2: CARGA INICIAL (SIN BÚSQUEDA) ---
        // Traemos los últimos 20 expedientes registrados
        $sql = "SELECT 
                    c.idConsulta, 
                    c.idPersonal,    /* <-- ¡Agrega esta línea! */
                    c.idUnidad,      /* <-- ¡Y esta línea! */
                    CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS nombre_paciente, 
                    p.curp, 
                    u.nombre AS unidad_medica, 
                    c.tipo_consulta, 
                    c.fecha_consulta, 
                    CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_que_atendio, 
                                    m.cedula AS cedula_medico
                FROM registro_consultas c
                INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
                INNER JOIN registro_unidad u ON c.idUnidad = u.idUnidad
                ORDER BY c.fecha_consulta DESC 
                LIMIT 20";
        
        // Como no hay variables dinámicas, podemos usar query() directo
        $stmt = $pdo->query($sql);
    }
    
    // Devolvemos los datos limpios al frontend
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
 

// --- EDITAR O ELIMINAR (HISTORIA CLÍNICA) ---
if ($metodo === 'POST') {
    // Recibimos los datos JSON del frontend
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';

    // Extraemos la llave primaria de la consulta médica
    $idConsulta = $input['idConsulta'] ?? 0; 

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idConsulta > 0) {
        
        try {
            // Eliminamos el evento de la tabla de consultas
            $stmt = $pdo->prepare("DELETE FROM registro_consultas WHERE idConsulta = :idConsulta");
            $stmt->execute(['idConsulta' => $idConsulta]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Consulta médica eliminada exitosamente del historial."
            ]);
            
        } catch (PDOException $e) {
            // Si la consulta ya tiene una receta generada o estudios asociados, el motor bloqueará el borrado
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "No se puede eliminar esta consulta porque ya tiene recetas médicas o estudios asociados."
            ]);
        }
        
        exit; 
    }

// --- LÓGICA DE EDICIÓN ---
    if ($accion === 'editar' && $idConsulta > 0) {
        
        $idPersonal = $input['idPersonal'] ?? 0;
        $idUnidad = $input['idUnidad'] ?? 0;
        $tipo_consulta = $input['tipo_consulta'] ?? '';
        
        // --- NUEVO ESCUDO DE VALIDACIÓN ---
        if ($idPersonal == 0 || $idUnidad == 0) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Por favor, selecciona un médico y una unidad médica válidos."
            ]);
            exit;
        }
        // ----------------------------------

        // PASO 1: Actualizar la tabla de consultas
        $stmt = $pdo->prepare("UPDATE registro_consultas 
            SET 
                idPersonal = :idPersonal,
                idUnidad = :idUnidad,
                tipo_consulta = :tipo_consulta
            WHERE idConsulta = :idConsulta");
            
        // ¡ESTA ES LA LÍNEA QUE FALTABA! Ejecutamos el UPDATE pasando el arreglo de datos
        $stmt->execute([
            'idPersonal'    => $idPersonal,
            'idUnidad'      => $idUnidad,
            'tipo_consulta' => $tipo_consulta,
            'idConsulta'    => $idConsulta
        ]);
        
        // PASO 2: Obtener la información actualizada para refrescar la interfaz
        // Usamos los mismos JOINs que en la vista principal
        $sqlObtener = "SELECT 
                            c.idConsulta, 
                            CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS nombre_paciente, 
                            p.curp, 
                            u.nombre AS unidad_medica, 
                            c.tipo_consulta, 
                            c.fecha_consulta, 
                            CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_que_atendio, 
                            m.cedula AS cedula_medico
                       FROM registro_consultas c
                       INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                       INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
                       INNER JOIN registro_unidad u ON c.idUnidad = u.idUnidad
                       WHERE c.idConsulta = :idConsulta";
                       
        $stmtObtener = $pdo->prepare($sqlObtener);
        $stmtObtener->execute(['idConsulta' => $idConsulta]);
        
        $datosActualizados = $stmtObtener->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Datos de la consulta actualizados correctamente.",
            "datos"   => $datosActualizados
        ]);
        
        exit;
    }
}
?>