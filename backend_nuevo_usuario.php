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

    // --- ESCUDO 1 Y 2: TIPOS DE DATOS Y SANITIZACIÓN ---
    $idPersonal     = filter_var($input['idPersonal'] ?? '', FILTER_VALIDATE_INT);
    $email          = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    
    $rol            = strip_tags(trim($input['rol'] ?? ''));
    $nombre_usuario = strip_tags(trim($input['nombre_usuario'] ?? ''));
    $contrasena     = $input['contrasena'] ?? '';

    // Validaciones de campos nulos o formatos inválidos
    if (!$idPersonal || empty($rol) || empty($nombre_usuario) || !$email || empty($contrasena)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Faltan datos obligatorios o el formato de correo es inválido."]);
        exit;
    }

    // --- ESCUDO 3: LONGITUDES DE DATOS (Protección de Base de Datos) ---
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

    if (strlen($rol) > 50) {
        echo json_encode(["estatus" => "error", "mensaje" => "El rol excede la longitud permitida."]);
        exit;
    }

    try {
        // --- ESCUDO 4: REGLAS DE NEGOCIO (Evitar Duplicados) ---
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

        // Encriptación y guardado
        $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
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
        echo json_encode(["estatus" => "error", "mensaje" => "Error al guardar en base de datos: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["estatus" => "error", "mensaje" => "Método no permitido."]);
}
?>