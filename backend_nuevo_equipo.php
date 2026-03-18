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

    $idCatalogoEquipo = $input['idCatalogoEquipo'] ?? '';

    // --- ESCUDO 1: SANITIZACIÓN Y TIPOS DE LA COMPRA (Bodega) ---
    $idProveedor  = filter_var($input['idProveedor'] ?? '', FILTER_VALIDATE_INT);
    $cantidad     = filter_var($input['cantidad'] ?? '', FILTER_VALIDATE_INT);
    $fecha_compra = trim($input['fecha_compra'] ?? '');

    if (!$idProveedor || !$cantidad || $cantidad <= 0) {
        echo json_encode(["estatus" => "error", "mensaje" => "El proveedor y una cantidad mayor a 0 son obligatorios."]);
        exit;
    }

    if (empty($fecha_compra)) {
        echo json_encode(["estatus" => "error", "mensaje" => "La fecha de compra es obligatoria."]);
        exit;
    }

    // --- ESCUDO 2: VALIDACIÓN LÓGICA DE FECHAS ---
    $hoy = date('Y-m-d');
    if ($fecha_compra > $hoy) {
        echo json_encode(["estatus" => "error", "mensaje" => "La fecha de compra no puede ser una fecha en el futuro."]);
        exit;
    }

    try {
        // Iniciamos el escudo de Todo o Nada
        $pdo->beginTransaction();

        // FLUJO A: REGISTRAR UN EQUIPO TOTALMENTE NUEVO EN EL CATÁLOGO
        if ($idCatalogoEquipo === 'nuevo') {
            
            // Sanitizamos los datos del diccionario
            $nombre     = strip_tags(trim($input['nombre'] ?? ''));
            $marca      = strip_tags(trim($input['marca'] ?? ''));
            $modelo     = strip_tags(trim($input['modelo'] ?? ''));
            $fabricante = strip_tags(trim($input['fabricante'] ?? ''));

            if (empty($nombre)) {
                echo json_encode(["estatus" => "error", "mensaje" => "El nombre del equipo es obligatorio para el catálogo."]);
                $pdo->rollBack(); exit;
            }

            // --- ESCUDO 3: LONGITUDES DEL CATÁLOGO (VARCHAR límites) ---
            if (strlen($nombre) > 120 || strlen($fabricante) > 120) {
                echo json_encode(["estatus" => "error", "mensaje" => "El nombre o fabricante exceden los 120 caracteres permitidos."]);
                $pdo->rollBack(); exit;
            }
            if (strlen($marca) > 65 || strlen($modelo) > 65) {
                echo json_encode(["estatus" => "error", "mensaje" => "La marca o el modelo exceden los 65 caracteres."]);
                $pdo->rollBack(); exit;
            }

            // Insertamos en el catálogo oficial
            $stmtCat = $pdo->prepare("
                INSERT INTO cat_equipo (nombre, marca, modelo, fabricante) 
                VALUES (:nombre, :marca, :modelo, :fabricante)
            ");
            
            $stmtCat->execute([
                'nombre'     => $nombre,
                'marca'      => empty($marca) ? null : $marca,
                'modelo'     => empty($modelo) ? null : $modelo,
                'fabricante' => empty($fabricante) ? null : $fabricante
            ]);

            // Recuperamos el ID recién creado
            $idCatalogoFinal = $pdo->lastInsertId();

        } else {
            // FLUJO B: EL EQUIPO YA EXISTE EN EL CATÁLOGO
            $idCatalogoFinal = filter_var($idCatalogoEquipo, FILTER_VALIDATE_INT);
            if (!$idCatalogoFinal) {
                echo json_encode(["estatus" => "error", "mensaje" => "ID de catálogo inválido."]);
                $pdo->rollBack(); exit;
            }
        }

        // --- PASO FINAL: ASIGNAR EL EQUIPO A LA CLÍNICA (Inventario) ---
        $stmtInv = $pdo->prepare("
            INSERT INTO inventario_equipo (
                idCatalogoEquipo, idUnidad, idProveedor, fecha_compra, cantidad
            ) VALUES (
                :idCat, :idUnidad, :idProveedor, :fecha_compra, :cantidad
            )
        ");
        
        $stmtInv->execute([
            'idCat'        => $idCatalogoFinal,
            'idUnidad'     => $idUnidad_Usuario,
            'idProveedor'  => $idProveedor,
            'fecha_compra' => $fecha_compra,
            'cantidad'     => $cantidad
        ]);
        
        // Confirmamos y guardamos todo
        $pdo->commit();
        echo json_encode(["estatus" => "exito", "mensaje" => "Equipo médico registrado y asignado exitosamente al inventario."]);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        echo json_encode(["estatus" => "error", "mensaje" => "Error de base de datos: " . $e->getMessage()]);
    }
    exit;
}
?>