<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';      

//RBAC
requerir_roles_api(['Médico', 'Administrativo', 'Enfermería']); 

require 'db_conn.php';

//Manejo de solicitudes
$metodo = $_SERVER['REQUEST_METHOD'];


if ($metodo === 'GET') {
    //Sanitizar entradas
    $busqueda = isset($_GET['q']) ? filter_var(trim($_GET['q']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    
    if ($busqueda) {
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
        $stmt->execute(['q' => "%$busqueda%"]);
        
    } else {
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
                ORDER BY p.idPaciente DESC LIMIT 100"; // Limite de registros a cargar en memoria 
        
        $stmt = $pdo->query($sql);
    }
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
 

//Editar y eliminar
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idPaciente = intval($input['idPaciente'] ?? 0);

    //Eliminar registros
    if ($accion === 'eliminar' && $idPaciente > 0) {
        
        try {
            $stmt = $pdo->prepare("DELETE FROM registro_paciente WHERE idPaciente = :idPaciente");
            $stmt->execute(['idPaciente' => $idPaciente]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Expediente del paciente eliminado exitosamente."
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "No se puede eliminar este expediente porque el paciente ya tiene consultas, recetas o estudios asociados en ITZAM."
            ]);
        }
        exit; 
    }

    //Editar registros
    if ($accion === 'editar' && $idPaciente > 0) {
        
        //Sanitizar entradas
        $telefono = trim($input['telefono'] ?? '');
        $correo_electronico = trim($input['correo_electronico'] ?? '');
        
        //Regex para campo de telefono
        if (!preg_match('/^[0-9]{10}$/', $telefono)) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Ingresa un número de teléfono válido. Debe contener exactamente 10 dígitos numéricos."
            ]);
            exit;
        }

        if (!empty($correo_electronico) && !filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "El formato del correo electrónico ingresado no es válido."
            ]);
            exit;
        }

        try {
            //Actualizar datos en tabla
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
            
            //Obtener datos
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
            $datosActualizados = $stmtObtener->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Datos de contacto del paciente actualizados correctamente.",
                "datos"   => $datosActualizados
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Ocurrió un error interno en el servidor al intentar guardar los cambios. Intente nuevamente."
            ]);
        }
        exit;
    }
}
?>