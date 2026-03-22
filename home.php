<?php
date_default_timezone_set('America/Mexico_City');

require 'inactive.php';       // Control de inactividad y cache
require 'autorizacion.php';   // Roles de usuario

//RBAC
requerir_roles(['Médico', 'Enfermería', 'Administrativo']);

//Menu de navegación dinámico
require 'header.php';
?>

	<div class="title-box">
		<h1>Bienvenido: <?= htmlspecialchars($_SESSION['nombre_usuario']) ?></h1>
	</div>

		<div class="dashboard-container">
			<div>
				<img class="dashboard-icon" src="/Assets/stethoscope-tool.png" alt="consultas">
				<p class="card-subtitle" id="kpi-consultas" style="font-weight: bold; font-size: 1.5em; color: #007bff;">...</p>
				<p class="card-subtitle">Consultas médicas</p>
			</div>
			<div>
				<img class="dashboard-icon" src="/Assets/message.png" alt="asesorias">
				<p class="card-subtitle" id="kpi-asesorias" style="font-weight: bold; font-size: 1.5em; color: #17a2b8;">...</p>
				<p class="card-subtitle">Asesorías</p>
			</div>
			<div>
				<img class="dashboard-icon" src="/Assets/patient.png" alt="pacientes" onerror="this.src='/Assets/stethoscope-tool.png'">
				<p class="card-subtitle" id="kpi-pacientes" style="font-weight: bold; font-size: 1.5em; color: #343a40;">...</p>
				<p class="card-subtitle">Pacientes registrados</p>
			</div>
			<div>
				<img class="dashboard-icon" src="/Assets/coronavirus.png" alt="virus">
				<p class="card-subtitle" id="kpi-influenza" style="font-weight: bold; font-size: 1.5em; color: #dc3545;">...</p>
				<p class="card-subtitle">Casos de influenza</p>
			</div>
			<div>
				<img class="dashboard-icon" src="/Assets/microscope.png" alt="laboratorio">
				<p class="card-subtitle" id="kpi-laboratorio" style="font-weight: bold; font-size: 1.5em; color: #6f42c1;">...</p>
				<p class="card-subtitle">Órdenes de laboratorio</p>
			</div>
			<div>
				<img class="dashboard-icon" src="/Assets/hospital-building.png" alt="unidades">
				<p class="card-subtitle" id="kpi-unidades" style="font-weight: bold; font-size: 1.5em; color: #fd7e14;">...</p>
				<p class="card-subtitle">Unidades médicas</p>
			</div>
			<div>
				<img class="dashboard-icon" src="/Assets/prescription.png" alt="recetas" onerror="this.src='/Assets/report.png'">
				<p class="card-subtitle" id="kpi-recetas" style="font-weight: bold; font-size: 1.5em; color: #e83e8c;">...</p>
				<p class="card-subtitle">Recetas emitidas</p>
			</div>
			<div>
				<img class="dashboard-icon" src="/Assets/vaccination.png" alt="vacunas">
				<p class="card-subtitle" id="kpi-vacunas" style="font-weight: bold; font-size: 1.5em; color: #28a745;">...</p>
				<p class="card-subtitle">Vacunas aplicadas</p>
			</div>
			<div>
				<img class="dashboard-icon" src="/Assets/doctor.png" alt="personal" onerror="this.src='/Assets/stethoscope-tool.png'">
				<p class="card-subtitle" id="kpi-personal" style="font-weight: bold; font-size: 1.5em; color: #20c997;">...</p>
				<p class="card-subtitle">Personal médico</p>
			</div>
			<div>
				<img class="dashboard-icon" src="/Assets/report.png" alt="urgencias">
				<p class="card-subtitle" id="kpi-urgencias" style="font-weight: bold; font-size: 1.5em; color: #ffc107;">...</p>
				<p class="card-subtitle">Urgencias atendidas</p>
			</div>
		</div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                const response = await fetch('backend_home.php');
                const data = await response.json();

                if(data.error) {
                    console.error(data.error);
                    return;
                }

                //Asignar valores a cada tarjeta
                document.getElementById('kpi-consultas').textContent = data.consultas;
                document.getElementById('kpi-asesorias').textContent = data.asesorias;
                document.getElementById('kpi-pacientes').textContent = data.pacientes; 
                document.getElementById('kpi-influenza').textContent = data.influenza;
                document.getElementById('kpi-laboratorio').textContent = data.laboratorio;
                document.getElementById('kpi-unidades').textContent = data.unidades;
                document.getElementById('kpi-recetas').textContent = data.recetas;
                document.getElementById('kpi-vacunas').textContent = data.vacunas;
                document.getElementById('kpi-personal').textContent = data.personal; 
                document.getElementById('kpi-urgencias').textContent = data.urgencias;

            } catch (error) {
                console.error("Error al cargar los datos del dashboard:", error);
            }
        });
    </script>

    <script src="Scripts/js/timeout.js"></script>
				
		

		<footer class="bottombar">© 2026 ITZAM</footer>
	</body>
</html>
