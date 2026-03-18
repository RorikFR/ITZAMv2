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
        <title>Sistema ITZAM — Orden de laboratorio</title>
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
        <h3>Formulario de creación de orden de laboratorio</h3>
        </div>

<div class="grid-wrapper">
    <div class="formulario-background-normal">
        <div class="tab-buttons" role="tablist" aria-label="Secciones del formulario">
            <button type="button" class="tab-btn active" data-step="0">Datos generales</button>
            <button type="button" class="tab-btn" data-step="1">Datos clínicos</button>
        </div>
        <span class="message">* Campos obligatorios</span>
        
        <form class="multi-form" id="multiStepForm" novalidate>
            <input type="hidden" id="idPaciente" name="idPaciente">

            <div class="tab active" data-step="0" aria-hidden="false">
                <fieldset id="orden-laboratorio">
                    <legend>Identificación y Estudios</legend>

                    <label for="curp">*CURP del paciente:</label>
                    <div style="display: flex; gap: 10px; margin-bottom: 5px;">
                        <input class="form" type="text" id="curp" name="curp" maxlength="18" style="text-transform: uppercase; width: 100%; margin-bottom: 0;" required />
                        <button type="button" id="btn-buscar-curp" class="btn" style="padding: 10px 20px;">Buscar</button>
                    </div>
                    <small id="curp-mensaje" style="color: #d9534f; display: none; margin-bottom: 15px;"></small>

                    <label for="nombre_paciente">Paciente Seleccionado:</label>
                    <input class="form" type="text" id="nombre_paciente" readonly style="background-color: #e9ecef; margin-bottom: 15px;" placeholder="Busque un CURP válido arriba" required />

                    <label for="estudios-solicitados">*Estudios solicitados:</label>
                    <select class="form" id="estudios-solicitados" name="estudios_solicitados" required>
                        <option value="" disabled selected>Cargando catálogo...</option>
                    </select>
                    
                    <label for="prioridad">*Prioridad:</label>
                    <select class="form" id="prioridad" name="prioridad" required>
                        <option value="" disabled selected>Cargando catálogo...</option>
                    </select>

                    <label for="laboratorio-observ">Observaciones para laboratorio:</label>
                    <textarea class="form" id="laboratorio-observ" name="observaciones" rows="4"></textarea> 
                </fieldset>
            </div>

            <div class="tab" data-step="1" aria-hidden="true">
                <fieldset id="orden-laboratorio-datos-medicos">
                    <legend>Justificación Médica</legend>

                    <label for="medico">*Médico solicitante:</label>
                    <select class="form" id="medico" name="medico" required>
                        <option value="" disabled selected>Cargando médicos...</option>
                    </select>

                    <label for="laboratorio-diag-pre">*Diagnóstico preliminar:</label>
                    <textarea id="laboratorio-diag-pre" name="diagnostico_preliminar" rows="6" required></textarea>
                </fieldset>
            </div>
        </form>
        
        <div class="step-controls">
            <button class="multi-btn" type="button" id="prevBtn">Anterior</button>
            <button class="multi-btn" type="button" id="nextBtn">Siguiente</button>
            
            <button class="multi-btn-clear" type="button" id="clearBtn" style="background-color: #6c757d;">Limpiar campos</button>
            
            <button class="multi-btn-submit" type="submit" id="submitBtn" style="display: none;">Crear orden</button>
            <div class="step-indicator" id="stepIndicator">Paso 1 de 2</div>
        </div>
    </div>
</div>

<script>
// 1. Cargar Catálogos Dinámicamente (Incluyendo los Médicos)
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Cargar Estudios
        const resEstudios = await fetch('backend_catalogos.php?tabla=cat_estudios_laboratorio');
        const datosEstudios = await resEstudios.json();
        const selectEstudios = document.getElementById('estudios-solicitados');
        selectEstudios.innerHTML = '<option value="" disabled selected>Selecciona un estudio</option>';
        if(!datosEstudios.error) datosEstudios.forEach(item => selectEstudios.innerHTML += `<option value="${item.id}">${item.valor}</option>`);

        // Cargar Prioridades
        const resPrioridad = await fetch('backend_catalogos.php?tabla=cat_prioridad_lab');
        const datosPrioridad = await resPrioridad.json();
        const selectPrioridad = document.getElementById('prioridad');
        selectPrioridad.innerHTML = '<option value="" disabled selected>Selecciona una prioridad</option>';
        if(!datosPrioridad.error) datosPrioridad.forEach(item => selectPrioridad.innerHTML += `<option value="${item.id}">${item.valor}</option>`);

        // 🔥 Cargar Lista de Médicos 🔥
        const resMedicos = await fetch('backend_nueva_orden_lab.php?accion=obtener_medicos');
        const datosMedicos = await resMedicos.json();
        const selectMedico = document.getElementById('medico');
        selectMedico.innerHTML = '<option value="" disabled selected>Seleccione al médico solicitante</option>';
        if(datosMedicos.estatus === 'exito') {
            datosMedicos.datos.forEach(med => {
                // El value es la cédula, el texto es el nombre completo
                selectMedico.innerHTML += `<option value="${med.cedula}">Dr(a). ${med.nombre} ${med.apellido_p} ${med.apellido_m || ''} (Cédula: ${med.cedula})</option>`;
            });
        }
    } catch (e) { console.error("Error cargando catálogos"); }
});

