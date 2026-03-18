<?php
date_default_timezone_set('America/Mexico_City');
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
        <title>Sistema ITZAM — Registrar consulta</title>
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
        <h3>Formulario de registro de consulta médica</h3>
        </div>

<div class="grid-wrapper">
    <div class="formulario-background-normal">
        <div class="tab-buttons" role="tablist" aria-label="Secciones del formulario">
            <button type="button" class="tab-btn active" data-step="0">Triage</button>
            <button type="button" class="tab-btn" data-step="1">Síntomas</button>
            <button type="button" class="tab-btn" data-step="2">Diagnóstico</button>
            <button type="button" class="tab-btn" data-step="3">Tratamiento</button>
        </div>
        <span class="message">* Campos obligatorios</span>
        
        <form class="multi-form" id="multiStepForm" action="" method="post" novalidate>
            <input type="hidden" id="idPaciente" name="idPaciente">

            <div class="tab active" data-step="0" aria-hidden="false">
                <fieldset id="datos">
                    <legend>Datos generales</legend>
                    
                    <label for="tipo_consulta">*Tipo de consulta:</label>
                    <select class="form" id="tipo_consulta" name="tipo_consulta" required>
                        <option value="" disabled selected>Cargando opciones...</option>
                    </select>

                    <label for="curp">*CURP del Paciente:</label>
                    <div style="display: flex; gap: 10px; margin-bottom: 5px;">
                        <input class="form" type="text" id="curp" name="curp" maxlength="18" style="text-transform: uppercase; width: 100%; margin-bottom: 0;" required />
                        <button type="button" id="btn-buscar-curp" class="btn" style="padding: 10px 20px;">Buscar</button>
                    </div>
                    <small id="curp-mensaje" style="color: #d9534f; display: none; margin-bottom: 15px;"></small>

                    <label for="nombre_paciente">Paciente Seleccionado:</label>
                    <input class="form" type="text" id="nombre_paciente" readonly style="background-color: #e9ecef; margin-bottom: 15px;" placeholder="Busque un CURP válido arriba" required />

                    <label for="peso">*Peso (kg):</label>
                    <input class="form" type="number" step="0.1" id="peso" name="peso" min="2" max="350" title="Ingrese un peso válido en kg" required />

                    <label for="talla">*Talla (m):</label>
                    <input class="form" type="number" step="0.01" id="talla" name="talla" min="0.40" max="2.50" title="Ingrese la talla en metros (ej. 1.75)" placeholder="Ej. 1.75" required />
                </fieldset>
                        
                <fieldset id="signos">
                    <legend>Signos vitales</legend>
                    <label for="temperatura">*Temperatura (°C):</label>
                    <input class="form" type="number" step="0.1" id="temperatura" name="temperatura" min="33" max="43" title="Temperatura válida entre 33°C y 43°C" required />

                    <label for="frecuencia">*Frecuencia cardiaca (lpm):</label>
                    <input class="form" type="number" id="frecuencia" name="frecuencia" min="30" max="250" required />

                    <label for="saturacion">*Saturación de oxígeno (%):</label>
                    <input class="form" type="number" id="saturacion" name="saturacion" min="40" max="100" required />

                    <label for="presion_arterial">*Presión arterial (mmHg):</label>
                    <input class="form" type="text" id="presion_arterial" name="presion_arterial" pattern="^[1-9]\d{1,2}\/[1-9]\d{1,2}$" title="El formato debe ser Sistólica/Diastólica usando números válidos (ej. 120/80)" placeholder="Ej. 120/80" required />
                </fieldset>
            </div>

            <div class="tab" data-step="1" aria-hidden="true">
                <fieldset id="sintomas_fieldset">
                    <legend>Sintomas</legend>
                    <label for="inicio_sintomas">*Inicio de síntomas:</label>
                    <input class="form" type="date" id="inicio_sintomas" name="inicio_sintomas" max="<?= date('Y-m-d') ?>" required />

                    <label for="sintomas">*Síntomas (Subjetivos):</label>
                    <textarea id="consulta-sintomas" name="sintomas" rows="5" required></textarea>
                </fieldset>

                <fieldset id="antecedentes_fieldset">
                    <legend>Antecedentes Relevantes</legend>
                    <label for="alergias">*Alergias:</label>
                    <input class="form" type="text" id="alergias" name="alergias" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d1\s\.,;]+$" title="No se permiten números. Ingrese 'Ninguna' si no aplica." placeholder="Escriba 'Ninguna' si no aplica" required />

                    <label for="antecedentes">*Antecedentes familiares:</label>
                    <input class="form" type="text" id="antecedentes" name="antecedentes" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d1\s\.,;]+$" title="Solo se permiten letras." placeholder="Diabetes, Hipertensión, etc." required />

                    <label for="habitos">*Hábitos (Tabaquismo, Alcoholismo):</label>
                    <input class="form" type="text" id="habitos" name="habitos" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d1\s\.,;]+$" title="Solo se permiten letras." required />
                </fieldset>
            </div>

            <div class="tab" data-step="2" aria-hidden="true">
                <label for="diagnostico">*Diagnóstico Médico (CIE-10 o Libre):</label>
                <textarea id="consulta-diagnostico" name="diagnostico" rows="10" required></textarea>
            </div>

            <div class="tab" data-step="3" aria-hidden="true">
                <label for="tratamiento">*Plan y Tratamiento a seguir:</label>
                <textarea id="consulta-tratamiento" name="tratamiento" rows="10" required></textarea>
            </div>
        </form>
        
        <div class="step-controls">
            <button class="multi-btn" type="button" id="prevBtn">Anterior</button>
            <button class="multi-btn" type="button" id="nextBtn">Siguiente</button>
            <button class="multi-btn-clear" type="button" id="clearBtn" style="background-color: #6c757d;">Limpiar campos</button>
            
            <button class="multi-btn-submit" type="submit" id="submitBtn" style="display: none;">Registrar consulta</button>
            <div class="step-indicator" id="stepIndicator">Paso 1 de 4</div>
        </div>
    </div>

