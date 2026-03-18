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
            
            <form class="formulario" id="nueva_asesoria" novalidate>
                <input type="hidden" id="idPaciente" name="idPaciente">

                <fieldset id="datos-paciente">
					<legend>Datos del usuario/paciente</legend>
					
                    <label class="form" for="curp">*CURP:</label>
                    <div style="display: flex; gap: 10px;">
					    <input class="form" type="text" id="curp" name="curp" maxlength="18" style="text-transform: uppercase; width: 100%;" required />
                        <button type="button" id="btn-buscar-curp" class="btn" style="padding: 10px 20px;">Buscar</button>
                    </div>
                    <small id="curp-mensaje" style="color: #d9534f; display: none; margin-bottom: 10px;">Paciente no encontrado.</small>
			
					<label class="form" for="nombre">*Nombre:</label>
                    <input class="form" type="text" id="nombre" name="nombre" readonly style="background-color: #e9ecef;" required />

					<label class="form" for="apellido_paterno">*Apellido paterno:</label>
                	<input class="form" type="text" id="apellido_paterno" name="apellido_paterno" readonly style="background-color: #e9ecef;" required />

					<label class="form" for="apellido_materno">Apellido materno:</label>
                    <input class="form" type="text" id="apellido_materno" name="apellido_materno" readonly style="background-color: #e9ecef;" />
				</fieldset>
                        
				<fieldset id="datos-asesoria">
					<legend>Datos de la asesoría</legend>
					<label class="form" for="motivo">*Motivo de la asesoría:</label>
					<select class="form" id="motivo" name="motivo" required>
						<option value="" disabled selected>Cargando motivos...</option>
					</select>

					<label class="form" for="comentarios">*Comentarios:</label>
					<textarea class="form" id="comentario-asesoria" name="comentarios" rows="8" required></textarea>
				</fieldset>

                <button type="submit" id="submit" class="long-btn">Registrar Asesoría</button>
				<button type="button" id="clear" class="long-btn" style="background-color: #6c757d;">Limpiar campos</button>
            </form>
        </div>
	</div>

<script>
    const form = document.getElementById('nueva_asesoria');
    const inputCurp = document.getElementById('curp');
    const btnBuscarCurp = document.getElementById('btn-buscar-curp');
    const curpMensaje = document.getElementById('curp-mensaje');
    const selectMotivo = document.getElementById('motivo');

    // 1. CARGAR CATÁLOGO DE MOTIVOS AL ABRIR LA PÁGINA
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const res = await fetch('backend_catalogos.php?tabla=cat_motivos_asesoria');
            const datos = await res.json();
            selectMotivo.innerHTML = '<option value="" disabled selected>Selecciona un motivo</option>';
            if(!datos.error) {
                datos.forEach(item => {
                    const op = document.createElement('option');
                    op.value = item.id;
                    op.textContent = item.valor;
                    selectMotivo.appendChild(op);
                });
            }
        } catch (error) {
            console.error("Error cargando motivos");
            selectMotivo.innerHTML = '<option value="">Error al cargar</option>';
        }
    });

// 2. BUSCAR PACIENTE POR CURP
    btnBuscarCurp.addEventListener('click', async () => {
        const curp = inputCurp.value.trim().toUpperCase();
        
        // 🔥 NUEVA DEFENSA REGEX PARA EL CURP 🔥
        const curpRegex = /^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/;
        
        if (!curpRegex.test(curp)) {
            alert("⚠️ El formato del CURP es incorrecto. Verifica que tenga 18 caracteres válidos (ej. ABCD123456HDFXYZ09).");
            // Limpiamos la cajita para que lo intente de nuevo
            inputCurp.focus();
            return; 
        }

        btnBuscarCurp.textContent = "...";
        curpMensaje.style.display = 'none';

        try {
            const res = await fetch(`backend_nueva_asesoria.php?accion=buscar_paciente&curp=${curp}`);
            const data = await res.json();

            if(data.estatus === 'exito') {
                // Llenamos los campos de solo lectura
                document.getElementById('idPaciente').value = data.datos.idPaciente;
                document.getElementById('nombre').value = data.datos.nombre;
                document.getElementById('apellido_paterno').value = data.datos.apellido_p;
                document.getElementById('apellido_materno').value = data.datos.apellido_m || '';
                curpMensaje.style.display = 'none';
            } else {
                // Limpiamos si no existe
                document.getElementById('idPaciente').value = '';
                document.getElementById('nombre').value = '';
                document.getElementById('apellido_paterno').value = '';
                document.getElementById('apellido_materno').value = '';
                curpMensaje.textContent = data.mensaje;
                curpMensaje.style.display = 'block';
            }
        } catch (error) {
            alert("Error de conexión al buscar paciente.");
        } finally {
            btnBuscarCurp.textContent = "Buscar";
        }
    });

    // 3. ENVIAR FORMULARIO
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const idPaciente = document.getElementById('idPaciente').value;
        const idMotivo = selectMotivo.value;
        const comentarios = document.getElementById('comentario-asesoria').value.trim();

        if(!idPaciente) {
            alert("Debes buscar y seleccionar un paciente válido usando su CURP.");
            return;
        }

        if(!idMotivo || !comentarios) {
            alert("El motivo y los comentarios son obligatorios.");
            return;
        }

        const btnSubmit = document.getElementById('submit');
        btnSubmit.disabled = true;
        btnSubmit.textContent = "Guardando...";

        try {
            const res = await fetch('backend_nueva_asesoria.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    idPaciente: idPaciente,
                    idMotivo: idMotivo,
                    comentarios: comentarios
                })
            });
            const data = await res.json();

            if(data.estatus === 'exito') {
                alert("✅ " + data.mensaje);
                form.reset();
                document.getElementById('idPaciente').value = ''; // Limpiar el ID oculto
            } else {
                alert("⚠️ " + data.mensaje);
            }
        } catch (error) {
            alert("Error de conexión al guardar.");
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = "Registrar Asesoría";
        }
    });

    // 4. BOTÓN LIMPIAR
    document.getElementById('clear').addEventListener('click', () => {
        form.reset();
        document.getElementById('idPaciente').value = '';
        curpMensaje.style.display = 'none';
    });
</script>

        <footer class="bottombar">© 2026 ITZAM</footer>

    </body>
</html>
