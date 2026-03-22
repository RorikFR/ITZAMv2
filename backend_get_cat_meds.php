<?php

require 'seguridad_backend.php';
require 'db_conn.php';

try {
    $stmt = $pdo->query("
        SELECT idCatalogoMed, nombre, marca, presentacion, via_adm, principio_activo, concentracion, refrigerado 
        FROM cat_medicamentos 
        ORDER BY nombre ASC
    ");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode([]);
}
?>