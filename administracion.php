<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';       
require 'autorizacion.php';   

//RBAC
requerir_roles(['Administrador']);

require 'header.php';
?>
		<div class="title-box">
		<h1>Bienvenido, administrador.</h1>
		</div>

		<div class="dashboard-container">
			<div>
				<a href="nuevo_usuario_sistema.php">
				<img class="dashboard-icon" src="/Assets/stethoscope-tool.png" alt="consultas">
				<p class="card-subtitle">Nuevo usuario del sistema</p>
				</a>
			</div>
			<div>
				<a href="consulta_usuario_sistema.php">
				<img class="dashboard-icon" src="/Assets/message.png" alt="asesorias">
				<p class="card-subtitle">Gestión de usuarios del sistema</p>
				</a>
			</div>
		</div>

		<script src="Scripts/js/timeout.js"></script>
				
	
		

		<footer class="bottombar">© 2026 ITZAM</footer>
	</body>
</html>
