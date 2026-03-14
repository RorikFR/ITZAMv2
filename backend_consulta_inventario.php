<?php
header('Content-Type: application/json');

//DEV ONLY - quitar en prod.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

// 2. MANEJO DE SOLICITUDES
$metodo = $_SERVER['REQUEST_METHOD'];

// --- CARGAR PROVEEDORES ---
if ($metodo === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'cargar_proveedores') {
    
    try {
        // Hacemos la consulta a la tabla proveedores
        $stmt = $pdo->query("SELECT idProveedor, nombre FROM proveedores ORDER BY nombre ASC");
        $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Devolvemos el JSON exactamente con la estructura que espera tu JavaScript
        echo json_encode([
            "proveedores" => $proveedores
        ]);
        
    } catch (PDOException $e) {
        // Si hay error en la base de datos, mandamos un JSON vacío para no romper el frontend
        echo json_encode([
            "proveedores" => [],
            "error" => "Error al consultar la base de datos."
        ]);
    }
    
    exit;
}


// --- LÓGICA DE LECTURA Y FILTRADO (GET) ---
if ($metodo === 'GET') {
    
    // Capturamos el filtro de categoría. Si no viene nada, asumimos 'Todos'
    $filtroCategoria = $_GET['cat'] ?? 'Todos';

    // 1. Preparamos las tres consultas base por separado
    $sqlMedicamentos = "SELECT 
                            m.idMed AS id, 
                            m.nombre AS Nombre, 
                            m.cantidad AS Cantidad, 
                            m.idProveedor,
                            p.nombre AS Proveedor, 
                            'Medicamento' AS Categoria 
                        FROM inventario_medicamentos m
                        LEFT JOIN proveedores p ON m.idProveedor = p.idProveedor";

    $sqlInsumos = "SELECT 
                        i.idInsumo AS id, 
                        i.nombre AS Nombre, 
                        i.cantidad AS Cantidad, 
                        i.idProveedor,
                        p.nombre AS Proveedor, 
                        'Insumo' AS Categoria 
                   FROM inventario_insumos i
                   LEFT JOIN proveedores p ON i.idProveedor = p.idProveedor";

    $sqlEquipo = "SELECT 
                        e.idItem AS id, 
                        e.nombre AS Nombre, 
                        e.cantidad AS Cantidad, 
                        e.idProveedor,
                        p.nombre AS Proveedor, 
                        'Equipo Médico' AS Categoria 
                  FROM inventario_equipo e
                  LEFT JOIN proveedores p ON e.idProveedor = p.idProveedor";

    // 2. Construimos el arreglo de consultas a ejecutar según el filtro
    $consultasAEjecutar = [];

    if ($filtroCategoria === 'Todos' || $filtroCategoria === 'Medicamento') {
        $consultasAEjecutar[] = $sqlMedicamentos;
    }
    if ($filtroCategoria === 'Todos' || $filtroCategoria === 'Insumo') {
        $consultasAEjecutar[] = $sqlInsumos;
    }
    if ($filtroCategoria === 'Todos' || $filtroCategoria === 'Equipo Médico') {
        $consultasAEjecutar[] = $sqlEquipo;
    }

    // 3. Unimos las consultas seleccionadas con UNION ALL
    // Si solo hay una (ej. eligieron 'Insumo'), implode no agrega el UNION ALL
    $sqlFinal = implode(" UNION ALL ", $consultasAEjecutar);

    // 4. Agregamos el ordenamiento al resultado final combinado
    $sqlFinal .= " ORDER BY Nombre ASC LIMIT 100";

    try {
        $stmt = $pdo->prepare($sqlFinal);
        $stmt->execute();
        
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($datos);
        
    } catch (PDOException $e) {
        echo json_encode([
            "error" => "Error al obtener el inventario.",
            "detalle" => $e->getMessage() // Útil para depurar si algo falla
        ]);
    }
    
    exit;
}
 

