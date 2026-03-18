<?php
session_start();
header('Content-Type: application/json');

// DEV ONLY - para ver errores si algo falla
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

try {
// 🛡️ REGLA: Traer personal que NO exista en usuarios_sistema y su correo
    $stmt = $pdo->query("
        SELECT 
            p.idPersonal, 
            CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS nombre_completo,
            cp.nombre_puesto AS puesto,
            COALESCE(p.email_inst, p.email_personal) AS email_heredado
        FROM registro_personal p
        LEFT JOIN usuarios_sistema u ON p.idPersonal = u.idPersonal
        LEFT JOIN cat_puestos cp ON p.idPuesto = cp.idPuesto
        WHERE u.idUsuario IS NULL
        ORDER BY p.nombre ASC
    ");
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($resultados);
    
} catch (PDOException $e) {
    // En lugar de devolver [] en silencio, devolvemos el error temporalmente para debugear
    echo json_encode([["idPersonal" => "", "nombre_completo" => "Error SQL", "puesto" => $e->getMessage()]]);
}
?>