<?php
session_start();
header('Content-Type: application/json');

//DEV ONLY - quitar en prod.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// 🛡️ REGLA DE NEGOCIO: Filtrar por la unidad del usuario en sesión
$idUnidad_Usuario = $_SESSION['idUnidad'] ?? 2; 

// --- CARGAR PROVEEDORES ---
if ($metodo === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'cargar_proveedores') {
    try {
        $stmt = $pdo->query("SELECT idProveedor, nombre FROM proveedores ORDER BY nombre ASC");
        echo json_encode(["proveedores" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (PDOException $e) {
        echo json_encode(["proveedores" => [], "error" => "Error al consultar la base de datos."]);
    }
    exit;
}

// --- LÓGICA DE LECTURA Y FILTRADO (GET) ---
if ($metodo === 'GET') {
    $filtroCategoria = $_GET['cat'] ?? 'Todos';

    // 💊 Consulta Medicamentos
    $sqlMedicamentos = "SELECT 
                            m.idMed AS id, 
                            c.nombre AS Nombre, 
                            m.cantidad AS Cantidad, 
                            m.idProveedor,
                            p.nombre AS Proveedor, 
                            'Medicamento' AS Categoria 
                        FROM inventario_medicamentos m
                        INNER JOIN cat_medicamentos c ON m.idCatalogoMed = c.idCatalogoMed
                        LEFT JOIN proveedores p ON m.idProveedor = p.idProveedor
                        WHERE m.idUnidad = :unidadMed";

    // 📦 Consulta Insumos
    $sqlInsumos = "SELECT 
                        i.idInsumo AS id, 
                        ci.nombre AS Nombre, 
                        i.cantidad AS Cantidad, 
                        i.idProveedor,
                        p.nombre AS Proveedor, 
                        'Insumo' AS Categoria 
                   FROM inventario_insumos i
                   INNER JOIN cat_insumos ci ON i.idCatalogoInsumo = ci.idCatalogoInsumo
                   LEFT JOIN proveedores p ON i.idProveedor = p.idProveedor
                   WHERE i.idUnidad = :unidadIns";

    // 🛠️ Consulta Equipo Médico (ACTUALIZADO A 3FN)
    $sqlEquipo = "SELECT 
                        e.idItem AS id, 
                        ce.nombre AS Nombre, 
                        e.cantidad AS Cantidad, 
                        e.idProveedor,
                        p.nombre AS Proveedor, 
                        'Equipo Médico' AS Categoria 
                  FROM inventario_equipo e
                  INNER JOIN cat_equipo ce ON e.idCatalogoEquipo = ce.idCatalogoEquipo
                  LEFT JOIN proveedores p ON e.idProveedor = p.idProveedor
                  WHERE e.idUnidad = :unidadEq";

    $consultasAEjecutar = [];
    $parametros = [];

    if ($filtroCategoria === 'Todos' || $filtroCategoria === 'Medicamento') {
        $consultasAEjecutar[] = $sqlMedicamentos;
        $parametros['unidadMed'] = $idUnidad_Usuario;
    }
    if ($filtroCategoria === 'Todos' || $filtroCategoria === 'Insumo') {
        $consultasAEjecutar[] = $sqlInsumos;
        $parametros['unidadIns'] = $idUnidad_Usuario;
    }
    if ($filtroCategoria === 'Todos' || $filtroCategoria === 'Equipo Médico') {
        $consultasAEjecutar[] = $sqlEquipo;
        $parametros['unidadEq'] = $idUnidad_Usuario;
    }

    $sqlFinal = implode(" UNION ALL ", $consultasAEjecutar) . " ORDER BY Nombre ASC LIMIT 100";

    try {
        $stmt = $pdo->prepare($sqlFinal);
        $stmt->execute($parametros);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error al obtener el inventario.", "detalle" => $e->getMessage()]);
    }
    exit;
}

// --- EDITAR O ELIMINAR ---
if ($metodo === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idItem = $input['id'] ?? 0; 
    $categoria = $input['categoria'] ?? ''; 

    $tablaDestino = '';
    $columnaId = '';

    switch ($categoria) {
        case 'Medicamento': $tablaDestino = 'inventario_medicamentos'; $columnaId = 'idMed'; break;
        case 'Insumo': $tablaDestino = 'inventario_insumos'; $columnaId = 'idInsumo'; break;
        case 'Equipo Médico': $tablaDestino = 'inventario_equipo'; $columnaId = 'idItem'; break;
    }

    if (empty($tablaDestino) || $idItem <= 0) {
        echo json_encode(["estatus" => "error", "mensaje" => "Categoría o ID inválido."]);
        exit;
    }

    // ELIMINAR
    if ($accion === 'eliminar') {
        try {
            $stmt = $pdo->prepare("DELETE FROM $tablaDestino WHERE $columnaId = :id");
            $stmt->execute(['id' => $idItem]);
            echo json_encode(["estatus" => "exito", "mensaje" => "Artículo eliminado exitosamente."]);
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "No se puede eliminar: el artículo está en uso."]);
        }
        exit; 
    }

    // EDICIÓN (TODAS LAS TABLAS EN 3FN)
    if ($accion === 'editar') {
        $cantidad = filter_var($input['cantidad'] ?? 0, FILTER_VALIDATE_INT);
        $idProveedor = filter_var($input['idProveedor'] ?? 0, FILTER_VALIDATE_INT); 
        
        if (!$idProveedor || $cantidad === false || $cantidad < 0) {
            echo json_encode(["estatus" => "error", "mensaje" => "Cantidades o Proveedor inválidos."]);
            exit;
        }

        try {
            // Como las 3 tablas ya están en 3FN, el UPDATE es idéntico para todas: solo cantidad y proveedor
            $stmt = $pdo->prepare("UPDATE $tablaDestino SET cantidad = :cantidad, idProveedor = :idProv WHERE $columnaId = :id");
            $stmt->execute(['cantidad' => $cantidad, 'idProv' => $idProveedor, 'id' => $idItem]);
            
            // Preparamos el query de retorno según la tabla de catálogo correspondiente
            if ($categoria === 'Medicamento') {
                $sqlObtener = "SELECT t.$columnaId AS id, c.nombre AS Nombre, t.cantidad AS Cantidad, p.nombre AS Proveedor, :cat AS Categoria 
                               FROM $tablaDestino t INNER JOIN cat_medicamentos c ON t.idCatalogoMed = c.idCatalogoMed
                               LEFT JOIN proveedores p ON t.idProveedor = p.idProveedor WHERE t.$columnaId = :id";
            } else if ($categoria === 'Insumo') {
                $sqlObtener = "SELECT t.$columnaId AS id, ci.nombre AS Nombre, t.cantidad AS Cantidad, p.nombre AS Proveedor, :cat AS Categoria 
                               FROM $tablaDestino t INNER JOIN cat_insumos ci ON t.idCatalogoInsumo = ci.idCatalogoInsumo
                               LEFT JOIN proveedores p ON t.idProveedor = p.idProveedor WHERE t.$columnaId = :id";
            } else {
                // Equipo Médico
                $sqlObtener = "SELECT t.$columnaId AS id, ce.nombre AS Nombre, t.cantidad AS Cantidad, p.nombre AS Proveedor, :cat AS Categoria 
                               FROM $tablaDestino t INNER JOIN cat_equipo ce ON t.idCatalogoEquipo = ce.idCatalogoEquipo
                               LEFT JOIN proveedores p ON t.idProveedor = p.idProveedor WHERE t.$columnaId = :id";
            }

            $stmtObtener = $pdo->prepare($sqlObtener);
            $stmtObtener->execute(['id' => $idItem, 'cat' => $categoria]);
            
            echo json_encode(["estatus" => "exito", "mensaje" => "Artículo actualizado.", "datos" => $stmtObtener->fetch(PDO::FETCH_ASSOC)]);
            
        } catch (PDOException $e) {
            echo json_encode(["estatus" => "error", "mensaje" => "Error al actualizar: " . $e->getMessage()]);
        }
        exit;
    }
}
?>