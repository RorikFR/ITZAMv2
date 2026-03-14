<?php
header('Content-Type: application/json');

// FORZAR A PHP A MOSTRAR ERRORES (Quitar cuando ya funcione)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

// 2. MANEJO DE SOLICITUDES
$metodo = $_SERVER['REQUEST_METHOD'];

// --- LEER DATOS (BÚSQUEDA) ---
if ($metodo === 'GET') {
    $busqueda = isset($_GET['q']) ? $_GET['q'] : '';
    
    // Si hay texto, buscamos por cedula
    if($busqueda) {
        $sql = "SELECT 
                    idPersonal, 
                    nombre, 
                    apellido_p AS apellido_paterno, 
                    apellido_m AS apellido_materno, 
                    cedula AS cedula_profesional, 
                    email_inst AS email_institucional, 
                    telefono AS telefono_celular
                FROM registro_personal
                WHERE cedula LIKE :q
                ORDER BY idPersonal DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['q' => "%$busqueda%"]);
    } else {
        // Si no hay búsqueda, traemos los últimos 20 registros
        $stmt = $pdo->query("SELECT 
                                idPersonal, 
                                nombre, 
                                apellido_p AS apellido_paterno, 
                                apellido_m AS apellido_materno, 
                                cedula AS cedula_profesional, 
                                email_inst AS email_institucional, 
                                telefono AS telefono_celular
                            FROM registro_personal
                            ORDER BY idPersonal DESC 
                            LIMIT 20");
    }
    
    echo json_encode($stmt->fetchAll());
}

// --- EDITAR O ELIMINAR ---
if ($metodo === 'POST') {
    // Recibimos los datos JSON del frontend
    $input = json_decode(file_get_contents('php://input'), true);
    
$accion = $input['accion'] ?? '';
    // Asegúrate de que tu JavaScript mande esta variable con el nombre 'idPersonal'
    $idPersonal = $input['idPersonal'] ?? 0; 

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idPersonal > 0) {
        
        try {
            // Eliminamos directamente al personal usando su llave primaria
            $stmt = $pdo->prepare("DELETE FROM registro_personal WHERE idPersonal = :idPersonal");
            $stmt->execute(['idPersonal' => $idPersonal]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Personal de salud eliminado exitosamente."
            ]);
        } catch (PDOException $e) {
            // Si el motor de BD rechaza la eliminación, lo atrapamos aquí
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "No se puede eliminar este personal porque ya tiene consultas o registros asociados en el sistema."
            ]);
        }
        
        exit; // Detenemos la ejecución
    }

/// Asegúrate de extraer la variable correcta que envía tu frontend
    $idPersonal = $input['idPersonal'] ?? 0;

    // --- LÓGICA DE EDICIÓN ---
    if ($accion === 'editar' && $idPersonal > 0) {
        
        // Recibimos los campos específicos para el personal de salud
        // Asegúrate de que tu JSON del frontend use estos nombres exactos
        $email_institucional = $input['email_institucional'] ?? '';
        $telefono_celular = $input['telefono_celular'] ?? '';
        
        // PASO 1: Actualizar la tabla maestra (registro_personal)
        // Al estar normalizado, todo se actualiza en un solo movimiento
        $stmt = $pdo->prepare("UPDATE registro_personal 
                                SET 
                                    email_inst = :email_institucional,
                                    telefono = :telefono_celular
                                WHERE idPersonal = :idPersonal");
        
        $stmt->execute([
            'email_institucional' => $email_institucional,
            'telefono_celular'    => $telefono_celular,
            'idPersonal'          => $idPersonal
        ]);
        
        // PASO 2: OBTENER la información actualizada para devolverla al frontend
        // Usamos una consulta simple, directa y con los alias correctos
        $sqlObtener = "SELECT 
                            idPersonal, 
                            nombre, 
                            apellido_p AS apellido_paterno, 
                            apellido_m AS apellido_materno, 
                            cedula AS cedula_profesional, 
                            email_inst AS email_institucional, 
                            telefono AS telefono_celular
                       FROM registro_personal
                       WHERE idPersonal = :idPersonal";
                       
        $stmtObtener = $pdo->prepare($sqlObtener);
        $stmtObtener->execute(['idPersonal' => $idPersonal]);
        
        $datosActualizados = $stmtObtener->fetch(PDO::FETCH_ASSOC);
        
        // Devolvemos el mensaje de éxito y la fila completa con los datos frescos
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Personal de salud actualizado correctamente.",
            "datos"   => $datosActualizados
        ]);
        exit;
    }
}
?>