<?php

//Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';

require 'db_conn.php';

//RBAC
requerir_roles_api(['Médico']);

$metodo = $_SERVER['REQUEST_METHOD'];

//Obtener datos
if ($metodo === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'obtener_medicamentos') {
    $idUnidad_Doctor = $_SESSION['idUnidad'] ?? 1; 

    try {
        $stmt = $pdo->prepare("
            SELECT 
                i.idMed, 
                c.nombre, 
                c.presentacion, 
                c.concentracion, 
                i.cantidad 
            FROM inventario_medicamentos i
            INNER JOIN cat_medicamentos c ON i.idCatalogoMed = c.idCatalogoMed
            WHERE i.cantidad > 0 
              AND i.fecha_caducidad >= CURDATE()
              AND i.idUnidad = :idUnidad
            ORDER BY c.nombre ASC
        ");
        $stmt->execute(['idUnidad' => $idUnidad_Doctor]);
        
        echo json_encode(["estatus" => "exito", "datos" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error interno al cargar el inventario."]);
    }
    exit;
}

//Guardar receta y descontar en inventario
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    //Validar campos vacios
    if (empty($input['idConsulta']) || empty($input['indicaciones_generales']) || empty($input['medicamentos'])) {
        echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios (Consulta, medicamentos o indicaciones)."]);
        exit;
    }

    $idConsulta_limpio = filter_var($input['idConsulta'], FILTER_VALIDATE_INT);
    $idUnidad_Doctor = $_SESSION['idUnidad'] ?? 1; 

    try {
        //Validar que la consulta exista
        $stmtCheck = $pdo->prepare("SELECT idConsulta FROM registro_consultas WHERE idConsulta = :idConsulta AND idUnidad = :idUnidad");
        $stmtCheck->execute([
            'idConsulta' => $idConsulta_limpio,
            'idUnidad'   => $idUnidad_Doctor
        ]);

        if (!$stmtCheck->fetch()) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "El ID de Consulta ingresado no es válido o pertenece a otra clínica."
            ]);
            exit;
        }

        $pdo->beginTransaction();

        //Insertar datos de proxima consulta e indicaciones
        $prox_consulta = !empty($input['prox_consulta']) ? $input['prox_consulta'] : null;
        $indicaciones_limpias = strip_tags(trim($input['indicaciones_generales']));

        $stmtReceta = $pdo->prepare("
            INSERT INTO registro_receta (idConsulta, prox_consulta, indicaciones_generales) 
            VALUES (:idConsulta, :prox_consulta, :indicaciones_generales)
        ");
        
        $stmtReceta->execute([
            'idConsulta'             => $idConsulta_limpio,
            'prox_consulta'          => $prox_consulta,
            'indicaciones_generales' => $indicaciones_limpias
        ]);
        
        $idRecetaGenerada = $pdo->lastInsertId();

        // Agregar a detalle de receta
        $stmtDetalle = $pdo->prepare("
            INSERT INTO receta_detalle (idReceta, idMed, dosis, cantidad_surtir) 
            VALUES (:idReceta, :idMed, :dosis, :cantidad_surtir)
        ");

        $stmtInventario = $pdo->prepare("
            UPDATE inventario_medicamentos 
            SET cantidad = cantidad - :cantidad_surtir 
            WHERE idMed = :idMed 
              AND cantidad >= :cantidad_validar 
              AND idUnidad = :idUnidad_seguridad
        ");

        //Obtener medicamentos
        foreach ($input['medicamentos'] as $med) {
            
            $dosis_limpia    = strip_tags(trim($med['dosis']));
            $cantidad_limpia = filter_var($med['cantidad_surtir'], FILTER_VALIDATE_INT);
            $idMed_limpio    = filter_var($med['idMed'], FILTER_VALIDATE_INT);

            if (!$cantidad_limpia || !$idMed_limpio || empty($dosis_limpia)) {
                throw new Exception("Datos de medicamento inválidos detectados.");
            }

            //Insertar datos
            $stmtDetalle->execute([
                'idReceta'        => $idRecetaGenerada,
                'idMed'           => $idMed_limpio,
                'dosis'           => $dosis_limpia,
                'cantidad_surtir' => $cantidad_limpia
            ]);

            //Descontar del inventario
            $stmtInventario->execute([
                'cantidad_surtir'    => $cantidad_limpia,
                'idMed'              => $idMed_limpio,
                'cantidad_validar'   => $cantidad_limpia,
                'idUnidad_seguridad' => $idUnidad_Doctor
            ]);

            if ($stmtInventario->rowCount() === 0) {
                throw new Exception("Stock insuficiente o medicamento no disponible en la clínica actual.");
            }
        }

        //Confirmar cambios
        $pdo->commit();
        echo json_encode(["estatus" => "exito", "mensaje" => "Receta generada correctamente y stock actualizado."]);
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        if ($e instanceof PDOException && $e->getCode() == 23000) {
             echo json_encode(["estatus" => "error", "mensaje" => "Error de integridad: El registro entra en conflicto con datos existentes."]);
        } else {
             $error_msg = ($e instanceof PDOException) ? "Error interno del servidor." : $e->getMessage();
             echo json_encode(["estatus" => "error", "mensaje" => $error_msg]);
        }
    }
    exit;
}

echo json_encode(["estatus" => "error", "mensaje" => "Método no permitido."]);
exit;
?>