<?php
session_start();
header('Content-Type: application/json');
require 'db_conn.php';

try {
    // Traemos el diccionario de equipos ordenado alfabéticamente
    $stmt = $pdo->query("
        SELECT idCatalogoEquipo, nombre, marca, modelo, fabricante 
        FROM cat_equipo 
        ORDER BY nombre ASC
    ");
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (PDOException $e) {
    // Si hay error, devolvemos un arreglo vacío para no romper el frontend
    echo json_encode([]);
}
?>