<?php

//Validaciones de seguridad e inactividad
require 'seguridad_backend.php';
require 'autorizacion.php';

//RBAC
requerir_roles_api(['Administrativo']);

require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    //Trazabilidad de unidad médica
    $rolUsuario = $_SESSION['rol'] ?? '';
    $esAdminGlobal = in_array($rolUsuario, ['Administrador']);

    if ($esAdminGlobal) {
        // Si el usuario es admin
        $idUnidad_Usuario = filter_var($input['idUnidadDestino'] ?? '', FILTER_VALIDATE_INT);
        if (!$idUnidad_Usuario) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error: Como Administrador, debe seleccionar la Unidad Médica de destino para el lote."]);
            exit;
        }
    } else {
        // Cualquier otro usuario se toma la unidad de la variable de sesión
        $idUnidad_Usuario = $_SESSION['idUnidad'] ?? null; 
        if (!$idUnidad_Usuario) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error: No tiene una Unidad Médica asignada para ingresar inventario físico."]);
            exit;
        }
    }

    $idCatalogoMed = trim($input['idCatalogoMed'] ?? '');

    //Sanitización de entradas
    $lote            = htmlspecialchars(trim($input['lote'] ?? ''), ENT_QUOTES, 'UTF-8');
    $fecha_caducidad = trim($input['fecha_caducidad'] ?? '');
    $idProveedor     = filter_var($input['idProveedor'] ?? '', FILTER_VALIDATE_INT);
    $cantidad        = filter_var($input['cantidad'] ?? '', FILTER_VALIDATE_INT);

    if (!$idProveedor || !$cantidad || $cantidad <= 0 || empty($lote) || empty($fecha_caducidad)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Datos de inventario (Proveedor, cantidad, lote o caducidad) inválidos o vacíos."]);
        exit;
    }

    if (mb_strlen($lote) > 45) {
        echo json_encode(["estatus" => "error", "mensaje" => "El lote excede el máximo de 45 caracteres."]);
        exit;
    }

    $hoy = date('Y-m-d');
    if ($fecha_caducidad <= $hoy) {
        echo json_encode(["estatus" => "error", "mensaje" => "La fecha de caducidad no puede ser hoy ni una fecha pasada."]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Registro de medicamento
        if ($idCatalogoMed === 'nuevo') {
            
            // Sanitizacion de entradas
            $nombre           = htmlspecialchars(trim($input['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
            $marca            = htmlspecialchars(trim($input['marca'] ?? ''), ENT_QUOTES, 'UTF-8');
            $presentacion     = htmlspecialchars(trim($input['presentacion'] ?? ''), ENT_QUOTES, 'UTF-8');
            $via_adm          = htmlspecialchars(trim($input['via_adm'] ?? ''), ENT_QUOTES, 'UTF-8');
            $principio_activo = htmlspecialchars(trim($input['principio_activo'] ?? ''), ENT_QUOTES, 'UTF-8');
            $concentracion    = htmlspecialchars(trim($input['concentracion'] ?? ''), ENT_QUOTES, 'UTF-8');
            
            // Forzar opciones a enteros
            $refrigeradoRaw = $input['refrigerado'] ?? null;
            if ($refrigeradoRaw === null || !in_array((string)$refrigeradoRaw, ['0', '1'], true)) {
                echo json_encode(["estatus" => "error", "mensaje" => "Debe indicar si requiere refrigeración."]);
                $pdo->rollBack();
                exit;
            }
            $refrigerado = (int)$refrigeradoRaw;

            // Validación de campos vacíos
            if (empty($nombre) || empty($marca) || empty($principio_activo)) {
                echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios para crear el medicamento."]);
                $pdo->rollBack();
                exit;
            }

            // Validación de longitudes de campo
            if (mb_strlen($nombre) > 120 || mb_strlen($marca) > 120 || mb_strlen($presentacion) > 45 || 
                mb_strlen($via_adm) > 45 || mb_strlen($principio_activo) > 120 || mb_strlen($concentracion) > 120) {
                echo json_encode(["estatus" => "error", "mensaje" => "Uno de los textos del medicamento excede la longitud permitida."]);
                $pdo->rollBack();
                exit;
            }

            // Insertamos los datos en catalogo
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

            // Capturamos el ID del catálogo que se acaba de generar
            $idCatalogoFinal = $pdo->lastInsertId();

        } else {
            // Si el medicamento ya existe en catalogo
            $idCatalogoFinal = filter_var($idCatalogoMed, FILTER_VALIDATE_INT);
            if (!$idCatalogoFinal) {
                echo json_encode(["estatus" => "error", "mensaje" => "ID de catálogo inválido."]);
                $pdo->rollBack();
                exit;
            }
        }

        // Guardar en inventario con idCatalogo y idUnidad
        $stmtInv = $pdo->prepare("
            INSERT INTO inventario_medicamentos (
                idCatalogoMed, idUnidad, idProveedor, cantidad, lote, fecha_caducidad
            ) VALUES (
                :idCat, :idUnidad, :idProveedor, :cantidad, :lote, :fecha
            )
        ");
        
        $stmtInv->execute([
            'idCat'       => $idCatalogoFinal,
            'idUnidad'    => $idUnidad_Usuario,
            'idProveedor' => $idProveedor,
            'cantidad'    => $cantidad,
            'lote'        => $lote,
            'fecha'       => $fecha_caducidad
        ]);
        
        $pdo->commit();
        echo json_encode(["estatus" => "exito", "mensaje" => "Lote ingresado correctamente al inventario de la farmacia."]);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        echo json_encode(["estatus" => "error", "mensaje" => "Error interno al procesar el registro en la base de datos."]);
    }
    exit;
}
?>