<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';      

//RBAC
requerir_roles_api(['Administrador', 'Administrativo', 'Médico', 'Enfermería']);

require 'db_conn.php';

//Obtener ID del usuario
$idLogueado = filter_var($_SESSION['idUsuario'] ?? 0, FILTER_VALIDATE_INT);

//Cargar y leer archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'subir_foto') {
    
    // Validar si el archivo se recibio correctamente
    if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error de transferencia al recibir el archivo en el servidor."]);
        exit;
    }

    $foto = $_FILES['foto_perfil'];

    //Tamaño maximo de archivo (2MB)
    $maxSizeMB = 2;
    if ($foto['size'] > ($maxSizeMB * 1024 * 1024)) {
        echo json_encode(["estatus" => "error", "mensaje" => "El archivo supera el límite de 2MB permitidos."]);
        exit;
    }

    // Validar tipo de archivo (MIME Type)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeTypeReal = finfo_file($finfo, $foto['tmp_name']);
    finfo_close($finfo);

    //Formatos de imagenes permitidos
    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!array_key_exists($mimeTypeReal, $tiposPermitidos)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Formato no válido. Por seguridad, solo se aceptan imágenes JPG, PNG o WEBP reales."]);
        exit;
    }

    //Guardar en destino
    $directorioDestino = __DIR__ . '/profile-imgs/';
    
    // Si la carpeta no existe, se crea
    if (!is_dir($directorioDestino)) {
        mkdir($directorioDestino, 0755, true);
    }

    // Renombramos el archivo
    $extensionSegura = $tiposPermitidos[$mimeTypeReal];
    $nombreNuevo = 'perfil_' . $idLogueado . '_' . time() . '.' . $extensionSegura;
    
    $rutaAbsoluta = $directorioDestino . $nombreNuevo; 
    $rutaRelativa = 'profile-imgs/' . $nombreNuevo;    

    try {
        //Borrar foto anterior
        $stmtBusqueda = $pdo->prepare("SELECT foto_perfil FROM usuarios_sistema WHERE idUsuario = :id");
        $stmtBusqueda->execute(['id' => $idLogueado]);
        $fotoAnterior = $stmtBusqueda->fetchColumn();

        //Proteger imagen placeholder para evitar borrado
        $imagenesProtegidas = ['Assets/img_placeholder.png', 'Assets/think.jpg'];
        
        if ($fotoAnterior && !in_array($fotoAnterior, $imagenesProtegidas)) {
            $rutaFisicaAnterior = __DIR__ . '/' . $fotoAnterior;
            if (file_exists($rutaFisicaAnterior)) {
                unlink($rutaFisicaAnterior);
            }
        }

        //Guardar nueva foto
        if (move_uploaded_file($foto['tmp_name'], $rutaAbsoluta)) {
            
            $stmtUpdate = $pdo->prepare("UPDATE usuarios_sistema SET foto_perfil = :ruta WHERE idUsuario = :id");
            $stmtUpdate->execute(['ruta' => $rutaRelativa, 'id' => $idLogueado]);

            //Actualizar sesion para reflejar cambios
            $_SESSION['foto_perfil'] = $rutaRelativa;

            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Foto de perfil actualizada exitosamente.",
                "nueva_ruta" => $rutaRelativa
            ]);

        } else {
            echo json_encode(["estatus" => "error", "mensaje" => "Error del servidor al guardar el archivo en el disco."]);
        }

    } catch (PDOException $e) {
        echo json_encode(["estatus" => "error", "mensaje" => "Error interno al enlazar la imagen con su perfil."]);
    }
    exit;
} else {
    echo json_encode(["estatus" => "error", "mensaje" => "Petición inválida."]);
    exit;
}
?>