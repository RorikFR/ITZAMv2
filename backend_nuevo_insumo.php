<?php
session_start();
header('Content-Type: application/json');

// DEV ONLY - quitar en producción
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // 🛡️ REGLA DE NEGOCIO: La unidad médica se extrae de la sesión
    $idUnidad_Usuario = $_SESSION['idUnidad'] ?? 2; // (Bypass temporal a 2)

    if (!$idUnidad_Usuario) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error de sesión: No se detectó tu Unidad Médica."]);
        exit;
    }

    $idCatalogoInsumo = $input['idCatalogoInsumo'] ?? '';

    // --- ESCUDO 1: SANITIZACIÓN Y TIPOS (Bodega) ---
    $idProveedor = filter_var($input['idProveedor'] ?? '', FILTER_VALIDATE_INT);
    $cantidad    = filter_var($input['cantidad'] ?? '', FILTER_VALIDATE_INT);
    
    $marca           = strip_tags(trim($input['marca'] ?? ''));
    $lote            = strip_tags(trim($input['lote'] ?? ''));
    $fecha_caducidad = trim($input['fecha_caducidad'] ?? '');

    if (!$idProveedor || !$cantidad || $cantidad <= 0) {
        echo json_encode(["estatus" => "error", "mensaje" => "El proveedor y una cantidad mayor a 0 son obligatorios."]);
        exit;
    }

    // --- ESCUDO 2: LONGITUDES DE LA BODEGA ---
    if (strlen($marca) > 45 || strlen($lote) > 45) {
        echo json_encode(["estatus" => "error", "mensaje" => "La marca o el lote exceden el límite de 45 caracteres."]);
        exit;
    }

    // --- ESCUDO 3: VALIDACIÓN LÓGICA DE FECHAS ---
    if (!empty($fecha_caducidad)) {
        $hoy = date('Y-m-d');
        if ($fecha_caducidad <= $hoy) {
            echo json_encode(["estatus" => "error", "mensaje" => "La fecha de caducidad no puede ser hoy ni una fecha pasada."]);
            exit;
        }
    } else {
        $fecha_caducidad = null; 
    }

    if (empty($lote)) { $lote = null; }
    if (empty($marca)) { $marca = null; }

    try {
        $pdo->beginTransaction();

        // FLUJO A: REGISTRAR UN INSUMO TOTALMENTE NUEVO EN EL CATÁLOGO
        if ($idCatalogoInsumo === 'nuevo') {
            
            // Sanitizamos los datos del diccionario
            $nombre        = strip_tags(trim($input['nombre'] ?? ''));
            $material      = strip_tags(trim($input['material'] ?? ''));
            $presentacion  = strip_tags(trim($input['presentacion'] ?? ''));
            $tamano        = strip_tags(trim($input['tamano'] ?? ''));
            $piezas_unidad = filter_var($input['piezas_unidad'] ?? null, FILTER_VALIDATE_INT);

            if (empty($nombre)) {
                echo json_encode(["estatus" => "error", "mensaje" => "El nombre del insumo es obligatorio para el catálogo."]);
                $pdo->rollBack();
                exit;
            }

            // --- ESCUDO 4: LONGITUDES DEL CATÁLOGO ---
            if (strlen($nombre) > 120) {
                echo json_encode(["estatus" => "error", "mensaje" => "El nombre excede los 120 caracteres permitidos."]);
                $pdo->rollBack(); exit;
            }
            if (strlen($material) > 65 || strlen($presentacion) > 65) {
                echo json_encode(["estatus" => "error", "mensaje" => "El material o presentación exceden los 65 caracteres."]);
                $pdo->rollBack(); exit;
            }
            if (strlen($tamano) > 45) {
                echo json_encode(["estatus" => "error", "mensaje" => "El tamaño excede los 45 caracteres permitidos."]);
                $pdo->rollBack(); exit;
            }

            // Insertamos en el catálogo oficial
            $stmtCat = $pdo->prepare("
                INSERT INTO cat_insumos (
                    nombre, material, presentacion, piezas_unidad, tamano
                ) VALUES (
                    :nombre, :material, :presentacion, :piezas_unidad, :tamano
                )
            ");
            $stmtCat->execute([
                'nombre'        => $nombre,
                'material'      => empty($material) ? null : $material,
                'presentacion'  => empty($presentacion) ? null : $presentacion,
                'piezas_unidad' => $piezas_unidad === false ? null : $piezas_unidad,
                'tamano'        => empty($tamano) ? null : $tamano
            ]);

            $idCatalogoFinal = $pdo->lastInsertId();

        } else {
            // FLUJO B: EL INSUMO YA EXISTE EN EL CATÁLOGO
            $idCatalogoFinal = filter_var($idCatalogoInsumo, FILTER_VALIDATE_INT);
            if (!$idCatalogoFinal) {
                echo json_encode(["estatus" => "error", "mensaje" => "ID de catálogo inválido."]);
                $pdo->rollBack();
                exit;
            }
        }

        // --- PASO FINAL: INGRESAR LAS CAJAS A LA BODEGA ---
        $stmtInv = $pdo->prepare("
            INSERT INTO inventario_insumos (
                idCatalogoInsumo, idUnidad, idProveedor, cantidad, lote, fecha_caducidad, marca
            ) VALUES (
                :idCat, :idUnidad, :idProveedor, :cantidad, :lote, :fecha, :marca
            )
        ");
        
        $stmtInv->execute([
            'idCat'       => $idCatalogoFinal,
            'idUnidad'    => $idUnidad_Usuario,
            'idProveedor' => $idProveedor,
            'cantidad'    => $cantidad,
            'lote'        => $lote,
            'fecha'       => $fecha_caducidad,
            'marca'       => $marca
        ]);
        
        $pdo->commit();
        echo json_encode(["estatus" => "exito", "mensaje" => "Insumos ingresados correctamente al inventario de farmacia."]);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        echo json_encode(["estatus" => "error", "mensaje" => "Error de base de datos: " . $e->getMessage()]);
    }
    exit;
}
?>