<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';      

//RBAC
requerir_roles_api(['Administrativo', 'Administrador']); 

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

//Identificar si usuario es Administrador
$idUnidad_Usuario = $_SESSION['idUnidad'] ?? null; 
$rolUsuario = $_SESSION['rol'] ?? '';
$esAdminGlobal = in_array($rolUsuario, ['Administrador', 'SuperAdmin']);

// Si no es administrador y no tiene unidad médica asignada lanzar error
if (!$idUnidad_Usuario && !$esAdminGlobal) {
    echo json_encode(["error" => "Error de sesión: No se identificó la Unidad Médica del usuario."]);
    exit;
}


// Si es administrador, puede ver todo. Si no, filtra por su unidad.
$filtroUnidadMed = $esAdminGlobal ? "" : "WHERE m.idUnidad = :unidadMed";
$filtroUnidadIns = $esAdminGlobal ? "" : "WHERE i.idUnidad = :unidadIns";
$filtroUnidadEq  = $esAdminGlobal ? "" : "WHERE e.idUnidad = :unidadEq";

// Cargar datos de proveedores
if ($metodo === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'cargar_proveedores') {
    try {
        $stmt = $pdo->query("SELECT idProveedor, nombre FROM proveedores ORDER BY nombre ASC");
        echo json_encode(["proveedores" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (PDOException $e) {
        echo json_encode(["proveedores" => [], "error" => "Error interno de base de datos."]);
    }
    exit;
}

if ($metodo === 'GET') {
    $filtroCategoria = $_GET['cat'] ?? 'Todos';

    // Consulta de medicamentos
    $sqlMedicamentos = "SELECT m.idMed AS id, c.nombre AS Nombre, m.cantidad AS Cantidad, m.idProveedor, p.nombre AS Proveedor, 'Medicamento' AS Categoria, u.nombre AS Unidad
                        FROM inventario_medicamentos m
                        INNER JOIN cat_medicamentos c ON m.idCatalogoMed = c.idCatalogoMed
                        INNER JOIN registro_unidad u ON m.idUnidad = u.idUnidad
                        LEFT JOIN proveedores p ON m.idProveedor = p.idProveedor
                        $filtroUnidadMed";

    // Consulta de insumos
    $sqlInsumos = "SELECT i.idInsumo AS id, ci.nombre AS Nombre, i.cantidad AS Cantidad, i.idProveedor, p.nombre AS Proveedor, 'Insumo' AS Categoria, u.nombre AS Unidad
                   FROM inventario_insumos i
                   INNER JOIN cat_insumos ci ON i.idCatalogoInsumo = ci.idCatalogoInsumo
                   INNER JOIN registro_unidad u ON i.idUnidad = u.idUnidad
                   LEFT JOIN proveedores p ON i.idProveedor = p.idProveedor
                   $filtroUnidadIns";

    // Consulta de equipo médico
    $sqlEquipo = "SELECT e.idItem AS id, ce.nombre AS Nombre, e.cantidad AS Cantidad, e.idProveedor, p.nombre AS Proveedor, 'Equipo Médico' AS Categoria, u.nombre AS Unidad
                  FROM inventario_equipo e
                  INNER JOIN cat_equipo ce ON e.idCatalogoEquipo = ce.idCatalogoEquipo
                  INNER JOIN registro_unidad u ON e.idUnidad = u.idUnidad
                  LEFT JOIN proveedores p ON e.idProveedor = p.idProveedor
                  $filtroUnidadEq";

    $consultasAEjecutar = [];
    $parametros = [];

    if ($filtroCategoria === 'Todos' || $filtroCategoria === 'Medicamento') {
        $consultasAEjecutar[] = $sqlMedicamentos;
        if (!$esAdminGlobal) $parametros['unidadMed'] = $idUnidad_Usuario;
    }
    if ($filtroCategoria === 'Todos' || $filtroCategoria === 'Insumo') {
        $consultasAEjecutar[] = $sqlInsumos;
        if (!$esAdminGlobal) $parametros['unidadIns'] = $idUnidad_Usuario;
    }
    if ($filtroCategoria === 'Todos' || $filtroCategoria === 'Equipo Médico') {
        $consultasAEjecutar[] = $sqlEquipo;
        if (!$esAdminGlobal) $parametros['unidadEq'] = $idUnidad_Usuario;
    }

    $sqlFinal = implode(" UNION ALL ", $consultasAEjecutar) . " ORDER BY Nombre ASC LIMIT 200";

    try {
        $stmt = $pdo->prepare($sqlFinal);
        $stmt->execute($parametros);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error interno al obtener el inventario."]);
    }
    exit;
}

// Editar y eliminar registros
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    //Sanitizar entradas
    $accion = filter_var($input['accion'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $idItem = filter_var($input['id'] ?? 0, FILTER_VALIDATE_INT); 
    $categoria = filter_var($input['categoria'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS); 

    $tablaDestino = ''; $columnaId = '';

    switch ($categoria) {
        case 'Medicamento': $tablaDestino = 'inventario_medicamentos'; $columnaId = 'idMed'; break;
        case 'Insumo': $tablaDestino = 'inventario_insumos'; $columnaId = 'idInsumo'; break;
        case 'Equipo Médico': $tablaDestino = 'inventario_equipo'; $columnaId = 'idItem'; break;
    }

    if (empty($tablaDestino) || $idItem <= 0) {
        echo json_encode(["estatus" => "error", "mensaje" => "Categoría o ID inválido."]);
        exit;
    }

    // Eliminar registros
    if ($accion === 'eliminar') {
        try {
            
            //Restricciones no aplican al administrador
            $condicionUnidad = $esAdminGlobal ? "" : "AND idUnidad = :unidad";
            
            $stmt = $pdo->prepare("DELETE FROM $tablaDestino WHERE $columnaId = :id $condicionUnidad");
            
            $paramsDelete = ['id' => $idItem];
            if (!$esAdminGlobal) $paramsDelete['unidad'] = $idUnidad_Usuario;
            
            $stmt->execute($paramsDelete);
            echo json_encode(["estatus" => "exito", "mensaje" => "Artículo eliminado exitosamente."]);
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error al intentar eliminar el artículo."]);
        }
        exit; 
    }

    // Editar registros
    if ($accion === 'editar') {
        $cantidad = filter_var($input['cantidad'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $idProveedor = filter_var($input['idProveedor'] ?? 0, FILTER_VALIDATE_INT); 
        
        if (!$idProveedor || !is_numeric($cantidad) || $cantidad < 0) {
            echo json_encode(["estatus" => "error", "mensaje" => "Datos de edición inválidos."]);
            exit;
        }

        try {
            $condicionUnidad = $esAdminGlobal ? "" : "AND idUnidad = :unidad";
            
            $stmt = $pdo->prepare("UPDATE $tablaDestino SET cantidad = :cantidad, idProveedor = :idProv WHERE $columnaId = :id $condicionUnidad");
            
            $paramsUpdate = ['cantidad' => $cantidad, 'idProv' => $idProveedor, 'id' => $idItem];
            if (!$esAdminGlobal) $paramsUpdate['unidad'] = $idUnidad_Usuario;
            
            $stmt->execute($paramsUpdate);
            
            // Retornar datos actualizados al frontend
            $sqlObtener = "";
            if ($categoria === 'Medicamento') {
                $sqlObtener = "SELECT t.$columnaId AS id, c.nombre AS Nombre, t.cantidad AS Cantidad, p.nombre AS Proveedor, :cat AS Categoria, u.nombre AS Unidad 
                               FROM $tablaDestino t 
                               INNER JOIN cat_medicamentos c ON t.idCatalogoMed = c.idCatalogoMed
                               INNER JOIN registro_unidad u ON t.idUnidad = u.idUnidad
                               LEFT JOIN proveedores p ON t.idProveedor = p.idProveedor WHERE t.$columnaId = :id";
            } else if ($categoria === 'Insumo') {
                $sqlObtener = "SELECT t.$columnaId AS id, ci.nombre AS Nombre, t.cantidad AS Cantidad, p.nombre AS Proveedor, :cat AS Categoria, u.nombre AS Unidad 
                               FROM $tablaDestino t 
                               INNER JOIN cat_insumos ci ON t.idCatalogoInsumo = ci.idCatalogoInsumo
                               INNER JOIN registro_unidad u ON t.idUnidad = u.idUnidad
                               LEFT JOIN proveedores p ON t.idProveedor = p.idProveedor WHERE t.$columnaId = :id";
            } else {
                $sqlObtener = "SELECT t.$columnaId AS id, ce.nombre AS Nombre, t.cantidad AS Cantidad, p.nombre AS Proveedor, :cat AS Categoria, u.nombre AS Unidad 
                               FROM $tablaDestino t 
                               INNER JOIN cat_equipo ce ON t.idCatalogoEquipo = ce.idCatalogoEquipo
                               INNER JOIN registro_unidad u ON t.idUnidad = u.idUnidad
                               LEFT JOIN proveedores p ON t.idProveedor = p.idProveedor WHERE t.$columnaId = :id";
            }

            $stmtObtener = $pdo->prepare($sqlObtener);
            $stmtObtener->execute(['id' => $idItem, 'cat' => $categoria]);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Inventario actualizado.", "datos" => $stmtObtener->fetch(PDO::FETCH_ASSOC)]);
            
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error interno de base de datos."]);
        }
        exit;
    }
}
?>