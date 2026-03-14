<?php
header('Content-Type: application/json');

//DEV ONLY - quitar en prod.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

// 2. MANEJO DE SOLICITUDES
$metodo = $_SERVER['REQUEST_METHOD'];

// --- LEER DATOS (BÚSQUEDA / FILTRO) ---
if ($metodo === 'GET') {
    $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';
    
    if($filtro) {
        // Usamos IF() para transformar el 1 en 'Sí' y el 0 en 'No'
        $sql = "SELECT 
                    u.idUnidad AS idUnidadMedica, 
                    u.nombre AS nombre_unidad, 
                    u.afiliacion, 
                    u.categoria, 
                    IF(u.es_prioritaria = 1, 'Sí', 'No') AS es_prioritaria, 
                    c.ciudad, 
                    u.telefono, 
                    u.email AS correo_electronico
                FROM registro_unidad u
                INNER JOIN catalogo_ubicacion c ON u.idUbicacion = c.idUbicacion
                WHERE u.es_prioritaria = :es_prioritaria
                ORDER BY u.idUnidad DESC";
        
        $stmt = $pdo->prepare($sql);
        
        $valorBooleano = ($filtro === 'prioritaria') ? 1 : 0;
        
        $stmt->execute(['es_prioritaria' => $valorBooleano]);
        
    } else {
        // Hacemos el mismo cambio en la consulta por defecto
        $stmt = $pdo->query("SELECT 
                                u.idUnidad AS idUnidadMedica, 
                                u.nombre AS nombre_unidad, 
                                u.afiliacion, 
                                u.categoria, 
                                IF(u.es_prioritaria = 1, 'Sí', 'No') AS es_prioritaria, 
                                c.ciudad, 
                                u.telefono, 
                                u.email AS correo_electronico
                            FROM registro_unidad u
                            INNER JOIN catalogo_ubicacion c ON u.idUbicacion = c.idUbicacion
                            ORDER BY u.idUnidad DESC 
                            LIMIT 20");
    }
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// --- EDITAR O ELIMINAR ---
if ($metodo === 'POST') {
    // Recibimos los datos JSON del frontend
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';

// Asegúrate de extraer la variable correcta que envía tu frontend
    $idUnidadMedica = $input['idUnidadMedica'] ?? 0; 

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idUnidadMedica > 0) {
        
        try {
            // Eliminamos el registro directamente de la tabla maestra
            $stmt = $pdo->prepare("DELETE FROM registro_unidad WHERE idUnidad = :idUnidad");
            $stmt->execute(['idUnidad' => $idUnidadMedica]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Unidad médica eliminada exitosamente."
            ]);
            
        } catch (PDOException $e) {
            // Si el motor de BD rechaza la eliminación por integridad referencial, lo atrapamos aquí
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "No se puede eliminar esta unidad médica porque ya tiene personal, pacientes o consultas asociadas en el sistema."
            ]);
        }
        
        exit; 
    }

    // Asegúrate de extraer la variable correcta que envía tu frontend
    $idUnidadMedica = $input['idUnidadMedica'] ?? 0;

    // --- LÓGICA DE EDICIÓN ---
    if ($accion === 'editar' && $idUnidadMedica > 0) {
        
        // Recibimos EXCLUSIVAMENTE los campos de contacto a editar
        $telefono = $input['telefono'] ?? '';
        $correo_electronico = $input['correo_electronico'] ?? '';
        
        // PASO 1: Actualizar la tabla maestra (solo teléfono y correo)
        $stmt = $pdo->prepare("UPDATE registro_unidad 
            SET 
                telefono = :telefono,
                email = :correo_electronico
            WHERE idUnidad = :idUnidad");
        
        $stmt->execute([
            'telefono'           => $telefono,
            'correo_electronico' => $correo_electronico,
            'idUnidad'           => $idUnidadMedica
        ]);
        
        // PASO 2: Obtener la información actualizada para devolverla al frontend
        // Seguimos trayendo todos los campos para que la tabla en pantalla no pierda datos
        $sqlObtener = "SELECT 
                            u.idUnidad AS idUnidadMedica, 
                            u.nombre AS nombre_unidad, 
                            u.afiliacion, 
                            u.categoria, 
                            IF(u.es_prioritaria = 1, 'Sí', 'No') AS es_prioritaria, 
                            c.ciudad, 
                            u.telefono, 
                            u.email AS correo_electronico
                       FROM registro_unidad u
                       INNER JOIN catalogo_ubicacion c ON u.idUbicacion = c.idUbicacion
                       WHERE u.idUnidad = :idUnidad";
                       
        $stmtObtener = $pdo->prepare($sqlObtener);
        $stmtObtener->execute(['idUnidad' => $idUnidadMedica]);
        
        // Usamos fetch() porque es un solo registro maestro
        $datosActualizados = $stmtObtener->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Datos de contacto de la unidad médica actualizados correctamente.",
            "datos"   => $datosActualizados
        ]);
        
        exit;
    }
}

?>