// --- EDITAR O ELIMINAR (MÓDULO DE INVENTARIO) ---
if ($metodo === 'POST') {
    // Recibimos los datos JSON del frontend
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idItem = $input['id'] ?? 0; 
    $categoria = $input['categoria'] ?? ''; // 'Medicamento', 'Insumo' o 'Equipo Médico'

    // --- ENRUTADOR DINÁMICO DE TABLAS ---
    $tablaDestino = '';
    $columnaId = '';

    switch ($categoria) {
        case 'Medicamento':
            $tablaDestino = 'inventario_medicamentos';
            $columnaId = 'idMed';
            break;
        case 'Insumo':
            $tablaDestino = 'inventario_insumos';
            $columnaId = 'idInsumo';
            break;
        case 'Equipo Médico':
            $tablaDestino = 'inventario_equipo';
            // Nota: Si en tu base de datos esta columna se llama idEquipo, cámbialo aquí.
            $columnaId = 'idItem'; 
            break;
    }

    // Si mandan una categoría que no existe, detenemos la ejecución
    if (empty($tablaDestino) && $idItem > 0) {
        echo json_encode(["estatus" => "error", "mensaje" => "Categoría de inventario no válida."]);
        exit;
    }

    // --- LÓGICA DE ELIMINACIÓN ---
    if ($accion === 'eliminar' && $idItem > 0) {
        try {
            // Eliminamos el artículo de la tabla correspondiente
            $stmt = $pdo->prepare("DELETE FROM $tablaDestino WHERE $columnaId = :id");
            $stmt->execute(['id' => $idItem]);
            
            echo json_encode([
                "estatus" => "exito", 
                "mensaje" => "Artículo eliminado exitosamente del inventario."
            ]);
            
        } catch (PDOException $e) {
            // Protección de integridad por si el artículo ya se recetó o usó
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "No se puede eliminar este artículo porque ya tiene historial de movimientos o recetas asociadas."
            ]);
        }
        exit; 
    }

// --- LÓGICA DE EDICIÓN ---
    if ($accion === 'editar' && $idItem > 0) {
        
        $nombre = $input['nombre'] ?? '';
        $cantidad = $input['cantidad'] ?? 0;
        
        // Ahora capturamos el ID directamente. Si el frontend envía vacío, se convierte en 0.
        $idProveedor = $input['idProveedor'] ?? 0; 
        
        // --- ESCUDO DE VALIDACIÓN ESTRICTO ---
        // Detenemos el proceso si el nombre está vacío O si no hay proveedor seleccionado
        if (trim($nombre) === '' || $idProveedor == 0) {
            echo json_encode([
                "estatus" => "error", 
                "mensaje" => "El nombre del artículo y el proveedor son campos obligatorios."
            ]);
            exit;
        }

        // PASO 1: Actualizar la tabla correspondiente
        $stmt = $pdo->prepare("UPDATE $tablaDestino 
            SET 
                nombre = :nombre,
                cantidad = :cantidad,
                idProveedor = :idProveedor
            WHERE $columnaId = :id");
            
        $stmt->execute([
            'nombre'      => $nombre,
            'cantidad'    => $cantidad,
            'idProveedor' => $idProveedor,
            'id'          => $idItem
        ]);
        
        
        // PASO 2: Obtener la información actualizada para refrescar la interfaz
        // Simulamos la misma estructura del UNION ALL usando el LEFT JOIN del proveedor
        $sqlObtener = "SELECT 
                            t.$columnaId AS id, 
                            t.nombre AS Nombre, 
                            t.cantidad AS Cantidad, 
                            t.idProveedor,
                            p.nombre AS Proveedor,
                            :categoria AS Categoria
                       FROM $tablaDestino t
                       LEFT JOIN proveedores p ON t.idProveedor = p.idProveedor
                       WHERE t.$columnaId = :id";
                       
        $stmtObtener = $pdo->prepare($sqlObtener);
        $stmtObtener->execute([
            'id'        => $idItem,
            'categoria' => $categoria
        ]);
        
        $datosActualizados = $stmtObtener->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "estatus" => "exito", 
            "mensaje" => "Artículo actualizado correctamente.",
            "datos"   => $datosActualizados
        ]);
        
        exit;
    }
}
?>