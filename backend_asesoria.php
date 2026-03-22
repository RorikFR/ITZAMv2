<?php
// 1. ESCUDOS BASE
require 'seguridad_backend.php'; 
require 'autorizacion.php';      

// 2. 🛑 LA BARRERA DE HIERRO (Nivel 3)
requerir_roles_api(['Médico', 'Enfermería']); 

// 3. CONEXIÓN Y LÓGICA
require 'db_conn.php';

// 2. MANEJO DE SOLICITUDES
$metodo = $_SERVER['REQUEST_METHOD'];

// --- LEER DATOS (BÚSQUEDA Y LISTADO) ---
if ($metodo === 'GET') {
    // Sanitizamos la variable de búsqueda
    $busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
    
    // Si hay texto, buscamos por CURP
    if($busqueda) {
        $sql = "SELECT 
                    a.idAsesoria, 
                    a.idPersonal, -- 🔥 EXTRAEMOS EL ID DEL AUTOR PARA EL FRONTEND
                    a.fecha_solicitud, 
                    p.curp, 
                    p.nombre, 
                    p.apellido_p, 
                    p.apellido_m, 
                    m.nombre_motivo AS motivo, 
                    a.comentarios,
                    -- 🔥 EXTRAEMOS EL NOMBRE DEL PERSONAL MÉDICO
                    CONCAT_WS(' ', med.nombre, med.apellido_p, med.apellido_m) AS personal_medico
                FROM registro_asesorias a
                INNER JOIN registro_paciente p ON a.idPaciente = p.idPaciente
                LEFT JOIN cat_motivos_asesoria m ON a.idMotivo = m.idMotivo
                LEFT JOIN registro_personal med ON a.idPersonal = med.idPersonal
                WHERE p.curp LIKE :q
                ORDER BY a.idAsesoria DESC";
                
        $stmt = $pdo->prepare($sql);
        // Protegemos contra caracteres extraños en la búsqueda LIKE
        $stmt->execute(['q' => "%" . htmlspecialchars($busqueda) . "%"]);
    } else {
        // Si no hay búsqueda, traemos los últimos 20 registros
        $stmt = $pdo->query("SELECT 
                                a.idAsesoria, 
                                a.idPersonal, -- 🔥 EXTRAEMOS EL ID DEL AUTOR PARA EL FRONTEND
                                p.curp, 
                                p.nombre, 
                                p.apellido_p,
                                p.apellido_m,
                                m.nombre_motivo AS motivo, 
                                a.comentarios, 
                                a.fecha_solicitud,
                                -- 🔥 EXTRAEMOS EL NOMBRE DEL PERSONAL MÉDICO
                                CONCAT_WS(' ', med.nombre, med.apellido_p, med.apellido_m) AS personal_medico
                            FROM registro_asesorias a
                            LEFT JOIN registro_paciente p ON a.idPaciente = p.idPaciente 
                            LEFT JOIN cat_motivos_asesoria m ON a.idMotivo = m.idMotivo
                            LEFT JOIN registro_personal med ON a.idPersonal = med.idPersonal
                            ORDER BY a.idAsesoria DESC");
    }
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// --- EDITAR O ELIMINAR (POST) ---
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Sanitización de acción e ID
    $accion = filter_var($input['accion'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $idAsesoria = filter_var($input['idAsesoria'] ?? 0, FILTER_VALIDATE_INT);

    // --- 🛡️ NUEVO CANDADO DE AUTORÍA (Aduana estricta) ---
    if ($idAsesoria > 0 && ($accion === 'editar' || $accion === 'eliminar')) {
        $stmtAutor = $pdo->prepare("SELECT idPersonal FROM registro_asesorias WHERE idAsesoria = :id");
        $stmtAutor->execute(['id' => $idAsesoria]);
        $autor = $stmtAutor->fetch(PDO::FETCH_ASSOC);

        // Si la asesoría no existe, o si el ID del creador no es el mismo ID de mi sesión:
        // Excepción: El SuperAdmin siempre puede editar/borrar cualquier cosa.
        if (!$autor || ($autor['idPersonal'] != $_SESSION['idUsuario'] && $_SESSION['rol'] !== 'Administrador')) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Acceso denegado: Por seguridad, solo puedes modificar o eliminar las asesorías que tú registraste."
            ]);
            exit; // 🛑 El proceso se detiene aquí.
        }
    }
    // --- FIN DEL CANDADO ---

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idAsesoria > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM registro_asesorias WHERE idAsesoria = :idAsesoria");
            $stmt->execute(['idAsesoria' => $idAsesoria]);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Registro eliminado exitosamente."]);
        } catch(PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "No se pudo eliminar el registro."]);
        }
        exit;
    }

    // --- LÓGICA DE EDICIÓN ---
    if ($accion === 'editar' && $idAsesoria > 0) {
        
        // 🛡️ SANITIZACIÓN RÁPIDA (Protegemos la base de datos de inyecciones al editar)
        $idMotivo    = filter_var($input['idMotivo'] ?? null, FILTER_VALIDATE_INT); 
        $curp        = strtoupper(trim($input['curp'] ?? ''));
        $comentarios = htmlspecialchars(trim($input['comentarios'] ?? ''), ENT_QUOTES, 'UTF-8');
        
        if (!$idMotivo || empty($curp) || empty($comentarios)) {
             echo json_encode(["estatus" => "error", "mensaje" => "Datos inválidos o incompletos."]);
             exit;
        }
        
        // PASO 1: Validar si el paciente existe usando su CURP
        $stmtPaciente = $pdo->prepare("SELECT idPaciente FROM registro_paciente WHERE curp = :curp");
        $stmtPaciente->execute(['curp' => $curp]);
        $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

        // Si el paciente no existe, abortamos
        if (!$paciente) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "El CURP ingresado no existe. Registre al paciente primero."
            ]);
            exit; 
        }
        
        // PASO 2: El paciente existe, extraemos su ID real
        $idPacienteEncontrado = $paciente['idPaciente'];
        
        // PASO 3: Actualizamos la asesoría
        try {
            // Nota: No actualizamos idPersonal. El autor original se queda por auditoría médica.
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
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "Error en la base de datos al intentar actualizar el registro."
            ]);
        }
        exit;
    }
}
?>