// 2. Buscar Paciente por CURP
const btnBuscar = document.getElementById('btn-buscar-curp');
const inputCurp = document.getElementById('curp');
const msgCurp = document.getElementById('curp-mensaje');

btnBuscar.addEventListener('click', async () => {
    const curp = inputCurp.value.trim().toUpperCase();
    inputCurp.value = curp;
    
    const curpRegex = /^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/;
    if (!curpRegex.test(curp)) {
        msgCurp.style.color = '#d9534f';
        msgCurp.textContent = "El formato del CURP es incorrecto.";
        msgCurp.style.display = 'block';
        return; 
    }

    btnBuscar.textContent = "...";
    try {
        const res = await fetch(`backend_nueva_orden_lab.php?accion=buscar_paciente&curp=${curp}`);
        const data = await res.json();
        if(data.estatus === 'exito') {
            document.getElementById('idPaciente').value = data.datos.idPaciente;
            document.getElementById('nombre_paciente').value = `${data.datos.nombre} ${data.datos.apellido_p} ${data.datos.apellido_m || ''}`;
            msgCurp.style.display = 'none';
        } else {
            document.getElementById('idPaciente').value = '';
            document.getElementById('nombre_paciente').value = '';
            msgCurp.style.color = '#d9534f';
            msgCurp.textContent = data.mensaje;
            msgCurp.style.display = 'block';
        }
    } catch (e) {
        alert("Error al buscar paciente.");
    } finally {
        btnBuscar.textContent = "Buscar";
    }
});

// 3. Lógica del Multi-Step y Envío AJAX
(function(){
    const form = document.getElementById('multiStepForm');
    const tabs = Array.from(document.querySelectorAll('.tab'));
    const tabButtons = Array.from(document.querySelectorAll('.tab-btn'));
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const stepIndicator = document.getElementById('stepIndicator');
    const clearBtn = document.getElementById('clearBtn'); // Agrega esta línea
    let current = 0;
    const total = tabs.length;

    function showStep(n){
        tabs.forEach((t,i)=>{
            const active = i===n;
            t.classList.toggle('active', active);
            t.setAttribute('aria-hidden', (!active).toString());
            tabButtons[i].classList.toggle('active', active);
        });
        prevBtn.style.display = n===0 ? 'none' : 'inline-block';
        nextBtn.style.display = n===total-1 ? 'none' : 'inline-block';
        submitBtn.style.display = n===total-1 ? 'inline-block' : 'none';
        stepIndicator.textContent = `Paso ${n+1} de ${total}`;
        current = n;
    }

    function validateStep(n){
        const inputs = Array.from(tabs[n].querySelectorAll('input, select, textarea'));
        for (const el of inputs){
            if (!el.checkValidity()) {
                el.reportValidity();
                return false;
            }
        }
        if(n === 0 && !document.getElementById('idPaciente').value) {
            alert("Debe buscar y validar el CURP del paciente antes de continuar.");
            return false;
        }
        return true;
    }

    nextBtn.addEventListener('click', ()=>{
        if (!validateStep(current)) return;
        showStep(Math.min(current+1, total-1));
    });

    prevBtn.addEventListener('click', ()=> showStep(Math.max(current-1, 0)));

    tabButtons.forEach(btn=>{
        btn.addEventListener('click', ()=> {
            const step = Number(btn.getAttribute('data-step'));
            if (step > current && !validateStep(current)) return;
            showStep(step);
        });
    });

    // Lógica para Limpiar todo el formulario
    clearBtn.addEventListener('click', () => {
        if(confirm("¿Estás seguro de que deseas borrar todos los datos ingresados?")) {
            form.reset(); // Borra todos los inputs
            document.getElementById('idPaciente').value = ''; // Borra el ID oculto
            document.getElementById('curp-mensaje').style.display = 'none'; // Oculta errores
            showStep(0); // Te regresa al Triage / Paso 1 automáticamente
        }
    });

    submitBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        if (!validateStep(current)) return;
        if (!form.checkValidity()){
            form.reportValidity();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = "Creando orden...";

        const formData = new FormData(form);
        const dataObj = Object.fromEntries(formData.entries());

        try {
            const res = await fetch('backend_nueva_orden_lab.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataObj)
            });
            const data = await res.json();

            if(data.estatus === 'exito') {
                alert("✅ " + data.mensaje);
                form.reset();
                document.getElementById('idPaciente').value = '';
                showStep(0); 
            } else {
                alert("⚠️ " + data.mensaje);
            }
        } catch (error) {
            alert("Error de conexión al guardar.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Crear orden";
        }
    });

    showStep(0);
})();
</script>

        <footer class="bottombar">© 2026 ITZAM</footer>

    </body>
</html>
