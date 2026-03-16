<?php
// Configuramos encabezados para JSON y UTF-8
header('Content-Type: application/json; charset=utf-8');

// --- 1. CONFIGURACIÓN DE LA BASE DE DATOS ---
require 'db_conn.php';

// --- 2. EL ESCUDO DE SEGURIDAD: LA LISTA BLANCA ---
// Aquí mapeamos las tablas permitidas con sus respectivas columnas reales en la BD.
// ¡IMPORTANTE!: Ajusta los nombres de las columnas 'id' y 'valor' para que coincidan con tu BD.
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
    ]
];

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// 🔵 PETICIONES GET: LECTURA DE DATOS
// ==========================================
if ($method === 'GET') {
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
        
        echo json_encode($stmt->fetchAll());
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
        $id = $input['id'] ?? '';
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
        $id = $input['id'] ?? '';

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