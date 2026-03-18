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
        <title>Sistema ITZAM — Usuarios del sistema</title>
        <link rel="stylesheet" href="styles.css" />
    </head>
    <body>
        <header>
			<div class="topbar-container">
				<div>
					<img class ="logo"src="Assets/itzam_logoV2.png" alt="LOGO" />
				</div>
				
				<div class="topbar-header">Sistema web consulta de información clínica - ITZAM</div>
				
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
        <h3>Nuevo usuario del sistema</h3>
        </div>

<div class="grid-wrapper">
    <div class="formulario-background-normal">
        <span class="message">* Campos obligatorios</span>
        
        <form id="formUsuario" novalidate autocomplete="off">
            <div class="tab active" data-step="0" aria-hidden="false">
                <fieldset id="admin-usuario">
                    <legend>Datos de la Cuenta</legend>

                    <label for="idPersonal">*Asignar a Personal (Empleado):</label>
                    <select class="form" id="idPersonal" name="idPersonal" required>
                        <option value="" disabled selected>Cargando personal disponible...</option>
                    </select>

                    <label for="rol">*Rol en el sistema:</label>
                    <select class="form" id="rol" name="rol" required>
                        <option value="" disabled selected>Seleccione un rol...</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Médico">Médico</option>
                        <option value="Enfermería">Enfermería</option>
                        <option value="Recepción">Recepción</option>
                        <option value="Farmacia">Farmacia</option>
                    </select>

                    <label for="nom-usuario">*Nombre de usuario:</label>
                    <input class="form" type="text" id="nom-usuario" name="nombre_usuario" required placeholder="Ej. dr_ramos" maxlength="50" />

                    <label for="email-usuario">*Email:</label>
                    <input class="form" type="email" id="email-usuario" name="email" required placeholder="correo@clinica.com" maxlength="100" />

                    <label for="pass-usuario">*Contraseña:</label>
                    <input class="form" type="password" id="pass-usuario" name="contrasena" required placeholder="Mínimo 8 caracteres" minlength="8" />

                    <label for="pass-usuario-verify">*Verificar contraseña:</label>
                    <input class="form" type="password" id="pass-usuario-verify" required placeholder="Repita la contraseña" />
                </fieldset>
            </div>
        </form>
        
        <div class="step-controls">
            <button class="multi-btn-clear" type="button" id="clearBtn" style="background-color: #6c757d;">Limpiar campos</button>
            <button class="multi-btn-submit" type="submit" id="submitBtn" form="formUsuario">Crear usuario</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const form = document.getElementById('formUsuario');
    const pass1 = document.getElementById('pass-usuario');
    const pass2 = document.getElementById('pass-usuario-verify');
    const selectPersonal = document.getElementById('idPersonal');
    const inputEmail = document.getElementById('email-usuario'); // Añadimos el input del correo
    const submitBtn = document.getElementById('submitBtn');
    const clearBtn = document.getElementById('clearBtn');

    let listaPersonal = []; // Guardamos la lista a nivel global para accederla fácilmente

    // --- CARGAR PERSONAL DISPONIBLE ---
    try {
        const res = await fetch('backend_get_personal_sin_usuario.php');
        listaPersonal = await res.json();
        
        if (listaPersonal.length === 0) {
            selectPersonal.innerHTML = '<option value="" disabled selected>No hay personal sin usuario asignado</option>';
        } else {
            selectPersonal.innerHTML = '<option value="" disabled selected>Seleccione un empleado...</option>';
            listaPersonal.forEach(p => {
                selectPersonal.innerHTML += `<option value="${p.idPersonal}">${p.nombre_completo} (${p.puesto})</option>`;
            });
        }
    } catch (e) {
        selectPersonal.innerHTML = '<option value="" disabled selected>Error al cargar personal</option>';
    }

    // 🔥 MAGIA DE AUTOCOMPLETADO DE CORREO 🔥
    selectPersonal.addEventListener('change', (e) => {
        const idSeleccionado = e.target.value;
        const empleado = listaPersonal.find(p => p.idPersonal == idSeleccionado);
        
        if (empleado && empleado.email_heredado) {
            inputEmail.value = empleado.email_heredado;
            inputEmail.readOnly = true;
            inputEmail.style.backgroundColor = '#e9ecef';
            inputEmail.style.cursor = 'not-allowed';
            inputEmail.title = "Correo heredado del perfil del empleado.";
        } else {
            // Si el empleado no tiene correo, abrimos el candado
            inputEmail.value = '';
            inputEmail.readOnly = false;
            inputEmail.style.backgroundColor = '#ffffff';
            inputEmail.style.cursor = 'text';
            inputEmail.placeholder = "Este empleado no tiene correo. Escriba uno.";
            inputEmail.title = "";
        }
    });

    // --- VALIDACIÓN VISUAL DE CONTRASEÑAS ---
    function validarPasswords() {
        if (pass2.value === '') {
            pass2.style.borderColor = '';
            pass2.setCustomValidity('');
        } else if (pass1.value !== pass2.value) {
            pass2.style.borderColor = '#dc3545'; 
            pass2.setCustomValidity('Las contraseñas no coinciden');
        } else {
            pass2.style.borderColor = '#198754'; 
            pass2.setCustomValidity('');
        }
    }

    pass1.addEventListener('input', validarPasswords);
    pass2.addEventListener('input', validarPasswords);

    // --- LIMPIAR FORMULARIO ---
    clearBtn.addEventListener('click', () => {
        if(confirm("¿Deseas borrar todos los datos del formulario?")) {
            form.reset();
            pass2.style.borderColor = '';
            
            // Devolvemos el campo de correo a su estado normal
            inputEmail.readOnly = false;
            inputEmail.style.backgroundColor = '#ffffff';
            inputEmail.style.cursor = 'text';
            inputEmail.placeholder = "correo@clinica.com";
            inputEmail.title = "";
        }
    });

    // --- ENVÍO DEL FORMULARIO (JSON) ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (pass1.value !== pass2.value) {
            alert("⚠️ Las contraseñas no coinciden.");
            pass2.focus();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = "Creando usuario...";

        const formData = new FormData(form);
        const dataObj = Object.fromEntries(formData.entries());

        try {
            const res = await fetch('backend_nuevo_usuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataObj)
            });
            const data = await res.json();

            if (data.estatus === 'exito') {
                alert("✅ ¡Excelente!\n\n" + data.mensaje);
                form.reset();
                pass2.style.borderColor = '';
                location.reload(); 
            } else {
                alert("⚠️ Atención:\n\n" + data.mensaje);
            }
        } catch (error) {
            alert("❌ Error de conexión al servidor.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Crear usuario";
        }
    });
});
</script>
        
        <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>
