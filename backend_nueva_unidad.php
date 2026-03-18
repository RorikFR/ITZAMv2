<?php
session_start();
header('Content-Type: application/json');
require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // --- ESCUDO 1: VERIFICACIÓN DE CAMPOS VACÍOS ---
    $campos_obligatorios = ['nombre_unidad', 'idAfiliacion', 'idCategoria', 'prioritaria', 'calle', 'idUbicacion', 'telefono'];
    foreach ($campos_obligatorios as $campo) {
        if (!isset($input[$campo]) || trim($input[$campo]) === '') {
            echo json_encode(["estatus" => "error", "mensaje" => "El campo '$campo' es obligatorio."]);
            exit;
        }
    }

    // --- ESCUDO 2: SANITIZACIÓN BÁSICA ---
    // Limpiamos etiquetas HTML para prevenir XSS (Cross-Site Scripting)
    $nombre_unidad = strip_tags(trim($input['nombre_unidad']));
    $calle         = strip_tags(trim($input['calle']));
    $telefono      = strip_tags(trim($input['telefono']));
    $email         = isset($input['email']) ? filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL) : '';
    
    $idAfiliacion  = filter_var($input['idAfiliacion'], FILTER_VALIDATE_INT);
    $idCategoria   = filter_var($input['idCategoria'], FILTER_VALIDATE_INT);
    $idUbicacion   = filter_var($input['idUbicacion'], FILTER_VALIDATE_INT);
    $prioritaria   = filter_var($input['prioritaria'], FILTER_VALIDATE_INT);

    // --- ESCUDO 3: VALIDACIÓN DE TIPOS Y FORMATOS ---
    if ($idAfiliacion === false || $idCategoria === false || $idUbicacion === false) {
        echo json_encode(["estatus" => "error", "mensaje" => "Los identificadores de catálogo deben ser numéricos."]);
        exit;
    }

    if ($prioritaria !== 0 && $prioritaria !== 1) {
        echo json_encode(["estatus" => "error", "mensaje" => "El valor de prioridad no es válido."]);
        exit;
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["estatus" => "error", "mensaje" => "El formato del correo electrónico es inválido."]);
        exit;
    }

    // Validar que el teléfono solo contenga números, espacios o guiones (ej. 55-1234-5678)
    if (!preg_match('/^[0-9\-\s]+$/', $telefono)) {
        echo json_encode(["estatus" => "error", "mensaje" => "El teléfono contiene caracteres no permitidos."]);
        exit;
    }

    // --- ESCUDO 4: VALIDACIÓN DE LONGITUD (Alineado a la BD) ---
    if (strlen($nombre_unidad) > 200) {
        echo json_encode(["estatus" => "error", "mensaje" => "El nombre de la unidad excede el límite de 200 caracteres."]);
        exit;
    }
    if (strlen($calle) > 65) {
        echo json_encode(["estatus" => "error", "mensaje" => "La calle excede el límite de 65 caracteres."]);
        exit;
    }
    if (strlen($telefono) > 20) {
        echo json_encode(["estatus" => "error", "mensaje" => "El teléfono excede el límite de 20 caracteres."]);
        exit;
    }
    if (!empty($email) && strlen($email) > 45) {
        echo json_encode(["estatus" => "error", "mensaje" => "El correo electrónico excede el límite de 45 caracteres."]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // PREVENCIÓN DE UNIDADES DUPLICADAS
        $stmtCheck = $pdo->prepare("
            SELECT idUnidad FROM registro_unidad 
            WHERE nombre = :nombre AND idUbicacion = :idUbi 
            LIMIT 1
        ");
        $stmtCheck->execute([
            'nombre' => $nombre_unidad,
            'idUbi'  => $idUbicacion
        ]);
        
        if ($stmtCheck->fetch()) {
            $pdo->rollBack();
            echo json_encode(["estatus" => "error", "mensaje" => "Esta unidad médica ya se encuentra registrada en esta ubicación."]);
            exit;
        }

        // INSERCIÓN DE LA UNIDAD MÉDICA
        $stmtUnidad = $pdo->prepare("
            INSERT INTO registro_unidad (
                nombre, idAfiliacion, idCategoria, es_prioritaria, 
                calle, idUbicacion, telefono, email
            ) VALUES (
                :nombre, :idAfiliacion, :idCategoria, :es_prioritaria, 
                :calle, :idUbicacion, :telefono, :email
            )
        ");
        
        $stmtUnidad->execute([
            'nombre'         => $nombre_unidad,
            'idAfiliacion'   => $idAfiliacion,
            'idCategoria'    => $idCategoria,
            'es_prioritaria' => $prioritaria, 
            'calle'          => $calle,
            'idUbicacion'    => $idUbicacion,         
            'telefono'       => $telefono,
            'email'          => $email
        ]);
        
        $pdo->commit();
        echo json_encode(["estatus" => "exito", "mensaje" => "Unidad médica registrada con éxito."]);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        echo json_encode(["estatus" => "error", "mensaje" => "Error de base de datos."]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        echo json_encode(["estatus" => "error", "mensaje" => "Error inesperado."]);
    }
    exit;
}
?>