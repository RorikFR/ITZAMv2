<?php
session_start();
header('Content-Type: application/json');
require 'db_conn.php';

try {
    $stmt = $pdo->query("
        SELECT idCatalogoInsumo, nombre, material, presentacion, piezas_unidad, tamano 
        FROM cat_insumos 
        ORDER BY nombre ASC
    ");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode([]);
}
?>