<?php
require 'seguridad_backend.php';
require 'autorizacion.php';
//RBAC
requerir_roles_api(['Administrador']);
require 'db_conn.php';

//Validar si ya existe un administrador en sistema
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios_sistema WHERE rol = 'Administrador'");
        $adminCount = $stmtAdmin->fetchColumn();
        
        // Si el conteo es 1 o mayor, ya existe un admin
        $existe = ($adminCount >= 1) ? true : false;
        
        echo json_encode(["existe_admin" => $existe]);
    } catch (PDOException $e) {
        // Si hay error, asumimos por seguridad que sí existe
        echo json_encode(["existe_admin" => true]);
    }
} else {
    echo json_encode(["existe_admin" => true]);
}
?>