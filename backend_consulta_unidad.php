<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';      

// RBAC
requerir_roles_api(['Médico', 'Administrativo', 'Enfermería']); 

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

//Obtener datos
if ($metodo === 'GET') {

    // Sanitizar filtro de busqueda
    $filtro = isset($_GET['filtro']) ? filter_var(trim($_GET['filtro']), FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    
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
                LEFT JOIN catalogo_ubicacion c ON u.idUbicacion = c.idUbicacion
                LEFT JOIN cat_afiliacion a ON u.idAfiliacion = a.idAfiliacion
                LEFT JOIN cat_categoria cat ON u.idCategoria = cat.idCategoria";

    try {
        if($filtro) {
            $sql = $sqlBase . " WHERE u.es_prioritaria = :es_prioritaria ORDER BY u.idUnidad DESC LIMIT 200";
            $stmt = $pdo->prepare($sql);
            $valorBooleano = ($filtro === 'prioritaria') ? 1 : 0;
            $stmt->execute(['es_prioritaria' => $valorBooleano]);
        } else {
            $sql = $sqlBase . " ORDER BY u.idUnidad DESC LIMIT 200";
            $stmt = $pdo->query($sql);
        }
        
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error interno al consultar el catálogo de unidades."]);
    }
    exit;
}


//Editar y eliminar registros
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idUnidadMedica = intval($input['idUnidadMedica'] ?? 0); 

    // Eliminar registros
    if ($accion === 'eliminar' && $idUnidadMedica > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM registro_unidad WHERE idUnidad = :idUnidad");
            $stmt->execute(['idUnidad' => $idUnidadMedica]);
            echo json_encode(["estatus" => "exito", "mensaje" => "Unidad médica eliminada correctamente del sistema."]);
        } catch (PDOException $e) {
            //Error de integridad referencial
            echo json_encode(["estatus" => "error", "mensaje" => "No se puede eliminar: Esta unidad clínica tiene registros o historial clínico asociados."]);
        }
        exit; 
    }

    // Editar registros
    if ($accion === 'editar' && $idUnidadMedica > 0) {
        
        // Sanitizar datos
        $telefono = preg_replace('/[^\d\-\s]/', '', $input['telefono'] ?? '');
        $correo_electronico = trim($input['correo_electronico'] ?? '');
        
        // Validar formato de correo
        if (!empty($correo_electronico) && !filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["estatus" => "error", "mensaje" => "El formato del correo electrónico ingresado es inválido."]);
            exit;
        }
        
        try {
            // Actualizar datos en tabla
            $stmt = $pdo->prepare("UPDATE registro_unidad SET telefono = :tel, email = :mail WHERE idUnidad = :id");
            $stmt->execute([
                'tel'  => $telefono, 
                'mail' => $correo_electronico, 
                'id'   => $idUnidadMedica
            ]);
            
            // Obtener registro actualizado
            $sqlObtener = "SELECT 
                                u.idUnidad AS idUnidadMedica, 
                                u.nombre AS nombre_unidad, 
                                a.nombre_afiliacion AS afiliacion, 
                                cat.nombre_categoria AS categoria, 
                                IF(u.es_prioritaria = 1, 'Sí', 'No') AS es_prioritaria, 
                                c.ciudad, 
                                u.telefono, 
                                u.email AS correo_electronico
                           FROM registro_unidad u
                           LEFT JOIN catalogo_ubicacion c ON u.idUbicacion = c.idUbicacion
                           LEFT JOIN cat_afiliacion a ON u.idAfiliacion = a.idAfiliacion
                           LEFT JOIN cat_categoria cat ON u.idCategoria = cat.idCategoria
                           WHERE u.idUnidad = :idUnidad";
                           
            $stmtObtener = $pdo->prepare($sqlObtener);
            $stmtObtener->execute(['idUnidad' => $idUnidadMedica]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Datos de contacto de la clínica actualizados correctamente.",
                "datos"   => $stmtObtener->fetch(PDO::FETCH_ASSOC)
            ]);
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Ocurrió un error interno al intentar actualizar los datos."]);
        }
        exit;
    }
}
?>