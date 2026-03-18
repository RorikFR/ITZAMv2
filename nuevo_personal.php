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
        <title>Sistema ITZAM — Registro de personal en unidad médica</title>
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
        <h3>Formulario de registro de personal</h3>
        </div>

<div class="grid-wrapper">
    <div class="formulario-background-normal">
        <div class="tab-buttons" role="tablist" aria-label="Secciones del formulario">
            <button type="button" class="tab-btn active" data-step="0">Identificación</button>
            <button type="button" class="tab-btn" data-step="1">Contacto</button>
        </div>
        <span class="message">* Campos obligatorios</span>
        
        <form class="multiform" id="multiStepForm" novalidate autocomplete="off">
            <div class="tab active" data-step="0" aria-hidden="false">
                <fieldset id="personal-datos-generales">
                    <legend>Datos generales</legend>
                    <label for="nombre">*Nombre:</label>
                    <input class="form" type="text" id="nombre" name="nombre" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$" title="Solo se permiten letras" required />

                    <label for="apellido_paterno">*Apellido paterno:</label>
                    <input class="form" type="text" id="apellido_paterno" name="apellido_paterno" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$" title="Solo se permiten letras" required />

                    <label for="apellido_materno">*Apellido materno:</label>
                    <input class="form" type="text" id="apellido_materno" name="apellido_materno" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$" title="Solo se permiten letras" required />

                    <label for="curp">*CURP:</label>
                    <input class="form" type="text" id="curp" name="curp" maxlength="18" style="text-transform: uppercase;" pattern="^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$" title="Ingrese un CURP válido de 18 caracteres" required />
                </fieldset>

                <fieldset id="personal-datos-cedula">
                    <legend>Datos profesionales</legend>
                    
                    <label for="unidad">*Unidad de Adscripción:</label>
                    <select class="form" id="unidad" name="unidad" required>
                        <option value="" disabled selected>Cargando unidades...</option>
                    </select>

                    <label for="puesto">*Puesto:</label>
                    <select class="form" id="puesto" name="puesto" required>
                        <option value="" disabled selected>Cargando puestos...</option>
                    </select>

                    <label for="cedula">*Cédula profesional:</label>
                    <input class="form" type="text" id="cedula" name="cedula" pattern="^\d{7,8}$" title="La cédula debe contener estrictamente 7 u 8 números" required />

                    <label for="especialidad">Especialidad (Opcional):</label>
                    <select class="form" id="especialidad" name="especialidad">
                        <option value="" selected>Cargando especialidades...</option>
                    </select>

                    <label for="cedula_especialidad">Cédula de especialidad (Opcional):</label>
                    <input class="form" type="text" id="cedula_especialidad" name="cedula_especialidad" pattern="^\d{7,8}$" title="La cédula debe contener estrictamente 7 u 8 números" />
                </fieldset>
            </div>

            <div class="tab" data-step="1" aria-hidden="true">
                <fieldset id="personal-datos-contacto">
                    <legend>Información de Contacto</legend>
                    <label for="email_institucional">*Email institucional: </label>
                    <input class="form" type="email" id="email_institucional" name="email_institucional" required />

                    <label for="email_personal">*Email personal:</label>
                    <input class="form" type="email" id="email_personal" name="email_personal" required />

                    <label for="telefono">*Teléfono (10 dígitos):</label>
                    <input class="form" type="tel" id="telefono" name="telefono" pattern="^\d{10}$" title="El teléfono debe tener exactamente 10 números, sin espacios ni guiones" required />
                </fieldset>
            </div>
        </form>
        
        <div class="step-controls">
            <button class="multi-btn" type="button" id="prevBtn">Anterior</button>
            <button class="multi-btn" type="button" id="nextBtn">Siguiente</button>
            <button class="multi-btn-clear" type="button" id="clearBtn" style="background-color: #6c757d;">Limpiar campos</button>
            <button class="multi-btn-submit" type="submit" id="submitBtn" style="display: none;">Registrar personal</button>
            <div class="step-indicator" id="stepIndicator">Paso 1 de 2</div>
        </div>
    </div>
</div>

<footer class="bottombar">© 2026 ITZAM</footer>

<script>
// 1. CARGA DINÁMICA DE CATÁLOGOS AL ABRIR LA PÁGINA
document.addEventListener('DOMContentLoaded', async () => {
    
    // 🔥 NUEVO: Cargar Catálogo de Unidades 🔥
    try {
        const resUnidad = await fetch('backend_catalogos.php?tabla=registro_unidad');
        const datosUnidad = await resUnidad.json();
        const selectUnidad = document.getElementById('unidad');
        selectUnidad.innerHTML = '<option value="" disabled selected>Seleccione una unidad</option>';
        if(!datosUnidad.error) {
            datosUnidad.forEach(item => {
                selectUnidad.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
            });
        }
    } catch (e) { console.error("Error cargando unidades"); }

    // Cargar Catálogo de Puestos
    try {
        const resPuestos = await fetch('backend_catalogos.php?tabla=cat_puestos');
        const datosPuestos = await resPuestos.json();
        const selectPuesto = document.getElementById('puesto');
        selectPuesto.innerHTML = '<option value="" disabled selected>Seleccione un puesto</option>';
        if(!datosPuestos.error) {
            datosPuestos.forEach(item => {
                selectPuesto.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
            });
        }
    } catch (e) { console.error("Error cargando puestos"); }

    // Cargar Catálogo de Especialidades
    try {
        const resEsp = await fetch('backend_catalogos.php?tabla=cat_especialidades');
        const datosEsp = await resEsp.json();
        const selectEspecialidad = document.getElementById('especialidad');
        selectEspecialidad.innerHTML = '<option value="" selected>Ninguna / Médico General</option>';
        if(!datosEsp.error) {
            datosEsp.forEach(item => {
                selectEspecialidad.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
            });
        }
    } catch (e) { console.error("Error cargando especialidades"); }
});

// 2. LÓGICA DEL FORMULARIO Y VALIDACIONES
(function(){
    const form = document.getElementById('multiStepForm');
    const tabs = Array.from(document.querySelectorAll('.tab'));
    const tabButtons = Array.from(document.querySelectorAll('.tab-btn'));
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const clearBtn = document.getElementById('clearBtn');
    const submitBtn = document.getElementById('submitBtn');
    const stepIndicator = document.getElementById('stepIndicator');
    
    // Forzar mayúsculas en el CURP en tiempo real
    const inputCurp = document.getElementById('curp');
    inputCurp.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

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

    // Lógica del botón de Limpiar
    clearBtn.addEventListener('click', () => {
        if(confirm("¿Estás seguro de que deseas borrar los datos ingresados?")) {
            form.reset();
            showStep(0); 
        }
    });

    // 3. ENVÍO POR AJAX AL BACKEND
    submitBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        if (!validateStep(current)) return;
        if (!form.checkValidity()){
            form.reportValidity();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = "Registrando...";

        const formData = new FormData(form);
        const dataObj = Object.fromEntries(formData.entries());

        try {
            const res = await fetch('backend_nuevo_personal.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataObj)
            });
            const data = await res.json();

            if(data.estatus === 'exito') {
                alert("✅ " + data.mensaje);
                form.reset();
                showStep(0);
            } else {
                alert("⚠️ " + data.mensaje);
            }
        } catch (error) {
            alert("Error de conexión al guardar.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Registrar personal";
        }
    });

    showStep(0);
})();
</script>