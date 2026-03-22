<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';      

//RBAC
requerir_roles_api(['Médico', 'Enfermería', 'Administrativo']); 

require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    //Validaciones y sanitización de datos
    $curp         = trim(strtoupper($input['curp'] ?? ''));
    $nombre       = preg_replace('/\s+/', ' ', strip_tags(trim($input['nombre'] ?? '')));
    $apellido_p   = preg_replace('/\s+/', ' ', strip_tags(trim($input['apellido_paterno'] ?? '')));
    $apellido_m   = preg_replace('/\s+/', ' ', strip_tags(trim($input['apellido_materno'] ?? '')));
    $fecha_nac    = trim($input['fecha_nacimiento'] ?? '');
    $genero       = strip_tags(trim($input['genero'] ?? ''));
    
    $calle        = preg_replace('/\s+/', ' ', strip_tags(trim($input['calle'] ?? '')));
    $numero       = strip_tags(trim($input['numero'] ?? ''));
    $telefono     = preg_replace('/\D/', '', $input['telefono'] ?? ''); // Solo números
    $email        = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL); 
    
    $codigo_postal = preg_replace('/\D/', '', $input['codigo_postal'] ?? '');
    $idUbicacion   = filter_var($input['idUbicacion'] ?? '', FILTER_VALIDATE_INT);
    $nueva_colonia = preg_replace('/\s+/', ' ', strip_tags(trim($input['nueva_colonia'] ?? '')));
    $ciudad        = preg_replace('/\s+/', ' ', strip_tags(trim($input['ciudad'] ?? '')));
    $estado        = preg_replace('/\s+/', ' ', strip_tags(trim($input['estado'] ?? '')));

    $nacionalidad = strip_tags(trim($input['nacionalidad'] ?? ''));
    $es_indigena  = filter_var($input['indigena'] ?? '', FILTER_VALIDATE_INT);
    $es_afrodesc  = filter_var($input['afrodesc'] ?? '', FILTER_VALIDATE_INT);

    // Validaciones regex
    if (empty($curp) || empty($nombre) || empty($apellido_p) || empty($fecha_nac)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios (CURP, Nombre, Apellido Paterno, Fecha de Nacimiento)."]);
        exit;
    }
    if (!preg_match('/^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/', $curp)) {
        echo json_encode(["estatus" => "error", "mensaje" => "El formato del CURP es inválido."]); exit;
    }
    if (strlen($telefono) !== 10) {
        echo json_encode(["estatus" => "error", "mensaje" => "El teléfono debe ser exactamente de 10 dígitos."]); exit;
    }
    if (strlen($codigo_postal) !== 5) {
        echo json_encode(["estatus" => "error", "mensaje" => "El Código Postal debe ser de 5 dígitos."]); exit;
    }
    if (strlen($nombre) > 100 || strlen($apellido_p) > 100 || strlen($apellido_m) > 100) {
        echo json_encode(["estatus" => "error", "mensaje" => "El nombre o apellidos exceden los 100 caracteres permitidos."]); exit;
    }

    //Validar si el paciente ya existe
    try {
        $stmtCheck = $pdo->prepare("SELECT idPaciente FROM registro_paciente WHERE curp = :curp LIMIT 1");
        $stmtCheck->execute(['curp' => $curp]);
        if ($stmtCheck->fetch()) {
            echo json_encode(["estatus" => "error", "mensaje" => "Ya existe un expediente clínico registrado con el CURP: $curp."]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error interno al verificar duplicados."]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        if ($idUbicacion) {
            //Si CP ya está en catalogo
            $idFinalUbicacion = $idUbicacion;
        } else {
            // Si CP no está registrado en catálogo
            if (empty($nueva_colonia) || empty($ciudad) || empty($estado)) {
                $pdo->rollBack();
                echo json_encode(["estatus" => "error", "mensaje" => "Debe especificar la Colonia, Ciudad y Estado para este nuevo Código Postal."]);
                exit;
            }

            // Guardar datos en catalogo
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
            
            $idFinalUbicacion = $pdo->lastInsertId();
        }

        //Guardar paciente
        $apellido_m = empty($apellido_m) ? null : $apellido_m;
        $email      = (empty($email) || $email === false) ? null : $email;

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
        echo json_encode(["estatus" => "exito", "mensaje" => "Paciente registrado correctamente en el sistema."]);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) { 
            $pdo->rollBack(); 
        }
        echo json_encode(["estatus" => "error", "mensaje" => "Ocurrió un error interno al registrar el paciente. Verifique los datos."]);
    }
} else {
    echo json_encode(["estatus" => "error", "mensaje" => "Método no permitido."]);
}
exit;
?>