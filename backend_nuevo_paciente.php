<?php
session_start();
header('Content-Type: application/json');
require 'db_conn.php';

// DEV ONLY - quitar en producción
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // --- ESCUDO 1 Y 2: TIPOS DE DATOS Y SANITIZACIÓN ---
    $curp         = trim(strtoupper($input['curp'] ?? ''));
    $nombre       = strip_tags(trim($input['nombre'] ?? ''));
    $apellido_p   = strip_tags(trim($input['apellido_paterno'] ?? ''));
    $apellido_m   = strip_tags(trim($input['apellido_materno'] ?? ''));
    $fecha_nac    = trim($input['fecha_nacimiento'] ?? '');
    $genero       = strip_tags(trim($input['genero'] ?? ''));
    
    $calle        = strip_tags(trim($input['calle'] ?? ''));
    $numero       = strip_tags(trim($input['numero'] ?? ''));
    $telefono     = preg_replace('/\D/', '', $input['telefono'] ?? ''); // Solo números
    $email        = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL); // Puede estar vacío
    
    // Lógica de Ubicación Dual
    $codigo_postal = preg_replace('/\D/', '', $input['codigo_postal'] ?? '');
    $idUbicacion   = filter_var($input['idUbicacion'] ?? '', FILTER_VALIDATE_INT);
    $nueva_colonia = strip_tags(trim($input['nueva_colonia'] ?? ''));
    $ciudad        = strip_tags(trim($input['ciudad'] ?? ''));
    $estado        = strip_tags(trim($input['estado'] ?? ''));

    // Variables socioculturales
    $nacionalidad = strip_tags(trim($input['nacionalidad'] ?? ''));
    $es_indigena  = filter_var($input['indigena'] ?? '', FILTER_VALIDATE_INT);
    $es_afrodesc  = filter_var($input['afrodesc'] ?? '', FILTER_VALIDATE_INT);

    // Validación Básica Obligatoria
    if (empty($curp) || empty($nombre) || empty($apellido_p) || empty($fecha_nac)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios (CURP, Nombre, Apellido Paterno, Fecha Nac)."]);
        exit;
    }

    // --- ESCUDO 3: LONGITUDES Y PATRONES ---
    if (!preg_match('/^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/', $curp)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Formato de CURP inválido."]); exit;
    }
    if (strlen($telefono) !== 10) {
        echo json_encode(["estatus" => "error", "mensaje" => "El teléfono debe ser exactamente de 10 dígitos."]); exit;
    }
    if (strlen($codigo_postal) !== 5) {
        echo json_encode(["estatus" => "error", "mensaje" => "El Código Postal debe ser de 5 dígitos."]); exit;
    }
    
    // Longitudes de base de datos
    if (strlen($nombre) > 100 || strlen($apellido_p) > 100 || strlen($apellido_m) > 100) {
        echo json_encode(["estatus" => "error", "mensaje" => "El nombre o apellidos exceden los 100 caracteres permitidos."]); exit;
    }

    try {
        $pdo->beginTransaction();

        // --- ESCUDO 4: REGLAS DE NEGOCIO (Antiduplicados) ---
        $stmtCheck = $pdo->prepare("SELECT idPaciente FROM registro_paciente WHERE curp = :curp LIMIT 1");
        $stmtCheck->execute(['curp' => $curp]);
        if ($stmtCheck->fetch()) {
            $pdo->rollBack();
            echo json_encode(["estatus" => "error", "mensaje" => "Ya existe un expediente clínico registrado con el CURP: $curp."]);
            exit;
        }

        // --- LÓGICA DE UBICACIÓN (El corazón de la solución) ---
        if ($idUbicacion) {
            // Caso A: El CP existía y el select mandó el ID correcto
            $idFinalUbicacion = $idUbicacion;
        } else {
            // Caso B: El CP NO existía. Validamos que hayan mandado los textos nuevos
            if (empty($nueva_colonia) || empty($ciudad) || empty($estado)) {
                $pdo->rollBack();
                echo json_encode(["estatus" => "error", "mensaje" => "Debe especificar la Colonia, Ciudad y Estado para este nuevo Código Postal."]);
                exit;
            }

            // Insertamos la nueva colonia al catálogo
            $stmtNuevaUbi = $pdo->prepare("
                INSERT INTO catalogo_ubicacion (codigo_postal, colonia, ciudad, estado) 
                VALUES (:cp, :colonia, :ciudad, :estado)
            ");
            $stmtNuevaUbi->execute([
                'cp'      => $codigo_postal,
                'colonia' => $nueva_colonia,
                'ciudad'  => $ciudad,
                'estado'  => $estado
            ]);
            
            // Atrapamos el ID que se acaba de crear
            $idFinalUbicacion = $pdo->lastInsertId();
        }

        // --- INSERCIÓN FINAL DEL PACIENTE ---
        // Manejamos los nulos si los campos venían vacíos
        if (empty($apellido_m)) $apellido_m = null;
        if (empty($email) || $email === false) $email = null;

        $stmtPaciente = $pdo->prepare("
            INSERT INTO registro_paciente (
                curp, nombre, apellido_p, apellido_m, fecha_nac, genero, 
                calle, numero, idUbicacion, telefono, email, nacionalidad, es_indigena, es_afrodesc
            ) VALUES (
                :curp, :nombre, :apellido_p, :apellido_m, :fecha_nac, :genero,
                :calle, :numero, :idUbicacion, :telefono, :email, :nacionalidad, :es_indigena, :es_afrodesc
            )
        ");
        
        $stmtPaciente->execute([
            'curp'         => $curp,
            'nombre'       => $nombre,
            'apellido_p'   => $apellido_p,
            'apellido_m'   => $apellido_m,
            'fecha_nac'    => $fecha_nac,
            'genero'       => $genero,
            'calle'        => $calle,
            'numero'       => $numero,
            'idUbicacion'  => $idFinalUbicacion,
            'telefono'     => $telefono,
            'email'        => $email,
            'nacionalidad' => $nacionalidad,
            'es_indigena'  => ($es_indigena === false) ? 0 : $es_indigena,
            'es_afrodesc'  => ($es_afrodesc === false) ? 0 : $es_afrodesc
        ]);
        
        $pdo->commit();
        echo json_encode(["estatus" => "exito", "mensaje" => "Paciente registrado correctamente."]);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        echo json_encode(["estatus" => "error", "mensaje" => "Error SQL: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["estatus" => "error", "mensaje" => "Método no permitido."]);
}
exit;
?>