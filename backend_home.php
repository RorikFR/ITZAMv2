<?php
date_default_timezone_set('America/Mexico_City');

require 'inactive.php';       // Control de inactividad y cache
require 'autorizacion.php';   // Roles de usuario

// RBAC
requerir_roles_api(['Médico', 'Enfermería', 'Administrativo', 'Administrador']);

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
    
    // 3. Pacientes Registrados
    $totales['pacientes'] = $pdo->query("SELECT COUNT(idPaciente) FROM registro_paciente")->fetchColumn();

    // 4. Casos de Influenza
    $totales['influenza'] = $pdo->query("SELECT COUNT(idConsulta) FROM registro_consultas WHERE LOWER(diagnostico) LIKE '%influenza%'")->fetchColumn();

    // 5. Estudios de laboratorio
    $totales['laboratorio'] = $pdo->query("SELECT COUNT(idOrdenLab) FROM registro_laboratorio")->fetchColumn();

    // 6. Unidades Médicas
    $totales['unidades'] = $pdo->query("SELECT COUNT(idUnidad) FROM registro_unidad")->fetchColumn();

    // 7. Recetas Emitidas
    $totales['recetas'] = $pdo->query("SELECT COUNT(idReceta) FROM registro_receta")->fetchColumn();

    // 8. Vacunas aplicadas
    $totales['vacunas'] = $pdo->query("SELECT COUNT(idAsesoria) FROM registro_asesorias WHERE idMotivo = 2")->fetchColumn();

    // 9. Personal Médico Activo
    $totales['personal'] = $pdo->query("SELECT COUNT(idPersonal) FROM registro_personal")->fetchColumn();

    // 10. Urgencias atendidas
    $totales['urgencias'] = $pdo->query("SELECT COUNT(idConsulta) FROM registro_consultas WHERE idTipoConsulta = 2")->fetchColumn();

    echo json_encode($totales);

} catch (PDOException $e) {

    echo json_encode(['error' => 'Ocurrió un error al cargar las estadísticas']);
}
?>