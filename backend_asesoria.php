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
    
    // Si hay texto, buscamos por Paciente O Curp
    if($busqueda) {
        $sql = "SELECT 
                    a.idAsesoria, 
                    a.fecha_solicitud, 
                    p.curp, 
                    p.nombre, 
                    p.apellido_p, 
                    p.apellido_m, 
                    a.motivo, 
                    a.comentarios 
                FROM registro_asesorias a
                INNER JOIN registro_paciente p ON a.idPaciente = p.idPaciente
                WHERE p.curp LIKE :q
                ORDER BY a.idAsesoria DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['q' => "%$busqueda%"]);
    } else {
        // Si no hay búsqueda, traemos los últimos 20 registros
        $stmt = $pdo->query("SELECT 
                                a.idAsesoria, 
                                p.curp, 
                                p.nombre, 
                                p.apellido_p,
                                p.apellido_m,
                                m.nombre_motivo AS motivo, -- Traemos el texto del catálogo y lo renombramos
                                a.comentarios, 
                                a.fecha_solicitud
                            FROM registro_asesorias a
                            -- Unimos con la tabla de pacientes para traer los datos personales
                            LEFT JOIN registro_paciente p ON a.idPaciente = p.idPaciente 
                            -- Unimos con el nuevo catálogo para traer el motivo
                            LEFT JOIN cat_motivos_asesoria m ON a.idMotivo = m.idMotivo
                            ORDER BY a.idAsesoria DESC");
    }
    
    echo json_encode($stmt->fetchAll());
}

// --- EDITAR O ELIMINAR ---
if ($metodo === 'POST') {
    // Recibimos los datos JSON del frontend
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idAsesoria = $input['idAsesoria'] ?? 0;

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idAsesoria > 0) {
        $stmt = $pdo->prepare("DELETE FROM registro_asesorias WHERE idAsesoria = :idAsesoria");
        $stmt->execute(['idAsesoria' => $idAsesoria]);
        
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Registro eliminado exitosamente."
        ]);
        exit; // Detenemos la ejecución para no procesar más abajo
    }

// --- LÓGICA DE EDICIÓN ---
    if ($accion === 'editar' && $idAsesoria > 0) {
        
        // 🔥 CAMBIO CLAVE 3FN: Ahora esperamos el ID del motivo, no el texto
        $idMotivo = $input['idMotivo'] ?? null; 
        $curp = $input['curp'] ?? '';
        $comentarios = $input['comentarios'] ?? '';
        
        // PASO 1: Validar si el paciente existe usando su CURP
        $stmtPaciente = $pdo->prepare("SELECT idPaciente FROM registro_paciente WHERE curp = :curp");
        $stmtPaciente->execute(['curp' => $curp]);
        $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

        // Si el paciente no existe, abortamos la actualización
        if (!$paciente) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "El CURP ingresado no existe en la base de datos. Por favor, registre al paciente primero."
            ]);
            exit; // Crucial para detener el script aquí
        }
        
        // PASO 2: El paciente existe, extraemos su ID real
        $idPacienteEncontrado = $paciente['idPaciente'];
        
        // PASO 3: Actualizamos la asesoría utilizando las llaves foráneas correctas
        try {
            $stmt = $pdo->prepare("UPDATE registro_asesorias 
                SET 
                    idPaciente = :idPaciente, 
                    idMotivo = :idMotivo, 
                    comentarios = :comentarios 
                WHERE idAsesoria = :idAsesoria");
            
            $stmt->execute([
                'idPaciente'  => $idPacienteEncontrado, 
                'idMotivo'    => $idMotivo, 
                'comentarios' => $comentarios,
                'idAsesoria'  => $idAsesoria
            ]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Registro actualizado correctamente."
            ]);
        } catch (PDOException $e) {
            // Si hay un error SQL (ej. el idMotivo no existe en el catálogo), lo atrapamos
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Error en la base de datos al intentar actualizar el registro."
            ]);
        }
        exit;
    }
}
?>