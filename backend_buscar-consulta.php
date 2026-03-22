<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';      

// RBAC
requerir_roles_api(['Médico', 'Administrativo', 'Enfermería']); 

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

//Cargar datos para DataTables
if ($metodo === 'GET') {

    try {
        $sql = "SELECT 
                    c.idConsulta, 
                    c.idPersonal,
                    p.nombre, 
                    p.apellido_p,
                    p.apellido_m,
                    p.curp, 
                    p.fecha_nac, 
                    p.genero, 
                    tc.nombre_tipo AS tipo_consulta,
                    c.idTipoConsulta,                
                    CONCAT_WS(' ', med.nombre, med.apellido_p, med.apellido_m) AS personal_medico 
                FROM registro_consultas c
                INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                LEFT JOIN cat_tipo_consulta tc ON c.idTipoConsulta = tc.idTipoConsulta
                LEFT JOIN registro_personal med ON c.idPersonal = med.idPersonal
                ORDER BY c.idConsulta DESC";
                
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error interno al consultar las bases de datos."]);
    }
    exit;
}


if ($metodo === 'POST') {
    
    // Recibimos los datos JSON del frontend
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    
    $accion = filter_var($input['accion'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $idConsulta = filter_var($input['idConsulta'] ?? 0, FILTER_VALIDATE_INT);

    // Trazabilidad del registro
    if ($idConsulta > 0 && ($accion === 'editar' || $accion === 'eliminar')) {
        $stmtAutor = $pdo->prepare("SELECT idPersonal FROM registro_consultas WHERE idConsulta = :id");
        $stmtAutor->execute(['id' => $idConsulta]);
        $autor = $stmtAutor->fetch(PDO::FETCH_ASSOC);

        // Validar ID del usuario que inicio sesión
        if (!$autor || ($autor['idPersonal'] != $_SESSION['idUsuario'] && $_SESSION['rol'] !== 'Administrador')) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Acceso denegado: Solo el médico tratante que registró esta consulta puede modificarla o eliminarla."
            ]);
            exit; 
        }
    }

    // Eliminar registro
    if ($accion === 'eliminar' && $idConsulta > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM registro_consultas WHERE idConsulta = :idConsulta");
            $stmt->execute(['idConsulta' => $idConsulta]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Expediente de consulta eliminado permanentemente del sistema."
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "No se puede eliminar la consulta por restricciones en la base de datos (posiblemente tenga recetas o laboratorios asociados)."
            ]);
        }
        exit;
    }

    // Editar registro
    if ($accion === 'editar' && $idConsulta > 0) {
        
        $idTipoConsulta = filter_var($input['idTipoConsulta'] ?? null, FILTER_VALIDATE_INT); 
        
        if (!$idTipoConsulta) {
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
                "mensaje" => "Error interno al actualizar la consulta."
            ]);
        }
        exit;
    }
}
?>