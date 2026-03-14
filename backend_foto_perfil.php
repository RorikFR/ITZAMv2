<?php
session_start();
header('Content-Type: application/json');

// DEV ONLY - quitar en prod.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- 1. ESCUDO DE SESIÓN ---
if (!isset($_SESSION['idUsuario'])) {
    http_response_code(401);
    echo json_encode(["estatus" => "error", "mensaje" => "No tienes autorización."]);
    exit;
}

require 'db_conn.php';
$idLogueado = $_SESSION['idUsuario'];

// --- 2. RECEPCIÓN Y VALIDACIÓN DEL ARCHIVO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'subir_foto') {
    
    // Verificamos si PHP recibió el archivo sin errores de red
    if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error de transferencia al recibir el archivo."]);
        exit;
    }

    $foto = $_FILES['foto_perfil'];

    // REGLA 1: Tamaño máximo (2MB) - Escudo de servidor
    $maxSizeMB = 2;
    if ($foto['size'] > ($maxSizeMB * 1024 * 1024)) {
        echo json_encode(["estatus" => "error", "mensaje" => "El archivo supera el límite de 2MB."]);
        exit;
    }

    // REGLA 2: Validación estricta del tipo de archivo (MIME Type real)
    // Esto evita que alguien suba un virus.exe y le cambie el nombre a foto.jpg
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeTypeReal = finfo_file($finfo, $foto['tmp_name']);
    finfo_close($finfo);

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!array_key_exists($mimeTypeReal, $tiposPermitidos)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Formato no válido. Sube un JPG, PNG o WEBP real."]);
        exit;
    }

    // --- 3. PREPARACIÓN DEL DIRECTORIO Y NOMBRE SEGURO ---
    $directorioDestino = __DIR__ . '/profile-imgs/';
    
    // Si la carpeta no existe, PHP la crea automáticamente con permisos seguros
    if (!is_dir($directorioDestino)) {
        mkdir($directorioDestino, 0755, true);
    }

    // Renombramos el archivo. NUNCA confiamos en el nombre original del usuario.
    // Usamos: perfil_ID_TIMESTAMP.ext (Ej. perfil_5_1710123456.jpg)
    // El timestamp evita que el navegador guarde la imagen vieja en caché
    $extensionSegura = $tiposPermitidos[$mimeTypeReal];
    $nombreNuevo = 'perfil_' . $idLogueado . '_' . time() . '.' . $extensionSegura;
    
    $rutaAbsoluta = $directorioDestino . $nombreNuevo; // Para guardar el archivo en el disco
    $rutaRelativa = 'profile-imgs/' . $nombreNuevo;    // Para guardar en la Base de Datos

    try {
        // --- 4. LIMPIEZA DE BASURA (BORRAR FOTO ANTERIOR) ---
        $stmtBusqueda = $pdo->prepare("SELECT foto_perfil FROM usuarios_sistema WHERE idUsuario = :id");
        $stmtBusqueda->execute(['id' => $idLogueado]);
        $fotoAnterior = $stmtBusqueda->fetchColumn();

        if ($fotoAnterior && file_exists(__DIR__ . '/' . $fotoAnterior)) {
            unlink(__DIR__ . '/' . $fotoAnterior); // Eliminamos el archivo físico viejo
        }

// Guardar la nueva foto
        if (move_uploaded_file($foto['tmp_name'], $rutaAbsoluta)) {
            
            $stmtUpdate = $pdo->prepare("UPDATE usuarios_sistema SET foto_perfil = :ruta WHERE idUsuario = :id");
            $stmtUpdate->execute(['ruta' => $rutaRelativa, 'id' => $idLogueado]);

            // ACTUALIZAMOS LA SESIÓN PARA QUE TODO EL SISTEMA LO SEPA
            $_SESSION['foto_perfil'] = $rutaRelativa;

            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Foto de perfil guardada exitosamente.",
                "nueva_ruta" => $rutaRelativa
            ]);

        } else {
            echo json_encode(["estatus" => "error", "mensaje" => "Error del servidor al guardar el archivo en el disco."]);
        }

    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error de base de datos al enlazar la imagen."]);
    }
    exit;
}
?>