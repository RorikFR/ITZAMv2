<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_conn.php';

$totales = [
    'consultas' => 0, 'asesorias' => 0, 'pacientes' => 0, 'influenza' => 0,
    'laboratorio' => 0, 'unidades' => 0, 'recetas' => 0, 'vacunas' => 0,
    'personal' => 0, 'urgencias' => 0
];

try {
    // 1. Consultas médicas
    $totales['consultas'] = $pdo->query("SELECT COUNT(idConsulta) FROM registro_consultas")->fetchColumn();
    
    // 2. Asesorías
    $totales['asesorias'] = $pdo->query("SELECT COUNT(idAsesoria) FROM registro_asesorias")->fetchColumn();
    
    // 3. NUEVO: Pacientes Registrados
    $totales['pacientes'] = $pdo->query("SELECT COUNT(idPaciente) FROM registro_paciente")->fetchColumn();

    // 4. Casos de Influenza (Busca en el texto del diagnóstico)
    $totales['influenza'] = $pdo->query("SELECT COUNT(idConsulta) FROM registro_consultas WHERE LOWER(diagnostico) LIKE '%influenza%'")->fetchColumn();

    // 5. Estudios de laboratorio (Órdenes creadas)
    $totales['laboratorio'] = $pdo->query("SELECT COUNT(idOrdenLab) FROM registro_laboratorio")->fetchColumn();

    // 6. Unidades Médicas
    $totales['unidades'] = $pdo->query("SELECT COUNT(idUnidad) FROM registro_unidad")->fetchColumn();

    // 7. NUEVO: Recetas Emitidas
    $totales['recetas'] = $pdo->query("SELECT COUNT(idReceta) FROM registro_receta")->fetchColumn();

    // 8. Vacunas aplicadas (Asesorías con el motivo 'Vacunación')
    $totales['vacunas'] = $pdo->query("
        SELECT COUNT(a.idAsesoria) FROM registro_asesorias a 
        INNER JOIN cat_motivos_asesoria m ON a.idMotivo = m.idMotivo 
        WHERE m.nombre_motivo LIKE '%Vacunación%'
    ")->fetchColumn();

    // 9. NUEVO: Personal Médico Activo
    $totales['personal'] = $pdo->query("SELECT COUNT(idPersonal) FROM registro_personal")->fetchColumn();

    // 10. Urgencias atendidas
    $totales['urgencias'] = $pdo->query("
        SELECT COUNT(c.idConsulta) FROM registro_consultas c 
        INNER JOIN cat_tipo_consulta tc ON c.idTipoConsulta = tc.idTipoConsulta 
        WHERE tc.nombre_tipo = 'Urgencia'
    ")->fetchColumn();

    echo json_encode($totales);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
}
?>