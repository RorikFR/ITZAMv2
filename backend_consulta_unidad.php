<?php
header('Content-Type: application/json');

// require 'conexion.php'; 
require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// --- LEER DATOS (BÚSQUEDA / FILTRO) ---
if ($metodo === 'GET') {
    $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';
    
    // 🔥 CONSULTA MAESTRA CON JOINs
    // Unimos registro_unidad con sus 3 tablas satélite: ubicación, afiliación y categoría.
    $sqlBase = "SELECT 
                    u.idUnidad AS idUnidadMedica, 
                    u.nombre AS nombre_unidad, 
                    a.nombre_afiliacion AS afiliacion, 
                    cat.nombre_categoria AS categoria, 
                    IF(u.es_prioritaria = 1, 'Sí', 'No') AS es_prioritaria, 
                    c.ciudad, 
                    u.telefono, 
                    u.email AS correo_electronico
                FROM registro_unidad u
                INNER JOIN catalogo_ubicacion c ON u.idUbicacion = c.idUbicacion
                INNER JOIN cat_afiliacion a ON u.idAfiliacion = a.idAfiliacion
                INNER JOIN cat_categoria cat ON u.idCategoria = cat.idCategoria";

    try {
        if($filtro) {
            $sql = $sqlBase . " WHERE u.es_prioritaria = :es_prioritaria ORDER BY u.idUnidad DESC";
            $stmt = $pdo->prepare($sql);
            $valorBooleano = ($filtro === 'prioritaria') ? 1 : 0;
            $stmt->execute(['es_prioritaria' => $valorBooleano]);
        } else {
            $sql = $sqlBase . " ORDER BY u.idUnidad DESC";
            $stmt = $pdo->query($sql);
        }
        
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error al consultar: " . $e->getMessage()]);
    }
    exit;
}

// --- EDITAR O ELIMINAR ---
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $accion = $input['accion'] ?? '';
    $idUnidadMedica = $input['idUnidadMedica'] ?? 0; 

    // --- ELIMINACIÓN (Se mantiene igual, la integridad referencial hará su trabajo) ---
    if ($accion === 'eliminar' && $idUnidadMedica > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM registro_unidad WHERE idUnidad = :idUnidad");
            $stmt->execute(['idUnidad' => $idUnidadMedica]);
            echo json_encode(["estatus" => "exito", "mensaje" => "Unidad médica eliminada correctamente."]);
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "No se puede eliminar: Esta unidad tiene registros asociados (Personal/Pacientes)."]);
        }
        exit; 
    }

    // --- EDICIÓN (Actualizamos contacto y refrescamos con JOINs) ---
    if ($accion === 'editar' && $idUnidadMedica > 0) {
        $telefono = $input['telefono'] ?? '';
        $correo_electronico = $input['correo_electronico'] ?? '';
        
        try {
            // 1. Actualizar contacto
            $stmt = $pdo->prepare("UPDATE registro_unidad SET telefono = :tel, email = :mail WHERE idUnidad = :id");
            $stmt->execute(['tel' => $telefono, 'mail' => $correo_electronico, 'id' => $idUnidadMedica]);
            
            // 2. Recuperar registro actualizado CON JOINs para no romper la vista del frontend
            $sqlObtener = "SELECT 
                                u.idUnidad AS idUnidadMedica, u.nombre AS nombre_unidad, 
                                a.nombre_afiliacion AS afiliacion, cat.nombre_categoria AS categoria, 
                                IF(u.es_prioritaria = 1, 'Sí', 'No') AS es_prioritaria, 
                                c.ciudad, u.telefono, u.email AS correo_electronico
                           FROM registro_unidad u
                           INNER JOIN catalogo_ubicacion c ON u.idUbicacion = c.idUbicacion
                           INNER JOIN cat_afiliacion a ON u.idAfiliacion = a.idAfiliacion
                           INNER JOIN cat_categoria cat ON u.idCategoria = cat.idCategoria
                           WHERE u.idUnidad = :idUnidad";
                           
            $stmtObtener = $pdo->prepare($sqlObtener);
            $stmtObtener->execute(['idUnidad' => $idUnidadMedica]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Contacto actualizado.",
                "datos"   => $stmtObtener->fetch(PDO::FETCH_ASSOC)
            ]);
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error al actualizar."]);
        }
        exit;
    }
}
?>