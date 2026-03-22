<?php

require 'seguridad_backend.php';
require 'autorizacion.php';

//RBAC
requerir_roles_api(['Administrativo', 'Administrador']);

require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Trazabilidad unidades médicas
    $rolUsuario = $_SESSION['rol'] ?? '';
    $esAdminGlobal = in_array($rolUsuario, ['Administrador']);

    if ($esAdminGlobal) {
        // Si usuario es admin, obtener el idUnidad del selector
        $idUnidad_Usuario = filter_var($input['idUnidadDestino'] ?? '', FILTER_VALIDATE_INT);
        if (!$idUnidad_Usuario) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error: Selecciona la unidad médica de destino para los insumos."]);
            exit;
        }
    } else {
        // Si usuario no es admin, tomar idUnidad de variable de sesión
        $idUnidad_Usuario = $_SESSION['idUnidad'] ?? null; 
        if (!$idUnidad_Usuario) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error: No tiene una Unidad Médica asignada en su sesión para ingresar inventario físico."]);
            exit;
        }
    }

    $idCatalogoInsumo = trim($input['idCatalogoInsumo'] ?? '');

    // Sanitización de datos
    $idProveedor = filter_var($input['idProveedor'] ?? '', FILTER_VALIDATE_INT);
    $cantidad    = filter_var($input['cantidad'] ?? '', FILTER_VALIDATE_INT);

    $marca           = htmlspecialchars(trim($input['marca'] ?? ''), ENT_QUOTES, 'UTF-8');
    $lote            = htmlspecialchars(trim($input['lote'] ?? ''), ENT_QUOTES, 'UTF-8');
    $fecha_caducidad = trim($input['fecha_caducidad'] ?? '');

    if (!$idProveedor || !$cantidad || $cantidad <= 0) {
        echo json_encode(["estatus" => "error", "mensaje" => "El proveedor y una cantidad mayor a 0 son obligatorios."]);
        exit;
    }

    // Validación de longitud de datos
    if (mb_strlen($marca) > 45 || mb_strlen($lote) > 45) {
        echo json_encode(["estatus" => "error", "mensaje" => "La marca o el lote exceden el límite de 45 caracteres."]);
        exit;
    }

    // Validación de fechas
    if (!empty($fecha_caducidad)) {
        // Validación de formato de fecha 
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_caducidad)) {
            echo json_encode(["estatus" => "error", "mensaje" => "Formato de fecha de caducidad inválido."]);
            exit;
        }
        
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

        // Registro manual de insumo
        if ($idCatalogoInsumo === 'nuevo') {
            
            // Sanitizamos los datos del catalogo
            $nombre        = htmlspecialchars(trim($input['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
            $material      = htmlspecialchars(trim($input['material'] ?? ''), ENT_QUOTES, 'UTF-8');
            $presentacion  = htmlspecialchars(trim($input['presentacion'] ?? ''), ENT_QUOTES, 'UTF-8');
            $tamano        = htmlspecialchars(trim($input['tamano'] ?? ''), ENT_QUOTES, 'UTF-8');
            $piezas_unidad = filter_var($input['piezas_unidad'] ?? null, FILTER_VALIDATE_INT);

            if (empty($nombre)) {
                echo json_encode(["estatus" => "error", "mensaje" => "El nombre del insumo es obligatorio para el catálogo."]);
                $pdo->rollBack();
                exit;
            }

            //Validar longitudes de datos
            if (mb_strlen($nombre) > 120) {
                echo json_encode(["estatus" => "error", "mensaje" => "El nombre excede los 120 caracteres permitidos."]);
                $pdo->rollBack(); exit;
            }
            if (mb_strlen($material) > 65 || mb_strlen($presentacion) > 65) {
                echo json_encode(["estatus" => "error", "mensaje" => "El material o presentación exceden los 65 caracteres."]);
                $pdo->rollBack(); exit;
            }
            if (mb_strlen($tamano) > 45) {
                echo json_encode(["estatus" => "error", "mensaje" => "El tamaño excede los 45 caracteres permitidos."]);
                $pdo->rollBack(); exit;
            }

            // Insertar datos
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
            // Si el insumo ya existe en el catalogo
            $idCatalogoFinal = filter_var($idCatalogoInsumo, FILTER_VALIDATE_INT);
            if (!$idCatalogoFinal) {
                echo json_encode(["estatus" => "error", "mensaje" => "ID de catálogo de insumos inválido."]);
                $pdo->rollBack();
                exit;
            }
        }

        // Guardar en inventario
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
        echo json_encode(["estatus" => "exito", "mensaje" => "Insumos ingresados correctamente al inventario de la farmacia."]);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        echo json_encode(["estatus" => "error", "mensaje" => "Error interno al registrar el insumo en la base de datos."]);
    }
    exit;
}
?>