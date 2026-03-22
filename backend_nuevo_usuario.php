<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php';
require 'autorizacion.php';

//RBAC
requerir_roles_api(['Administrador']);

require 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $input = json_decode(file_get_contents('php://input'), true);

    //Validar tipos de datos y sanitizar
    $idPersonal     = filter_var($input['idPersonal'] ?? '', FILTER_VALIDATE_INT);
    $email          = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $rol            = strip_tags(trim($input['rol'] ?? ''));
    
    // Forzar formato de nombre de usuario a letras, números y guiones bajos
    $nombre_usuario = preg_replace('/[^a-zA-Z0-9_]/', '', $input['nombre_usuario'] ?? '');
    $contrasena     = $input['contrasena'] ?? '';

    // Validaciones de campos nulos o formatos inválidos
    if (!$idPersonal || empty($rol) || empty($nombre_usuario) || !$email || empty($contrasena)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios o el formato de correo es inválido."]);
        exit;
    }

    //Validar longitudes de datos
    if (strlen($contrasena) < 8) {
        echo json_encode(["estatus" => "error", "mensaje" => "La contraseña debe tener al menos 8 caracteres."]);
        exit;
    }
    
    if (strlen($nombre_usuario) > 50) {
        echo json_encode(["estatus" => "error", "mensaje" => "El nombre de usuario excede los 50 caracteres permitidos."]);
        exit;
    }

    if (strlen($email) > 100) {
        echo json_encode(["estatus" => "error", "mensaje" => "El correo electrónico excede los 100 caracteres permitidos."]);
        exit;
    }

    $roles_permitidos = ['Administrador', 'Médico', 'Enfermería', 'Administrativo'];
    if (!in_array($rol, $roles_permitidos)) {
        echo json_encode(["estatus" => "error", "mensaje" => "El rol seleccionado no es válido en el sistema."]);
        exit;
    }

    try {
        //Limite de 1 usuario con el rol de administrador
        if ($rol === 'Administrador') {
            $stmtAdmin = $pdo->query("SELECT COUNT(*) FROM usuarios_sistema WHERE rol = 'Administrador'");
            $adminCount = $stmtAdmin->fetchColumn();
            
            if ($adminCount >= 1) {
                echo json_encode(["estatus" => "error", "mensaje" => "Límite alcanzado: Ya existe una cuenta de Administrador en el sistema. Seleccione otro rol."]);
                exit;
            }
        }

        //Validar que no existan registros duplicados
        $stmtCheck = $pdo->prepare("SELECT email, nombre_usuario, idPersonal FROM usuarios_sistema WHERE email = :email OR nombre_usuario = :usuario OR idPersonal = :idPer");
        $stmtCheck->execute([
            'email'   => $email,
            'usuario' => $nombre_usuario,
            'idPer'   => $idPersonal
        ]);
        
        if ($row = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
            if ($row['email'] === $email) {
                echo json_encode(["estatus" => "error", "mensaje" => "Este correo electrónico ya está registrado."]);
            } elseif ($row['nombre_usuario'] === $nombre_usuario) {
                echo json_encode(["estatus" => "error", "mensaje" => "El nombre de usuario ya está en uso. Elige otro."]);
            } else {
                echo json_encode(["estatus" => "error", "mensaje" => "Este empleado ya tiene una cuenta de usuario asignada."]);
            }
            exit;
        }

        //Generar hash para contraseña con Bcrypt
        $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
        // Imagen de perfil por defecto
        $ruta_foto_db = "Assets/img_placeholder.png";

        $stmt = $pdo->prepare("
            INSERT INTO usuarios_sistema (
                idPersonal, nombre_usuario, email, contrasena, rol, estatus, fecha_creacion, foto_perfil
            ) VALUES (
                :idPersonal, :usuario, :email, :pass, :rol, 1, NOW(), :foto
            )
        ");

        $stmt->execute([
            'idPersonal' => $idPersonal,
            'usuario'    => $nombre_usuario,
            'email'      => $email,
            'pass'       => $hash_contrasena,
            'rol'        => $rol,
            'foto'       => $ruta_foto_db
        ]);

        echo json_encode(["estatus" => "exito", "mensaje" => "Usuario creado y vinculado al personal exitosamente."]);

    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error interno al intentar guardar el usuario en la base de datos."]);
    }
} else {
    echo json_encode(["estatus" => "error", "mensaje" => "Método no permitido."]);
}
?>