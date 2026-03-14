<?php
session_start();

// Opcional pero recomendado: El escudo de seguridad
if (!isset($_SESSION['idUsuario'])) {
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>Sistema ITZAM — Registrar asesoría</title>
        <link rel="stylesheet" href="styles.css" />
    </head>
    <body>
    	<header>
        <div class="topbar-container">
          <div>
            <img class ="logo"src="Assets/itzam_logoV2.png" alt="LOGO" />
          </div>
          
          <div class="topbar-header">Sistema web de consulta de información clínica - ITZAM</div>
          
		<div class="user-menu">
  			<div class="user-menu">
			    <img id="header-user-photo" class="user-photo user-icon" src="<?php echo isset($_SESSION['foto_perfil']) && $_SESSION['foto_perfil'] ? $_SESSION['foto_perfil'] : 'Assets/think.jpg'; ?>" onclick="toggleMenu()">
			</div>
  			
			<div class="dropdown-menu" id="userDropdown">
				<p class="user-menu-title" style="font-weight: bold;"><?= htmlspecialchars($_SESSION['nombre_usuario']) ?></p>
				<hr></hr>
    			<a class="dropdown-item" href="administracion.php">Administración</a>
    			<a class="dropdown-item" href="catalogos.php">Catálogos</a>
    			<a class="dropdown-item" href="configuracion_cuenta.php">Configuración</a>
    			<a class="dropdown-item" href="logout.php">Cerrar sesión</a>
  			</div>
		</div>
	</header>
	
	<nav>	
		<ul>
  			<li><a href="home.php" class="active">Inicio</a></li>

			<!-- Dropdown menu for Asesorías -->
			<li class="dropdown">
   			<a href="javascript:void(0)" class="dropbtn">Asesorías</a>
			<div class="dropdown-content">
				<a href="mis_asesorias.php">Mis asesorías</a>
      			<a href="nueva_asesoria.php">Registrar asesoría</a>
    		</div>	
  			</li>

			<!-- Dropdown menu for Consultas médicas -->
			<li class="dropdown">
   			<a href="javascript:void(0)" class="dropbtn">Consultas médicas</a>
			<div class="dropdown-content">
				<a href="buscar_consulta.php">Buscar consulta</a>
      			<a href="nueva_consulta.php">Registrar consulta</a>
    		</div>
  			</li>

			<li><a href="estadisticas.php">Estadísticas</a></li>

			<!-- Dropdown menu for Estudios -->
			<li class="dropdown">
   			<a href="javascript:void(0)" class="dropbtn">Laboratorios</a>
			<div class="dropdown-content">
				<a href="consulta_orden_laboratorio.php">Buscar orden de laboratorio</a>
      			<a href="nueva_orden_laboratorio.php">Crear orden de laboratorio</a>
    		</div>
  			</li>

			<!-- Dropdown menu for Inventario -->
			<li class="dropdown">
   			<a href="javascript:void(0)" class="dropbtn">Inventario</a>
			<div class="dropdown-content">
				<a href="consulta_inventario.php">Buscar en inventario</a>
				<a href="nueva_compra_med.php">Registrar compra de medicamentos</a>
				<a href="nueva_compra_insumo.php">Registrar compra de insumos</a>
				<a href="nueva_compra_equipo.php">Registrar compra de equipo médico</a>
    		</div>
  			</li>

			<!-- Dropdown menu for Pacientes -->
			<li class="dropdown">
   				<a href="javascript:void(0)" class="dropbtn">Pacientes</a>
				<div class="dropdown-content">
					<a href="consulta_expediente.php">Consultar historia clínica</a>
					<a href="consulta_paciente.php">Consultar paciente</a>
					<a href="nuevo_paciente.php">Registrar paciente</a>
				</div>
  			</li>

			<!-- Dropdown menu for Personal de salud -->
			<li class="dropdown">
   			<a href="javascript:void(0)" class="dropbtn">Personal de salud</a>
			<div class="dropdown-content">
				<a href="consulta_personal.php">Consultar personal</a>
      			<a href="nuevo_personal.php">Registrar personal</a>
    		</div>
  			</li>

			<!-- Dropdown menu for Recetas -->
			<li class="dropdown">
   			<a href="javascript:void(0)" class="dropbtn">Recetas</a>
			<div class="dropdown-content">
				<a href="consulta_receta.php">Consultar receta</a>
      			<a href="nueva_receta.php">Registrar receta</a>
    		</div>
  			</li>

			<!-- Dropdown menu for Unidades médicas -->
			<li class="dropdown">
   			<a href="javascript:void(0)" class="dropbtn">Unidades médicas</a>
			<div class="dropdown-content">
				<a href="consulta_unidad.php">Consultar unidad médica</a>
      			<a href="nueva_unidad.php">Registrar unidad médica</a>
    		</div>
  			</li>

		</ul>
	</nav>

        <div class="title-box">
		<h3>Formulario de registro de asesoría</h3>
		</div>

    <div class="background">
		<div class="formulario-background-normal">
			<span class="message">* Campos obligatorios</span>
            <form class="formulario" id="nueva_asesoria" action="" method="post" novalidate>
                <fieldset id="datos-paciente">
					<legend>Datos del usuario/paciente</legend>
					
					<label class="form" for="curp">*CURP:</label>
					<input class="form" type="text" id="curp" name="curp" maxlength="18" required />
			
					<label class="form" for="nombre">*Nombre:</label>
                    <input class="form" type="text" id="nombre" name="nombre" required />

					<label class="form" for="apellido_paterno">*Apellido paterno:</label>
                	<input class="form" type="text" id="apellido_paterno" name="apellido_paterno" required />

					<label class="form" for="apellido_materno">*Apellido materno:</label>
                    <input class="form" type="text" id="apellido_materno" name="apellido_materno" required />
				</fieldset>
                        
				<fieldset id="datos-asesoria">
					<legend>Datos de la asesoría</legend>
					<label class="form" for="motivo">*Motivo de la asesoría:</label>
					<select class="form" id="motivo" name="motivo" required>
							<option value="" disabled selected>Selecciona un motivo</option>
							<option value="orientacion">Orientación general</option>
							<option value="sintomas">Síntomas específicos</option>
							<option value="prevencion">Prevención de enfermedades</option>
							<option value="otro">Otro</option>
					</select>

					<label class="form" for="comentarios">*Comentarios:</label>
					<textarea class="form" id="comentario-asesoria" name="comentarios" rows="8"></textarea>
				</fieldset>

                <button type="submit" id="submit" class="long-btn">Registrar</button>
				<button type="button" id="clear" class="long-btn">Limpiar campos</button>
            </form>
        </div>
	</div>

<script>
  const form = document.getElementById('nueva_asesoria');

  form.addEventListener('input', (e) => {
    const target = e.target;
    const output = document.getElementById(`out-${target.id}`);

    if (output) {
      if (target.tagName === 'SELECT') {
        // Obtenemos el texto de la opción seleccionada, no el valor
        output.textContent = target.options[target.selectedIndex].text;
      } else {
        // Para inputs normales usamos el valor directo
        output.textContent = target.value;
      }
    }
  });
</script>
        <footer class="bottombar">© 2026 ITZAM</footer>

    </body>
</html>
