<?php
// Validaciones de seguridad e inactividad
require 'seguridad_backend.php'; 
require 'autorizacion.php';     

//RBAC
requerir_roles_api(['Administrativo', 'Médico']);

require 'db_conn.php';

// JSON anidado
$respuesta = [
    "unidades"   => ["labels" => [], "valores" => []],
    "categorias" => ["labels" => [], "valores" => []],
    "incidencia" => ["labels" => [], "valores" => []],
    "personal"   => ["labels" => [], "valores" => []],
    "edades"     => ["labels" => [], "valores" => []],
    "inventario" => ["labels" => [], "valores" => []],
    "insumos"    => ["labels" => [], "valores" => []],
    "equipo"     => ["labels" => [], "valores" => []]
];

try {
    // 1. Consultas por unidad médica
    $stmt1 = $pdo->query("
        SELECT u.nombre AS nombre_unidad, COUNT(c.idConsulta) as total
        FROM registro_consultas c
        INNER JOIN registro_unidad u ON c.idUnidad = u.idUnidad
        GROUP BY u.idUnidad
    ");
    while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
        $respuesta["unidades"]["labels"][] = $row['nombre_unidad'];
        $respuesta["unidades"]["valores"][] = (int)$row['total'];
    }

    // 2. Consultas por tipo de atención
    $stmt2 = $pdo->query("
        SELECT tc.nombre_tipo AS tipo_consulta, COUNT(c.idConsulta) as total
        FROM registro_consultas c
        INNER JOIN cat_tipo_consulta tc ON c.idTipoConsulta = tc.idTipoConsulta
        GROUP BY tc.nombre_tipo
    ");
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $respuesta["categorias"]["labels"][] = $row['tipo_consulta'];
        $respuesta["categorias"]["valores"][] = (int)$row['total'];
    }

    // 3. Top 5 Incidencias (diagnósticos)
    $stmt3 = $pdo->query("
        SELECT diagnostico, COUNT(idConsulta) as total
        FROM registro_consultas
        GROUP BY diagnostico
        ORDER BY total DESC
        LIMIT 5
    ");
    while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
        $respuesta["incidencia"]["labels"][] = $row['diagnostico'];
        $respuesta["incidencia"]["valores"][] = (int)$row['total'];
    }

    // 4. Productividad por personal médico
    $stmt4 = $pdo->query("
        SELECT 
            CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS nombre_completo, 
            COUNT(c.idConsulta) AS total
        FROM registro_consultas c
        INNER JOIN registro_personal p ON c.idPersonal = p.idPersonal
        GROUP BY p.idPersonal
        ORDER BY total DESC
    ");
    while ($row = $stmt4->fetch(PDO::FETCH_ASSOC)) {
        $respuesta["personal"]["labels"][] = $row['nombre_completo'];
        $respuesta["personal"]["valores"][] = (int)$row['total'];
    }

    // 5. Distribución de edades
    $stmt5 = $pdo->query("
        SELECT 
            CASE 
                WHEN ROUND(DATEDIFF(c.fecha_consulta, p.fecha_nac) / 365.25) < 18 THEN 'Menores de 18'
                WHEN ROUND(DATEDIFF(c.fecha_consulta, p.fecha_nac) / 365.25) BETWEEN 18 AND 59 THEN 'Adultos (18-59)'
                ELSE 'Adultos Mayores (60+)'
            END AS rango_edad,
            COUNT(c.idConsulta) AS total
        FROM registro_consultas c
        INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente
        GROUP BY rango_edad
        ORDER BY 
            MIN(ROUND(DATEDIFF(c.fecha_consulta, p.fecha_nac) / 365.25)) ASC
    ");
    while ($row = $stmt5->fetch(PDO::FETCH_ASSOC)) {
        $respuesta["edades"]["labels"][] = $row['rango_edad'];
        $respuesta["edades"]["valores"][] = (int)$row['total'];
    }

    // 6. Top 10 medicamentos con mayor stock
    $stmt6 = $pdo->query("
        SELECT 
            CONCAT(cat.nombre, ' (', u.nombre, ')') AS etiqueta_inventario, 
            SUM(m.cantidad) AS volumen
        FROM inventario_medicamentos m
        INNER JOIN cat_medicamentos cat ON m.idCatalogoMed = cat.idCatalogoMed 
        INNER JOIN registro_unidad u ON m.idUnidad = u.idUnidad
        GROUP BY cat.nombre, u.nombre
        ORDER BY volumen DESC
        LIMIT 10
    ");
    while ($row = $stmt6->fetch(PDO::FETCH_ASSOC)) {
        $respuesta["inventario"]["labels"][] = $row['etiqueta_inventario'];
        $respuesta["inventario"]["valores"][] = (int)$row['volumen']; 
    }
    
    // 7. Top 10 insumos con mayor stock
    $stmt7 = $pdo->query("
        SELECT 
            CONCAT(ci.nombre, ' (', u.nombre, ')') AS etiqueta_insumo, 
            SUM(i.cantidad) AS volumen
        FROM inventario_insumos i
        INNER JOIN cat_insumos ci ON i.idCatalogoInsumo = ci.idCatalogoInsumo
        INNER JOIN registro_unidad u ON i.idUnidad = u.idUnidad
        GROUP BY ci.nombre, u.nombre
        ORDER BY volumen DESC
        LIMIT 10
    ");
    while ($row = $stmt7->fetch(PDO::FETCH_ASSOC)) {
        $respuesta["insumos"]["labels"][] = $row['etiqueta_insumo'];
        $respuesta["insumos"]["valores"][] = (int)$row['volumen']; 
    }

    // 8. Top 10 equipo médico con mayor stock
    $stmt8 = $pdo->query("
        SELECT 
            CONCAT(ce.nombre, ' (', u.nombre, ')') AS etiqueta_equipo, 
            SUM(e.cantidad) AS volumen
        FROM inventario_equipo e
        INNER JOIN cat_equipo ce ON e.idCatalogoEquipo = ce.idCatalogoEquipo
        INNER JOIN registro_unidad u ON e.idUnidad = u.idUnidad
        GROUP BY ce.nombre, u.nombre
        ORDER BY volumen DESC
        LIMIT 10
    ");
    while ($row = $stmt8->fetch(PDO::FETCH_ASSOC)) {
        $respuesta["equipo"]["labels"][] = $row['etiqueta_equipo'];
        $respuesta["equipo"]["valores"][] = (int)$row['volumen']; 
    }

    // Enviar datos a frontend
    echo json_encode($respuesta);

} catch (PDOException $e) {
    http_response_code(500); 
    echo json_encode(["estatus" => "error", "mensaje" => "Error de BD: " . $e->getMessage()]);
}
?>