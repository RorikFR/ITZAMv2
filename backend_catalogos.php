<?php
// Configuramos encabezados para JSON y UTF-8
header('Content-Type: application/json; charset=utf-8');

// --- 1. CONFIGURACIÓN DE LA BASE DE DATOS ---
require 'db_conn.php';

// --- 2. EL ESCUDO DE SEGURIDAD: LA LISTA BLANCA ---
$catalogos_permitidos = [
    'cat_motivos_asesoria' => [
        'col_id'    => 'idMotivo', 
        'col_valor' => 'nombre_motivo'
    ],
    'cat_tipo_consulta' => [
        'col_id'    => 'idTipoConsulta', 
        'col_valor' => 'nombre_tipo'
    ],
    'cat_especialidades' => [
        'col_id'    => 'idEspecialidad', 
        'col_valor' => 'nombre_especialidad'
    ],
    'cat_puestos' => [
        'col_id'    => 'idPuesto', 
        'col_valor' => 'nombre_puesto'
    ],
    'cat_prioridad_lab' => [
        'col_id'    => 'idPrioridad', 
        'col_valor' => 'nombre_prioridad'
    ],
    'cat_estudios_laboratorio' => [
        'col_id'    => 'idEstudio', 
        'col_valor' => 'nombre_estudio'
    ],
    'proveedores' => [
        'col_id'    => 'idProveedor', 
        'col_valor' => 'nombre'
    ],
    'registro_unidad' => [
        'col_id'    => 'idUnidad', 
        // Usamos una subconsulta SQL para extraer el nombre de la otra tabla en tiempo real
        'col_valor' => "CONCAT(nombre, ' (', (SELECT nombre_afiliacion FROM cat_afiliacion WHERE cat_afiliacion.idAfiliacion = registro_unidad.idAfiliacion), ')')"
    ],
    'cat_afiliacion' => [
        'col_id'    => 'idAfiliacion', 
        'col_valor' => 'nombre_afiliacion'
    ],
    'cat_categoria' => [
        'col_id'    => 'idCategoria', 
        'col_valor' => 'nombre_categoria'
    ],
    'catalogo_ubicacion' => [
        'col_id'    => 'idUbicacion',
        'col_valor' => "CONCAT(codigo_postal, ' - ', colonia, ', ', ciudad)" 
    ]
];

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// 🔵 PETICIONES GET: LECTURA DE DATOS
// ==========================================
if ($method === 'GET') {
    
    // 🔥 1. ACCIONES ESPECIALES PRIMERO (Antes de la Lista Blanca) 🔥
    if (isset($_GET['accion']) && $_GET['accion'] === 'buscar_cp') {
        
        // Sanitización extrema: Forzamos que solo lleguen números
        $cp = preg_replace('/\D/', '', $_GET['cp'] ?? '');

        // Validación de longitud
        if (strlen($cp) !== 5) {
            echo json_encode(["estatus" => "error", "mensaje" => "El código postal debe ser de 5 dígitos."]);
            exit;
        }

        try {
            // Traemos todas las colonias que compartan este CP, junto con su ciudad y estado
            $stmt = $pdo->prepare("
                SELECT idUbicacion, colonia, ciudad, estado 
                FROM catalogo_ubicacion 
                WHERE codigo_postal = :cp 
                ORDER BY colonia ASC
            ");
            $stmt->execute(['cp' => $cp]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Respuesta al Frontend
            if (count($resultados) > 0) {
                echo json_encode(["estatus" => "exito", "data" => $resultados]);
            } else {
                echo json_encode(["estatus" => "error", "mensaje" => "CP no encontrado."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error de base de datos."]);
        }
        
        // Matamos el proceso aquí para que no intente validar la Lista Blanca
        exit;
    }

    // 🛡️ 2. LÓGICA NORMAL DE CATÁLOGOS (La Lista Blanca) 🛡️
    $tabla_solicitada = $_GET['tabla'] ?? '';

    // Verificamos que la tabla exista en nuestra Lista Blanca
    if (!array_key_exists($tabla_solicitada, $catalogos_permitidos)) {
        echo json_encode(["error" => "Catálogo no válido o no autorizado por el sistema."]);
        exit;
    }

    $col_id = $catalogos_permitidos[$tabla_solicitada]['col_id'];
    $col_valor = $catalogos_permitidos[$tabla_solicitada]['col_valor'];

    try {
        // Armamos la consulta dinámica segura
        $sql = "SELECT $col_id AS id, $col_valor AS valor FROM $tabla_solicitada ORDER BY $col_valor ASC";
        $stmt = $pdo->query($sql);
        
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo json_encode(["error" => "Error al consultar el catálogo."]);
    }
}

// ==========================================
// 🔴 PETICIONES POST: CREAR, EDITAR, ELIMINAR
// ==========================================
elseif ($method === 'POST') {
    
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    
    $accion = $input['accion'] ?? '';
    $tabla_solicitada = $input['tabla'] ?? '';

    // Verificamos seguridad nuevamente para las acciones POST
    if (!array_key_exists($tabla_solicitada, $catalogos_permitidos)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Intento de acceso a tabla no autorizada."]);
        exit;
    }

    $col_id = $catalogos_permitidos[$tabla_solicitada]['col_id'];
    $col_valor = $catalogos_permitidos[$tabla_solicitada]['col_valor'];

    // --- ACCIÓN: CREAR (NUEVO REGISTRO) ---
    if ($accion === 'crear') {
        $valor = trim($input['valor'] ?? '');

        if (empty($valor)) {
            echo json_encode(["estatus" => "error", "mensaje" => "El valor no puede estar vacío."]); exit;
        }

        try {
            $sql = "INSERT INTO $tabla_solicitada ($col_valor) VALUES (:valor)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['valor' => $valor]);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Registro agregado correctamente al catálogo."]);
        } catch (Exception $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error al guardar el nuevo registro."]);
        }
    }
    
    // --- ACCIÓN: EDITAR ---
    elseif ($accion === 'editar') {
        $id = filter_var($input['id'] ?? '', FILTER_VALIDATE_INT);
        $valor = trim($input['valor'] ?? '');

        if (empty($id) || empty($valor)) {
            echo json_encode(["estatus" => "error", "mensaje" => "Datos incompletos para actualizar."]); exit;
        }

        try {
            $sql = "UPDATE $tabla_solicitada SET $col_valor = :valor WHERE $col_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['valor' => $valor, 'id' => $id]);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Registro actualizado correctamente."]);
        } catch (Exception $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error de base de datos al actualizar."]);
        }
    }
    
    // --- ACCIÓN: ELIMINAR ---
    elseif ($accion === 'eliminar') {
        $id = filter_var($input['id'] ?? '', FILTER_VALIDATE_INT);

        if (empty($id)) {
            echo json_encode(["estatus" => "error", "mensaje" => "ID no proporcionado para eliminar."]); exit;
        }

        try {
            $sql = "DELETE FROM $tabla_solicitada WHERE $col_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Registro eliminado del catálogo."]);
        } catch (PDOException $e) {
            // 🔥 EL ESCUDO 3FN: Evitamos que borren algo que ya está en uso
            if ($e->getCode() == 23000) {
                echo json_encode([
                    "estatus" => "error", 
                    "mensaje" => "No se puede borrar. Este registro ya está siendo utilizado en expedientes, consultas o inventario."
                ]);
            } else {
                echo json_encode(["estatus" => "error", "mensaje" => "Error interno al intentar eliminar el registro."]);
            }
        }
    } 
    else {
        echo json_encode(["estatus" => "error", "mensaje" => "Acción no reconocida."]);
    }
}
?>