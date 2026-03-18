<?php
session_start();
header('Content-Type: application/json');
require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // MODO DESARROLLADOR: Bypass temporal de sesión.
    $idUnidad_Doctor = $_SESSION['idUnidad'] ?? 2; 

    if (!$idUnidad_Doctor) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error: Unidad Médica no detectada en la sesión."]);
        exit;
    }

    $idCatalogoMed = $input['idCatalogoMed'] ?? '';

    // ESCUDO 1: VALIDACIÓN DE INVENTARIO FÍSICO (Siempre obligatorios)
    $lote            = strip_tags(trim($input['lote'] ?? ''));
    $fecha_caducidad = trim($input['fecha_caducidad'] ?? '');
    $idProveedor     = filter_var($input['idProveedor'] ?? '', FILTER_VALIDATE_INT);
    $cantidad        = filter_var($input['cantidad'] ?? '', FILTER_VALIDATE_INT);

    if (!$idProveedor || !$cantidad || $cantidad <= 0 || empty($lote) || empty($fecha_caducidad)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Datos de inventario (Proveedor, cantidad, lote o caducidad) inválidos o vacíos."]);
        exit;
    }

    $hoy = date('Y-m-d');
    if ($fecha_caducidad <= $hoy) {
        echo json_encode(["estatus" => "error", "mensaje" => "La fecha de caducidad no puede ser hoy ni una fecha pasada."]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // FLUJO A: REGISTRAR UN MEDICAMENTO TOTALMENTE NUEVO EN EL CATÁLOGO
        if ($idCatalogoMed === 'nuevo') {
            
            // Sanitizamos los datos del diccionario
            $nombre           = strip_tags(trim($input['nombre'] ?? ''));
            $marca            = strip_tags(trim($input['marca'] ?? ''));
            $presentacion     = strip_tags(trim($input['presentacion'] ?? ''));
            $via_adm          = strip_tags(trim($input['via_adm'] ?? ''));
            $principio_activo = strip_tags(trim($input['principio_activo'] ?? ''));
            $concentracion    = strip_tags(trim($input['concentracion'] ?? ''));
            $refrigerado      = filter_var($input['refrigerado'] ?? '', FILTER_VALIDATE_INT);

            if (empty($nombre) || empty($marca) || empty($principio_activo) || $refrigerado === false) {
                echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios para crear el medicamento en el catálogo."]);
                $pdo->rollBack();
                exit;
            }

            // Insertamos en el diccionario oficial
            $stmtCat = $pdo->prepare("
                INSERT INTO cat_medicamentos (
                    nombre, marca, presentacion, via_adm, principio_activo, concentracion, refrigerado
                ) VALUES (
                    :nombre, :marca, :presentacion, :via_adm, :principio_activo, :concentracion, :refrigerado
                )
            ");
            $stmtCat->execute([
                'nombre'           => $nombre,
                'marca'            => $marca,
                'presentacion'     => $presentacion,
                'via_adm'          => $via_adm,
                'principio_activo' => $principio_activo,
                'concentracion'    => $concentracion,
                'refrigerado'      => $refrigerado
            ]);

            // Capturamos el ID que se acaba de generar
            $idCatalogoFinal = $pdo->lastInsertId();

        } else {
            // FLUJO B: EL MEDICAMENTO YA EXISTE EN EL CATÁLOGO
            $idCatalogoFinal = filter_var($idCatalogoMed, FILTER_VALIDATE_INT);
            if (!$idCatalogoFinal) {
                echo json_encode(["estatus" => "error", "mensaje" => "ID de catálogo inválido."]);
                $pdo->rollBack();
                exit;
            }
        }

        // --- PASO FINAL: INGRESAR LAS CAJAS A LA BODEGA ---
        // Insertamos en inventario usando el ID del catálogo (sea el nuevo o el que seleccionó del select)
        $stmtInv = $pdo->prepare("
            INSERT INTO inventario_medicamentos (
                idCatalogoMed, idUnidad, idProveedor, cantidad, lote, fecha_caducidad
            ) VALUES (
                :idCat, :idUnidad, :idProveedor, :cantidad, :lote, :fecha
            )
        ");
        
        $stmtInv->execute([
            'idCat'       => $idCatalogoFinal,
            'idUnidad'    => $idUnidad_Doctor,
            'idProveedor' => $idProveedor,
            'cantidad'    => $cantidad,
            'lote'        => $lote,
            'fecha'       => $fecha_caducidad
        ]);
        
        $pdo->commit();
        echo json_encode(["estatus" => "exito", "mensaje" => "Lote ingresado correctamente al inventario de farmacia."]);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        echo json_encode(["estatus" => "error", "mensaje" => "Error de base de datos: " . $e->getMessage()]);
    }
    exit;
}
?>