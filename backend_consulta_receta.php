<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';     

// RBAC
requerir_roles_api(['Médico', 'Enfermería']); 

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

//Carga de datos
if ($metodo === 'GET') {
    
    //Estructurar datos para documento de receta
    if (isset($_GET['accion']) && $_GET['accion'] === 'obtener_receta_pdf') {
        $idReceta = isset($_GET['idReceta']) ? filter_var($_GET['idReceta'], FILTER_VALIDATE_INT) : 0;
        
        if (!$idReceta) {
            echo json_encode(["estatus" => "error", "mensaje" => "ID de receta inválido."]);
            exit;
        }

        try {
            // Obtener cabecera (Médico, Paciente, Consulta y Diagnóstico)
            $sqlMaestra = "SELECT 
                            r.idReceta, 
                            r.indicaciones_generales, 
                            r.prox_consulta,
                            c.fecha_consulta,
                            c.diagnostico, /* <-- ESTA ES LA LÍNEA NUEVA */
                            CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS paciente_nombre,
                            TIMESTAMPDIFF(YEAR, p.fecha_nac, CURDATE()) AS edad,
                            CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico_nombre,
                            m.cedula AS medico_cedula,
                            m.cedula_esp AS medico_cedula_esp,
                            ce.nombre_especialidad AS especialidad
                           FROM registro_receta r
                           INNER JOIN registro_consultas c ON r.idConsulta = c.idConsulta
                           INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                           INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
                           LEFT JOIN cat_especialidades ce ON m.idEspecialidad = ce.idEspecialidad
                           WHERE r.idReceta = :idReceta LIMIT 1";
                           
            $stmtMaestra = $pdo->prepare($sqlMaestra);
            $stmtMaestra->execute(['idReceta' => $idReceta]);
            $datosReceta = $stmtMaestra->fetch(PDO::FETCH_ASSOC);

            if (!$datosReceta) {
                echo json_encode(["estatus" => "error", "mensaje" => "La receta solicitada no existe."]);
                exit;
            }

            // Obtener lista de medicamentos (Detalle)
            $sqlDetalle = "SELECT 
                            cm.nombre, 
                            cm.concentracion, 
                            cm.presentacion, 
                            d.dosis, 
                            d.cantidad_surtir
                           FROM receta_detalle d
                           INNER JOIN inventario_medicamentos i ON d.idMed = i.idMed
                           INNER JOIN cat_medicamentos cm ON i.idCatalogoMed = cm.idCatalogoMed
                           WHERE d.idReceta = :idReceta";
                           
            $stmtDetalle = $pdo->prepare($sqlDetalle);
            $stmtDetalle->execute(['idReceta' => $idReceta]);
            $datosReceta['medicamentos'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(["estatus" => "exito", "datos" => $datosReceta]);
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error interno al generar estructura de la receta."]);
        }
        exit;
    }

    //Sanitizar campo busqueda
    $busqueda = isset($_GET['q']) ? filter_var(trim($_GET['q']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    
    // Carga de datos 
    if($busqueda) {
        $sql = "SELECT 
                    r.idReceta, 
                    p.curp AS curp_paciente, 
                    CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS paciente,
                    
                    /* Agrupar medicamentos y dosis para recetas con más de 1 medicamento */
                    GROUP_CONCAT(cm.nombre SEPARATOR ', ') AS medicamento, 
                    GROUP_CONCAT(d.dosis SEPARATOR ' | ') AS dosis, 
                    SUM(d.cantidad_surtir) AS cantidad_surtir, 
                    
                    r.indicaciones_generales, 
                    r.prox_consulta, 
                    CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico
                FROM registro_receta r
                INNER JOIN registro_consultas c ON r.idConsulta = c.idConsulta
                INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
                INNER JOIN receta_detalle d ON r.idReceta = d.idReceta                    
                INNER JOIN inventario_medicamentos i ON d.idMed = i.idMed
                INNER JOIN cat_medicamentos cm ON i.idCatalogoMed = cm.idCatalogoMed
                WHERE p.curp LIKE :q
                GROUP BY r.idReceta 
                ORDER BY r.idReceta DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['q' => "%$busqueda%"]);
    } else {
        $stmt = $pdo->query("SELECT 
                                r.idReceta, 
                                p.curp AS curp_paciente, 
                                CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS paciente,
                                
                                /* Agrupar medicamentos y dosis para recetas con más de 1 medicamento */
                                GROUP_CONCAT(cm.nombre SEPARATOR ', ') AS medicamento, 
                                GROUP_CONCAT(d.dosis SEPARATOR ' | ') AS dosis, 
                                SUM(d.cantidad_surtir) AS cantidad_surtir, 
                                
                                r.prox_consulta, 
                                CONCAT_WS(' ', m.nombre, m.apellido_p, m.apellido_m) AS medico
                            FROM registro_receta r
                            INNER JOIN registro_consultas c ON r.idConsulta = c.idConsulta
                            INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
                            INNER JOIN registro_personal m ON c.idPersonal = m.idPersonal
                            INNER JOIN receta_detalle d ON r.idReceta = d.idReceta
                            INNER JOIN inventario_medicamentos i ON d.idMed = i.idMed
                            INNER JOIN cat_medicamentos cm ON i.idCatalogoMed = cm.idCatalogoMed
                            GROUP BY r.idReceta 
                            ORDER BY r.idReceta DESC LIMIT 200");
    }
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}


//Editar y eliminar registros
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idReceta = intval($input['idReceta'] ?? 0); 

    // Eliminar registros y recuperar inventario
    if ($accion === 'eliminar' && $idReceta > 0) {
        
        try {
            $pdo->beginTransaction();

            // Consultar medicamentos y cantidades
            $stmtMeds = $pdo->prepare("SELECT idMed, cantidad_surtir FROM receta_detalle WHERE idReceta = :idReceta");
            $stmtMeds->execute(['idReceta' => $idReceta]);
            $medicamentosARestaurar = $stmtMeds->fetchAll(PDO::FETCH_ASSOC);

            // Restaurar stock
            $stmtRestaurar = $pdo->prepare("UPDATE inventario_medicamentos SET cantidad = cantidad + :cantidad WHERE idMed = :idMed");
            foreach ($medicamentosARestaurar as $med) {
                $stmtRestaurar->execute([
                    'cantidad' => $med['cantidad_surtir'],
                    'idMed'    => $med['idMed']
                ]);
            }

            // Eliminar detalles de receta
            $stmtDetalle = $pdo->prepare("DELETE FROM receta_detalle WHERE idReceta = :idReceta");
            $stmtDetalle->execute(['idReceta' => $idReceta]);
            
            // Eliminar registro principal
            $stmtPrincipal = $pdo->prepare("DELETE FROM registro_receta WHERE idReceta = :idReceta");
            $stmtPrincipal->execute(['idReceta' => $idReceta]);
            
            $pdo->commit();

            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Receta eliminada y los medicamentos fueron restaurados al inventario exitosamente."
            ]);
            
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Ocurrió un error en el servidor al intentar eliminar la receta y restaurar el stock."
            ]);
        }
        exit; 
    }

    // Editar registros
    if ($accion === 'editar' && $idReceta > 0) {
        
        $prox_consulta = trim($input['prox_consulta'] ?? '');
        if (empty($prox_consulta)) { 
            $prox_consulta = null; 
        } else {
            $d = DateTime::createFromFormat('Y-m-d', $prox_consulta);
            if (!$d || $d->format('Y-m-d') !== $prox_consulta) {
                echo json_encode(["estatus" => "error", "mensaje" => "El formato de la fecha de próxima consulta es inválido."]);
                exit;
            }
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE registro_receta 
                SET prox_consulta = :prox_consulta
                WHERE idReceta = :idReceta");
            
            $stmt->execute([
                'prox_consulta' => $prox_consulta,
                'idReceta'      => $idReceta
            ]);
            
            // Obtener la información actualizada
            $sqlObtener = "SELECT 
                                r.idReceta, 
                                p.curp AS curp_paciente, 
                                CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS paciente,
                                cm.nombre AS medicamento, 
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
                           INNER JOIN cat_medicamentos cm ON i.idCatalogoMed = cm.idCatalogoMed
                           WHERE r.idReceta = :idReceta";
                           
            $stmtObtener = $pdo->prepare($sqlObtener);
            $stmtObtener->execute(['idReceta' => $idReceta]);
            
            $datosActualizados = $stmtObtener->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Fecha de próxima consulta actualizada correctamente.",
                "datos"   => $datosActualizados
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Ocurrió un error al actualizar el registro."
            ]);
        }
        exit;
    }
}
?>