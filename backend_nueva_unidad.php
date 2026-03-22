<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php';
require 'autorizacion.php';

//RBAC
requerir_roles_api(['Administrativo']);

require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    //Validacion de datos (campos vacios)
    $campos_obligatorios = ['nombre_unidad', 'idAfiliacion', 'idCategoria', 'prioritaria', 'calle', 'idUbicacion', 'telefono'];
    foreach ($campos_obligatorios as $campo) {
        if (!isset($input[$campo]) || trim($input[$campo]) === '') {
            echo json_encode(["estatus" => "error", "mensaje" => "El campo '$campo' es obligatorio."]);
            exit;
        }
    }

    //Sanitizacion de datos ingresados
    $nombre_unidad = preg_replace('/\s+/', ' ', strip_tags(trim($input['nombre_unidad'])));
    $calle         = preg_replace('/\s+/', ' ', strip_tags(trim($input['calle'])));
    $telefono      = preg_replace('/\D/', '', $input['telefono']); 
    $email         = isset($input['email']) ? trim($input['email']) : '';
    
    $idAfiliacion  = filter_var($input['idAfiliacion'], FILTER_VALIDATE_INT);
    $idCategoria   = filter_var($input['idCategoria'], FILTER_VALIDATE_INT);
    $idUbicacion   = filter_var($input['idUbicacion'], FILTER_VALIDATE_INT);
    $prioritaria   = filter_var($input['prioritaria'], FILTER_VALIDATE_INT);

    //Validacion de tipo de datos y formatos
    if (!$idAfiliacion || !$idCategoria || !$idUbicacion) {
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

    if (strlen($telefono) !== 10) {
        echo json_encode(["estatus" => "error", "mensaje" => "El teléfono debe contener exactamente 10 dígitos."]);
        exit;
    }

    //Validacion de longitud de caracteres
    if (strlen($nombre_unidad) > 200) {
        echo json_encode(["estatus" => "error", "mensaje" => "El nombre de la unidad excede el límite de 200 caracteres."]); exit;
    }
    if (strlen($calle) > 65) {
        echo json_encode(["estatus" => "error", "mensaje" => "La calle excede el límite de 65 caracteres."]); exit;
    }
    if (!empty($email) && strlen($email) > 120) {
        echo json_encode(["estatus" => "error", "mensaje" => "El correo electrónico excede el límite permitido."]); exit;
    }

    try {
        // Validar registros duplicados
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
            echo json_encode(["estatus" => "error", "mensaje" => "Esta unidad médica ya se encuentra registrada en esta ubicación."]);
            exit;
        }

        $pdo->beginTransaction();

        // Registrar unidad en DB
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
            'email'          => empty($email) ? null : $email //Set NULL si campo email no fue llenado
        ]);
        
        $pdo->commit();
        echo json_encode(["estatus" => "exito", "mensaje" => "Unidad médica registrada con éxito en el sistema."]);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        echo json_encode(["estatus" => "error", "mensaje" => "Error interno al intentar guardar la unidad en la base de datos."]);
    }
    exit;
} else {
    echo json_encode(["estatus" => "error", "mensaje" => "Método no permitido."]);
    exit;
}
?>