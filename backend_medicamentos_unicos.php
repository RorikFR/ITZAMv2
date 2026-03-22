<?php

require 'seguridad_backend.php';
require 'db_conn.php';

// Obtenemos los medicamentos únicos registrados previamente (sin importar la unidad, para estandarizar la captura)
try {
    $stmt = $pdo->query("
        SELECT MIN(idMed) as idMed, nombre, marca, presentacion, via_adm, principio_activo, concentracion 
        FROM inventario_medicamentos 
        GROUP BY nombre, marca, presentacion, via_adm, principio_activo, concentracion
        ORDER BY nombre ASC
    ");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode([]);
}
?>