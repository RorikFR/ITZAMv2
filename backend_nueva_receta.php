<?php
session_start();
header('Content-Type: application/json');
require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// --- GET: OBTENER MEDICAMENTOS DEL INVENTARIO (3FN) ---
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
        echo json_encode(["estatus" => "error", "mensaje" => "Error al cargar medicamentos: " . $e->getMessage()]);
    }
    exit;
}

// --- POST: GUARDAR RECETA (MAESTRO-DETALLE E INVENTARIO) ---
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Validación estricta de campos vacíos
    if (empty($input['idConsulta']) || empty($input['indicaciones_generales']) || empty($input['medicamentos'])) {
        echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios (Consulta, medicamentos o indicaciones)."]);
        exit;
    }

    $idConsulta_limpio = filter_var($input['idConsulta'], FILTER_VALIDATE_INT);
    $idUnidad_Doctor = $_SESSION['idUnidad'] ?? 1; 

    try {
        // 🔥 NUEVO ESCUDO: Verificar que la consulta realmente exista 🔥
        // (Y como extra de seguridad, verificamos que la consulta se haya dado en esta misma clínica)
        $stmtCheck = $pdo->prepare("SELECT idConsulta FROM registro_consultas WHERE idConsulta = :idConsulta AND idUnidad = :idUnidad");
        $stmtCheck->execute([
            'idConsulta' => $idConsulta_limpio,
            'idUnidad'   => $idUnidad_Doctor
        ]);

        if (!$stmtCheck->fetch()) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "El ID de Consulta ingresado no es válido, no existe o pertenece a otra clínica."
            ]);
            exit;
        }

        // Iniciamos la transacción (Todo o nada)
        $pdo->beginTransaction();

        // 1. Insertar la Cabecera de la Receta
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

        // Preparar consultas para el ciclo de medicamentos
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

        // 2. Iterar sobre el array de medicamentos
        foreach ($input['medicamentos'] as $med) {
            
            $dosis_limpia = strip_tags(trim($med['dosis']));
            $cantidad_limpia = filter_var($med['cantidad_surtir'], FILTER_VALIDATE_INT);
            $idMed_limpio = filter_var($med['idMed'], FILTER_VALIDATE_INT);

            // A. Insertar en la tabla receta_detalle
            $stmtDetalle->execute([
                'idReceta'        => $idRecetaGenerada,
                'idMed'           => $idMed_limpio,
                'dosis'           => $dosis_limpia,
                'cantidad_surtir' => $cantidad_limpia
            ]);

            // B. Descontar del inventario
            $stmtInventario->execute([
                'cantidad_surtir'    => $cantidad_limpia,
                'idMed'              => $idMed_limpio,
                'cantidad_validar'   => $cantidad_limpia,
                'idUnidad_seguridad' => $idUnidad_Doctor
            ]);

            if ($stmtInventario->rowCount() === 0) {
                throw new Exception("Stock insuficiente para el medicamento seleccionado.");
            }
        }

        // Confirmamos los cambios
        $pdo->commit();
        echo json_encode(["estatus" => "exito", "mensaje" => "Receta generada correctamente y stock actualizado."]);
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // 🛡️ ESCUDO FINAL: Capturar errores genéricos de Integridad de MySQL de forma elegante
        if ($e instanceof PDOException && $e->getCode() == 23000) {
             echo json_encode(["estatus" => "error", "mensaje" => "Error de integridad: El registro entra en conflicto con datos existentes."]);
        } else {
             echo json_encode(["estatus" => "error", "mensaje" => "Error: " . $e->getMessage()]);
        }
    }
    exit;
}
?>