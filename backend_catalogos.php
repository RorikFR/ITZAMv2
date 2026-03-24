<?php
//Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';      

//RBAC
requerir_roles_api(['Administrador']); 

//Conexión a DB
require 'db_conn.php';

//Lista de catalogos permitidos
$catalogos_permitidos = [
    'cat_motivos_asesoria' => ['col_id' => 'idMotivo', 'col_valor' => 'nombre_motivo'],
    'cat_tipo_consulta'    => ['col_id' => 'idTipoConsulta', 'col_valor' => 'nombre_tipo'],
    'cat_especialidades'   => ['col_id' => 'idEspecialidad', 'col_valor' => 'nombre_especialidad'],
    'cat_puestos'          => ['col_id' => 'idPuesto', 'col_valor' => 'nombre_puesto'],
    'cat_prioridad_lab'    => ['col_id' => 'idPrioridad', 'col_valor' => 'nombre_prioridad'],
    'cat_estudios_laboratorio' => ['col_id' => 'idEstudio', 'col_valor' => 'nombre_estudio'],
    'proveedores'          => ['col_id' => 'idProveedor', 'col_valor' => 'nombre'],
    'cat_afiliacion'       => ['col_id' => 'idAfiliacion', 'col_valor' => 'nombre_afiliacion'],
    'cat_categoria'        => ['col_id' => 'idCategoria', 'col_valor' => 'nombre_categoria'],
    
    //Solo lectura
    'registro_unidad'      => [
        'col_id'    => 'idUnidad', 
        'col_valor' => "CONCAT(nombre, ' (', (SELECT nombre_afiliacion FROM cat_afiliacion WHERE cat_afiliacion.idAfiliacion = registro_unidad.idAfiliacion LIMIT 1), ')')"
    ],
    'catalogo_ubicacion'   => [
        'col_id'    => 'idUbicacion',
        'col_valor' => "CONCAT(codigo_postal, ' - ', colonia, ', ', ciudad)" 
    ]
];

$catalogos_solo_lectura = ['registro_unidad', 'catalogo_ubicacion'];

$method = $_SERVER['REQUEST_METHOD'];

//Carga de datos
if ($method === 'GET') {
    
    // Buscar código postal
    if (isset($_GET['accion']) && $_GET['accion'] === 'buscar_cp') {
        
        $cp = preg_replace('/\D/', '', $_GET['cp'] ?? '');

        if (strlen($cp) !== 5) {
            echo json_encode(["estatus" => "error", "mensaje" => "El código postal debe ser de 5 dígitos."]);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT idUbicacion, colonia, ciudad, estado FROM catalogo_ubicacion WHERE codigo_postal = :cp ORDER BY colonia ASC");
            $stmt->execute(['cp' => $cp]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($resultados) > 0) {
                echo json_encode(["estatus" => "exito", "data" => $resultados]);
            } else {
                echo json_encode(["estatus" => "error", "mensaje" => "CP no encontrado en la base de datos."]);
            }
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error interno de base de datos."]);
        }
        exit;
    }

    //Catalogos
    $tabla_solicitada = $_GET['tabla'] ?? '';

    if (!array_key_exists($tabla_solicitada, $catalogos_permitidos)) {
        echo json_encode(["error" => "Catálogo no válido o no autorizado por el sistema."]);
        exit;
    }

    $col_id = $catalogos_permitidos[$tabla_solicitada]['col_id'];
    $col_valor = $catalogos_permitidos[$tabla_solicitada]['col_valor'];

    try {
        $sql = "SELECT $col_id AS id, $col_valor AS valor FROM $tabla_solicitada ORDER BY valor ASC";
        $stmt = $pdo->query($sql);
        
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo json_encode(["error" => "Error al consultar el catálogo seleccionado."]);
    }
    exit;
}


//Crear, Editar o Eliminar
elseif ($method === 'POST') {
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $tabla_solicitada = $input['tabla'] ?? '';

    //Verificar que catalogo este en lista permitida
    if (!array_key_exists($tabla_solicitada, $catalogos_permitidos)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Intento de acceso a tabla no autorizada."]);
        exit;
    }

    //Verificar si es de solo lectura
    if (in_array($tabla_solicitada, $catalogos_solo_lectura)) {
        echo json_encode(["estatus" => "error", "mensaje" => "Operación denegada. Este catálogo es gestionado por un módulo especializado y es de solo lectura aquí."]);
        exit;
    }

    $col_id = $catalogos_permitidos[$tabla_solicitada]['col_id'];
    $col_valor = $catalogos_permitidos[$tabla_solicitada]['col_valor'];

    //Sanitizar entradas
    $valor = strip_tags(trim($input['valor'] ?? ''));

    //Crear registro
    if ($accion === 'crear') {
        if (empty($valor)) {
            echo json_encode(["estatus" => "error", "mensaje" => "El valor no puede estar vacío."]); exit;
        }

        try {
            $sql = "INSERT INTO $tabla_solicitada ($col_valor) VALUES (:valor)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['valor' => $valor]);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Registro agregado correctamente."]);
        } catch (Exception $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error al guardar el nuevo registro."]);
        }
    }
    
    //Editar registro
    elseif ($accion === 'editar') {
        $id = filter_var($input['id'] ?? '', FILTER_VALIDATE_INT);

        if (empty($id) || empty($valor)) {
            echo json_encode(["estatus" => "error", "mensaje" => "Datos incompletos para actualizar."]); exit;
        }

        try {
            $sql = "UPDATE $tabla_solicitada SET $col_valor = :valor WHERE $col_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['valor' => $valor, 'id' => $id]);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Registro actualizado correctamente."]);
        } catch (Exception $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error al intentar actualizar el registro."]);
        }
    }
    
    //Eliminar registro
    elseif ($accion === 'eliminar') {
        $id = filter_var($input['id'] ?? '', FILTER_VALIDATE_INT);

        if (empty($id)) {
            echo json_encode(["estatus" => "error", "mensaje" => "ID no proporcionado para eliminar."]); exit;
        }

        try {
            $sql = "DELETE FROM $tabla_solicitada WHERE $col_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Registro eliminado exitosamente."]);
        } catch (PDOException $e) {
            //Mantener integridad referencial. (FK)
            if ($e->getCode() == 23000) {
                echo json_encode([
                    "estatus" => "error", 
                    "mensaje" => "No se puede borrar. Este registro ya está siendo utilizado (por ejemplo, en un expediente o por un usuario)."
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