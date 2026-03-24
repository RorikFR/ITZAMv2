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

<div class="dashboard-kpi-grid">
    
    <div class="tarjeta-kpi">
        <img class="kpi-icon" src="Assets/stethoscope-tool.png" alt="consultas">
        <h2 class="kpi-valor kpi-color-verde" id="kpi-consultas">...</h2>
        <p class="kpi-etiqueta">Consultas médicas</p>
    </div>

    <div class="tarjeta-kpi">
        <img class="kpi-icon" src="Assets/message.png" alt="asesorias">
        <h2 class="kpi-valor kpi-color-dorado" id="kpi-asesorias">...</h2>
        <p class="kpi-etiqueta">Asesorías</p>
    </div>

    <div class="tarjeta-kpi">
        <img class="kpi-icon" src="Assets/sick-boy.png" alt="pacientes">
        <h2 class="kpi-valor kpi-color-negro" id="kpi-pacientes">...</h2>
        <p class="kpi-etiqueta">Pacientes registrados</p>
    </div>

    <div class="tarjeta-kpi">
        <img class="kpi-icon" src="Assets/sick-person.png" alt="virus">
        <h2 class="kpi-valor kpi-color-rojo" id="kpi-influenza">...</h2>
        <p class="kpi-etiqueta">Casos de influenza</p>
    </div>

    <div class="tarjeta-kpi">
        <img class="kpi-icon" src="Assets/microscope.png" alt="laboratorio">
        <h2 class="kpi-valor kpi-color-verde" id="kpi-laboratorio">...</h2>
        <p class="kpi-etiqueta">Órdenes de laboratorio</p>
    </div>

    <div class="tarjeta-kpi">
        <img class="kpi-icon" src="Assets/hospital-building.png" alt="unidades">
        <h2 class="kpi-valor kpi-color-negro" id="kpi-unidades">...</h2>
        <p class="kpi-etiqueta">Unidades médicas</p>
    </div>

    <div class="tarjeta-kpi">
        <img class="kpi-icon" src="Assets/receipt.png" alt="recetas">
        <h2 class="kpi-valor kpi-color-dorado" id="kpi-recetas">...</h2>
        <p class="kpi-etiqueta">Recetas emitidas</p>
    </div>

    <div class="tarjeta-kpi">
        <img class="kpi-icon" src="Assets/vaccination.png" alt="vacunas">
        <h2 class="kpi-valor kpi-color-verde" id="kpi-vacunas">...</h2>
        <p class="kpi-etiqueta">Vacunas aplicadas</p>
    </div>

    <div class="tarjeta-kpi">
        <img class="kpi-icon" src="Assets/doctor.png" alt="personal">
        <h2 class="kpi-valor kpi-color-negro" id="kpi-personal">...</h2>
        <p class="kpi-etiqueta">Personal médico</p>
    </div>

    <div class="tarjeta-kpi">
        <img class="kpi-icon" src="Assets/ecg-monitor.png" alt="urgencias">
        <h2 class="kpi-valor kpi-color-rojo" id="kpi-urgencias">...</h2>
        <p class="kpi-etiqueta">Urgencias atendidas</p>
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
