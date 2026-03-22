<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';      


//RBAC
requerir_roles_api(['Médico', 'Administrativo', 'Enfermería']); 

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

//Carga de datos catalogos
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


//Expediente clínico
if ($metodo === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'obtener_historial') {
    $curp = filter_var($_GET['curp'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    if (empty($curp)) {
        echo json_encode(["error" => "CURP no proporcionado."]);
        exit;
    }

    try {
        //Datos del paciente (alergias, antecedentes)
        $stmtPaciente = $pdo->prepare("
            SELECT 
                p.idPaciente, 
                p.nombre, 
                p.apellido_p, 
                p.apellido_m, 
                p.fecha_nac, 
                p.genero,
                (SELECT alergias FROM registro_consultas c 
                 WHERE c.idPaciente = p.idPaciente AND c.alergias IS NOT NULL AND c.alergias != '' 
                 ORDER BY c.fecha_consulta DESC LIMIT 1) AS alergias,
                (SELECT antecedentes FROM registro_consultas c 
                 WHERE c.idPaciente = p.idPaciente AND c.antecedentes IS NOT NULL AND c.antecedentes != '' 
                 ORDER BY c.fecha_consulta DESC LIMIT 1) AS antecedentes
            FROM registro_paciente p 
            WHERE p.curp = :curp LIMIT 1
        ");
        
        $stmtPaciente->execute(['curp' => $curp]);
        $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

        if (!$paciente) {
            echo json_encode(["error" => "Paciente no encontrado."]);
            exit;
        }

        //Obtener datos de consultas médicas y signos
        $stmtConsultas = $pdo->prepare("
            SELECT 
                c.idConsulta, c.fecha_consulta, c.sintomas, c.diagnostico, c.tratamiento,
                c.presion_arte, c.peso, c.temperatura, c.freq_card, c.sat_oxigeno,
                tc.nombre_tipo AS tipo_consulta,
                u.nombre AS unidad_medica,
                CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico
            FROM registro_consultas c
            INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
            INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
            INNER JOIN registro_unidad u ON c.idUnidad = u.idUnidad 
            LEFT JOIN cat_tipo_consulta tc ON c.idTipoConsulta = tc.idTipoConsulta
            WHERE p.curp = :curp
            ORDER BY c.fecha_consulta DESC
        ");
        $stmtConsultas->execute(['curp' => $curp]);
        $consultas = $stmtConsultas->fetchAll(PDO::FETCH_ASSOC);

        // Aqui podemos integrar recetas y estudios de laboratorio
        $recetas = []; 
        $laboratorios = []; 

        // Empaquetar JSON
        echo json_encode([
            "estatus" => "exito",
            "paciente" => $paciente,
            "historial" => $consultas,
            "recetas" => $recetas,
            "laboratorios" => $laboratorios
        ]);

    } catch (PDOException $e) {
        echo json_encode(["error" => "Error interno al recuperar el expediente profundo."]);
    }
    exit;
}

//Buscar por CURP
if ($metodo === 'GET' && empty($_GET['accion'])) {
    $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    $sql_base = "SELECT 
                    c.idConsulta, 
                    c.idPersonal, 
                    c.idUnidad, 
                    c.idTipoConsulta, 
                    CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS nombre_paciente, 
                    p.curp, 
                    u.nombre AS unidad_medica, 
                    tc.nombre_tipo AS tipo_consulta,
                    c.fecha_consulta, 
                    CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_que_atendio, 
                    m.cedula AS cedula_medico,
                    c.sintomas,
                    c.diagnostico,
                    c.tratamiento
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
 
//Editar o eliminar registros
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idConsulta = $input['idConsulta'] ?? 0; 

    //Eliminar registro
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

    // Editar registro
    if ($accion === 'editar' && $idConsulta > 0) {
        
        $idPersonal = $input['idPersonal'] ?? 0;
        $idUnidad = $input['idUnidad'] ?? 0;
        $idTipoConsulta = $input['idTipoConsulta'] ?? '';
        
        // Validar datos
        if ($idPersonal == 0 || $idUnidad == 0 || empty($idTipoConsulta)) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Por favor, selecciona un médico, unidad y tipo de consulta válidos."
            ]);
            exit;
        }

        try {
            // Actualizar tabla
            $stmt = $pdo->prepare("UPDATE registro_consultas 
                SET 
                    idPersonal = :idPersonal,
                    idUnidad = :idUnidad,
                    idTipoConsulta = :idTipoConsulta 
                WHERE idConsulta = :idConsulta");
                
            $stmt->execute([
                'idPersonal'     => $idPersonal,
                'idUnidad'       => $idUnidad,
                'idTipoConsulta' => $idTipoConsulta,
                'idConsulta'     => $idConsulta
            ]);
            
            // Obtener datos actualizados
            $sqlObtener = "SELECT 
                                c.idConsulta, 
                                CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS nombre_paciente, 
                                p.curp, 
                                u.nombre AS unidad_medica, 
                                tc.nombre_tipo AS tipo_consulta, 
                                c.fecha_consulta, 
                                CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_que_atendio, 
                                m.cedula AS cedula_medico,
                                c.sintomas,
                                c.diagnostico,
                                c.tratamiento
                           FROM registro_consultas c
                           INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                           INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
                           INNER JOIN registro_unidad u ON c.idUnidad = u.idUnidad
                           LEFT JOIN cat_tipo_consulta tc ON c.idTipoConsulta = tc.idTipoConsulta
                           WHERE c.idConsulta = :idConsulta";
                           
            $stmtObtener = $pdo->prepare($sqlObtener);
            $stmtObtener->execute(['idConsulta' => $idConsulta]);
            $datosActualizados = $stmtObtener->fetch(PDO::FETCH_ASSOC);
            
            //Empaquetar JSON
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