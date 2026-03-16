<?php
header('Content-Type: application/json');

// DEV ONLY - quitar en prod.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// ==========================================
// --- LEER CATÁLOGOS PARA SELECTS ---
// ==========================================
if ($metodo === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'cargar_catalogos') {
    
    $stmtUnidades = $pdo->query("SELECT idUnidad, nombre FROM registro_unidad ORDER BY nombre ASC");
    $unidades = $stmtUnidades->fetchAll(PDO::FETCH_ASSOC);
    
    $stmtMedicos = $pdo->query("SELECT 
                                    idPersonal, 
                                    CONCAT_WS(' ', nombre, apellido_p, apellido_m) AS nombre_completo 
                                FROM registro_personal 
                                ORDER BY nombre ASC");
    $medicos = $stmtMedicos->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "unidades" => $unidades,
        "medicos"  => $medicos
    ]);
    exit;
}

// ==========================================
// --- LEER DATOS (BÚSQUEDA POR CURP O GENERAL) ---
// ==========================================
if ($metodo === 'GET') {
    $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    // Creamos la base de la consulta para no repetir código (¡Y con el nuevo JOIN 3FN!)
    $sql_base = "SELECT 
                    c.idConsulta, 
                    c.idPersonal, 
                    c.idUnidad, 
                    c.idTipoConsulta, -- 👈 Extraemos el ID para el Modal
                    CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS nombre_paciente, 
                    p.curp, 
                    u.nombre AS unidad_medica, 
                    tc.nombre_tipo AS tipo_consulta, -- 👈 Extraemos el texto para la Tabla
                    c.fecha_consulta, 
                    CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_que_atendio, 
                    m.cedula AS cedula_medico
                FROM registro_consultas c
                INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
                INNER JOIN registro_unidad u ON c.idUnidad = u.idUnidad
                LEFT JOIN cat_tipo_consulta tc ON c.idTipoConsulta = tc.idTipoConsulta";

    if ($busqueda) {
        $sql = $sql_base . " WHERE p.curp LIKE :q ORDER BY c.fecha_consulta DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['q' => "%$busqueda%"]);
    } else {
        $sql = $sql_base . " ORDER BY c.fecha_consulta DESC";
        $stmt = $pdo->query($sql);
    }
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
 
// ==========================================
// --- EDITAR O ELIMINAR (HISTORIA CLÍNICA) ---
// ==========================================
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idConsulta = $input['idConsulta'] ?? 0; 

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idConsulta > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM registro_consultas WHERE idConsulta = :idConsulta");
            $stmt->execute(['idConsulta' => $idConsulta]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Consulta médica eliminada exitosamente del historial."
            ]);
            
        } catch (PDOException $e) {
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
        // 🔥 AHORA ESPERAMOS EL ID DEL TIPO DE CONSULTA
        $idTipoConsulta = $input['idTipoConsulta'] ?? '';
        
        // ESCUDO DE VALIDACIÓN ACTUALIZADO
        if ($idPersonal == 0 || $idUnidad == 0 || empty($idTipoConsulta)) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Por favor, selecciona un médico, unidad y tipo de consulta válidos."
            ]);
            exit;
        }

        try {
            // PASO 1: Actualizar la tabla de consultas
            $stmt = $pdo->prepare("UPDATE registro_consultas 
                SET 
                    idPersonal = :idPersonal,
                    idUnidad = :idUnidad,
                    idTipoConsulta = :idTipoConsulta -- 👈 Actualizamos la llave foránea
                WHERE idConsulta = :idConsulta");
                
            $stmt->execute([
                'idPersonal'     => $idPersonal,
                'idUnidad'       => $idUnidad,
                'idTipoConsulta' => $idTipoConsulta,
                'idConsulta'     => $idConsulta
            ]);
            
            // PASO 2: Obtener la información actualizada
            $sqlObtener = "SELECT 
                                c.idConsulta, 
                                CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS nombre_paciente, 
                                p.curp, 
                                u.nombre AS unidad_medica, 
                                tc.nombre_tipo AS tipo_consulta, -- 👈 Con el catálogo
                                c.fecha_consulta, 
                                CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_que_atendio, 
                                m.cedula AS cedula_medico
                           FROM registro_consultas c
                           INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                           INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
                           INNER JOIN registro_unidad u ON c.idUnidad = u.idUnidad
                           LEFT JOIN cat_tipo_consulta tc ON c.idTipoConsulta = tc.idTipoConsulta
                           WHERE c.idConsulta = :idConsulta";
                           
            $stmtObtener = $pdo->prepare($sqlObtener);
            $stmtObtener->execute(['idConsulta' => $idConsulta]);
            $datosActualizados = $stmtObtener->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Datos de la consulta actualizados correctamente.",
                "datos"   => $datosActualizados
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Error interno al actualizar la consulta en la base de datos."
            ]);
        }
        exit;
    }
}
?>