<footer class="bottombar">© 2026 ITZAM</footer>

<script>
// Lógica de carga de catálogos y búsqueda
document.addEventListener('DOMContentLoaded', async () => {
    // 1. Cargar Tipos de Consulta
    try {
        const res = await fetch('backend_catalogos.php?tabla=cat_tipo_consulta');
        const datos = await res.json();
        const selectTipo = document.getElementById('tipo_consulta');
        selectTipo.innerHTML = '<option value="" disabled selected>Selecciona una opción</option>';
        if(!datos.error) {
            datos.forEach(item => {
                selectTipo.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
            });
        }
    } catch (e) { console.error("Error cargando tipos de consulta"); }
});

// 2. Buscar Paciente
const btnBuscar = document.getElementById('btn-buscar-curp');
const inputCurp = document.getElementById('curp');
const msgCurp = document.getElementById('curp-mensaje');

btnBuscar.addEventListener('click', async () => {
    const curp = inputCurp.value.trim().toUpperCase();
    inputCurp.value = curp;
    
    const curpRegex = /^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/;
    if (!curpRegex.test(curp)) {
        msgCurp.style.color = '#d9534f';
        msgCurp.textContent = "El formato del CURP es incorrecto. Verifique los 18 caracteres.";
        msgCurp.style.display = 'block';
        return; 
    }

    btnBuscar.textContent = "...";
    try {
        const res = await fetch(`backend_nueva_consulta.php?accion=buscar_paciente&curp=${curp}`);
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

// 3. Lógica del Multi-Step Original (Adaptada)
(function(){
    const form = document.getElementById('multiStepForm');
    const tabs = Array.from(document.querySelectorAll('.tab'));
    const tabButtons = Array.from(document.querySelectorAll('.tab-btn'));
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const clearBtn = document.getElementById('clearBtn'); // <-- Variable del botón limpiar
    const stepIndicator = document.getElementById('stepIndicator');
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
        // Validación extra: Obligar a buscar al paciente en el paso 0
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

    // --- LÓGICA DEL BOTÓN LIMPIAR ---
    clearBtn.addEventListener('click', () => {
        if(confirm("¿Deseas borrar todos los datos de esta consulta?")) {
            form.reset(); 
            // Limpiamos los campos bloqueados y ocultos del buscador
            document.getElementById('idPaciente').value = '';
            document.getElementById('nombre_paciente').value = '';
            document.getElementById('curp-mensaje').style.display = 'none';
            // Regresamos a la pestaña de Triage
            showStep(0); 
        }
    });

    tabButtons.forEach(btn=>{
        btn.addEventListener('click', ()=> {
            const step = Number(btn.getAttribute('data-step'));
            if (step > current && !validateStep(current)) return;
            showStep(step);
        });
    });

    // 4. Envío Final AJAX
    submitBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        if (!validateStep(current)) return;
        if (!form.checkValidity()){
            form.reportValidity();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = "Guardando...";

        // Recolectar datos
        const formData = new FormData(form);
        const dataObj = Object.fromEntries(formData.entries());

        try {
            const res = await fetch('backend_nueva_consulta.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataObj)
            });
            const data = await res.json();

            if(data.estatus === 'exito') {
                // Usamos \n\n para dar un salto de línea y que el mensaje se vea limpio
                let quiereReceta = confirm(`✅ ${data.mensaje}\n\n¿Desea emitir una receta médica derivada de esta consulta?`);
                
                if (quiereReceta) {
                    // Si el doctor elige "Aceptar", lo llevamos directo a la receta con el ID inyectado
                    window.location.href = `nueva_receta.php?idConsulta=${data.idConsulta}`;
                } else {
                    // Si el doctor elige "Cancelar", limpiamos TODO para el siguiente paciente
                    form.reset();
                    document.getElementById('idPaciente').value = '';
                    document.getElementById('nombre_paciente').value = '';
                    showStep(0); // Volvemos a la pestaña inicial
                }
            } else {
                alert("⚠️ " + data.mensaje);
            }
        } catch (error) {
            alert("Error de conexión al guardar la consulta.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Guardar Consulta";
        }
    });

    showStep(0);
})();
</script>