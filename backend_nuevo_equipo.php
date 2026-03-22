<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'seguridad_backend.php';
require 'autorizacion.php';

// RBAC
requerir_roles_api(['Administrativo', 'Administrador']);

require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Trazabilidad de usuario y unidad médica
    $rolUsuario = $_SESSION['rol'] ?? '';
    $esAdminGlobal = in_array($rolUsuario, ['Administrador']);

    if ($esAdminGlobal) {
        // Si usuario es admin, debe seleccionar unidad médica
        $idUnidad_Usuario = filter_var($input['idUnidadDestino'] ?? '', FILTER_VALIDATE_INT);
        if (!$idUnidad_Usuario) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error: Como Administrador, debe seleccionar la Unidad Médica de destino para el equipo."]);
            exit;
        }
    } else {
        // Si usuario no es admin, se carga datos de unidad médica con variable de sesión.
        $idUnidad_Usuario = $_SESSION['idUnidad'] ?? null; 
        if (!$idUnidad_Usuario) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error: No tiene una Unidad Médica asignada en su sesión para ingresar equipo."]);
            exit;
        }
    }

    $idCatalogoEquipo = trim($input['idCatalogoEquipo'] ?? '');

    // Sanitización de entradas
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

    // Validacion de formato de fecha
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_compra)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Formato de fecha de compra inválido."]);
        exit;
    }

    //Validacion de fechas
    $hoy = date('Y-m-d');
    if ($fecha_compra > $hoy) {
        echo json_encode(["estatus" => "error", "mensaje" => "La fecha de compra no puede ser una fecha en el futuro."]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Registro manual de equipo médico
        if ($idCatalogoEquipo === 'nuevo') {
            
            // Sanitización de datos
            $nombre     = htmlspecialchars(trim($input['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
            $marca      = htmlspecialchars(trim($input['marca'] ?? ''), ENT_QUOTES, 'UTF-8');
            $modelo     = htmlspecialchars(trim($input['modelo'] ?? ''), ENT_QUOTES, 'UTF-8');
            $fabricante = htmlspecialchars(trim($input['fabricante'] ?? ''), ENT_QUOTES, 'UTF-8');

            if (empty($nombre)) {
                echo json_encode(["estatus" => "error", "mensaje" => "El nombre del equipo es obligatorio para el catálogo."]);
                $pdo->rollBack(); exit;
            }

            // Validar longitudes de datos
            if (mb_strlen($nombre) > 120 || mb_strlen($fabricante) > 120) {
                echo json_encode(["estatus" => "error", "mensaje" => "El nombre o fabricante exceden los 120 caracteres permitidos."]);
                $pdo->rollBack(); exit;
            }
            if (mb_strlen($marca) > 65 || mb_strlen($modelo) > 65) {
                echo json_encode(["estatus" => "error", "mensaje" => "La marca o el modelo exceden los 65 caracteres permitidos."]);
                $pdo->rollBack(); exit;
            }

            //Guardar en DB
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
            // Si el equipo ya existe en catalogo
            $idCatalogoFinal = filter_var($idCatalogoEquipo, FILTER_VALIDATE_INT);
            if (!$idCatalogoFinal) {
                echo json_encode(["estatus" => "error", "mensaje" => "ID de catálogo inválido."]);
                $pdo->rollBack(); exit;
            }
        }

        // Asignar equipo a unidad médica
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
        
        $pdo->commit();
        echo json_encode(["estatus" => "exito", "mensaje" => "Equipo médico registrado y asignado exitosamente al inventario."]);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        echo json_encode(["estatus" => "error", "mensaje" => "Error interno al registrar el equipo médico en la base de datos."]);
    }
    exit;
}
?>