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
        <title>Sistema ITZAM — Registro de unidad médica</title>
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
        <h3>Formulario de registro de unidad médica</h3>
        </div>

<div class="grid-wrapper">
    <div class="formulario-background-normal">
        <div class="tab-buttons" role="tablist" aria-label="Secciones del formulario">
            <button type="button" class="tab-btn active" data-step="0">Datos generales</button>
            <button type="button" class="tab-btn" data-step="1">Dirección</button>
            <button type="button" class="tab-btn" data-step="2">Contacto</button>
        </div>
        <span class="message">* Campos obligatorios</span>

        <form class="multiform" id="multiStepForm" novalidate autocomplete="off">
            <div class="tab active" data-step="0" aria-hidden="false">
                <fieldset id="unidad-datos">
                    <label for="nombre_unidad">*Nombre de la unidad:</label>
                    <input class="form" type="text" id="nombre_unidad" name="nombre_unidad" placeholder="Ej. Hospital Central ITZAM" required />

                    <label for="afiliacion">*Afiliación:</label>
                    <select class="form" id="afiliacion" name="idAfiliacion" required>
                        <option value="" disabled selected>Cargando opciones...</option>
                    </select> 

                    <label for="categoria">*Categoría:</label>
                    <select class="form" id="categoria" name="idCategoria" required>
                        <option value="" disabled selected>Cargando opciones...</option>
                    </select>

                    <label for="prioritaria">*¿Es prioritaria?</label>
                    <select class="form" id="prioritaria" name="prioritaria" required>
                        <option value="" disabled selected>Selecciona una opción</option>
                        <option value="1">Sí</option> 
                        <option value="0">No</option> 
                    </select>
                </fieldset>
            </div>

            <div class="tab" data-step="1" aria-hidden="true">
                <fieldset id="unidad-datos-direccion">
                    <label for="calle">*Calle y número:</label>
                    <input class="form" type="text" id="calle" name="calle" placeholder="Ej. Av. Lázaro Cárdenas 123" required />

                    <label for="ubicacion">*Ubicación (CP - Colonia, Ciudad):</label>
                    <select class="form" id="ubicacion" name="idUbicacion" required>
                        <option value="" disabled selected>Cargando opciones...</option>
                    </select>
                </fieldset>
            </div>

            <div class="tab" data-step="2" aria-hidden="true">
                <fieldset id="unidad-datos-contacto">
                    <label for="telefono">*Teléfono de contacto:</label>
                    <input class="form" type="tel" id="telefono" name="telefono" placeholder="10 dígitos" required />  

                    <label for="email">*Correo electrónico:</label>
                    <input class="form" type="email" id="email" name="email" placeholder="contacto@unidad.com" required /> 
                </fieldset>
            </div>
        </form>

        <div class="step-controls">
            <button class="multi-btn" type="button" id="prevBtn">Anterior</button>
            <button class="multi-btn" type="button" id="nextBtn">Siguiente</button>
            <button class="multi-btn-clear" type="button" id="clearBtn" style="background-color: #6c757d;">Limpiar campos</button>
            <button class="multi-btn-submit" type="submit" id="submitBtn" form="multiStepForm" style="display: none;">Registrar unidad</button>
            <div class="step-indicator" id="stepIndicator">Paso 1 de 3</div>
        </div>
    </div>
</div>

<script>
    (function(){
        const form = document.getElementById('multiStepForm');
        const tabs = Array.from(document.querySelectorAll('.tab'));
        const tabButtons = Array.from(document.querySelectorAll('.tab-btn'));
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const clearBtn = document.getElementById('clearBtn');
        const submitBtn = document.getElementById('submitBtn');
        const stepIndicator = document.getElementById('stepIndicator');
        let current = 0;
        const total = tabs.length;

        // --- CARGA DINÁMICA DE CATÁLOGOS ---
        async function cargarCatalogos() {
            // 🔥 Añadimos nuestro catálogo de ubicación a la lista
            const catalogos = [
                { id: 'afiliacion', tabla: 'cat_afiliacion', msg: 'Selecciona una afiliación' },
                { id: 'categoria', tabla: 'cat_categoria', msg: 'Selecciona una categoría' },
                { id: 'ubicacion', tabla: 'catalogo_ubicacion', msg: 'Selecciona una ubicación' } 
            ];

            for (const cat of catalogos) {
                try {
                    const res = await fetch(`backend_catalogos.php?tabla=${cat.tabla}`);
                    const data = await res.json();
                    const select = document.getElementById(cat.id);
                    
                    select.innerHTML = `<option value="" disabled selected>${cat.msg}</option>`;
                    data.forEach(item => {
                        select.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
                    });
                } catch (e) {
                    console.error(`Error cargando el catálogo ${cat.tabla}:`, e);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', cargarCatalogos);

        // --- LÓGICA DEL FORMULARIO ---
        function showStep(n){
            tabs.forEach((t, i) => {
                const active = i === n;
                t.classList.toggle('active', active);
                t.setAttribute('aria-hidden', (!active).toString());
                tabButtons[i].classList.toggle('active', active);
            });
            prevBtn.style.display = n === 0 ? 'none' : 'inline-block';
            nextBtn.style.display = n === total - 1 ? 'none' : 'inline-block';
            submitBtn.style.display = n === total - 1 ? 'inline-block' : 'none';
            stepIndicator.textContent = `Paso ${n + 1} de ${total}`;
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

        nextBtn.addEventListener('click', () => {
            if (!validateStep(current)) return;
            showStep(Math.min(current + 1, total - 1));
        });

        prevBtn.addEventListener('click', () => showStep(Math.max(current - 1, 0)));

        clearBtn.addEventListener('click', () => {
            if(confirm("¿Deseas borrar los datos ingresados?")) {
                form.reset();
                showStep(0); 
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!validateStep(current)) return;

            submitBtn.disabled = true;
            submitBtn.textContent = "Procesando...";

            const formData = new FormData(form);
            const dataObj = Object.fromEntries(formData.entries());

            try {
                const res = await fetch('backend_nueva_unidad.php', {
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
                alert("Error de conexión al servidor.");
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = "Registrar unidad";
            }
        });

        showStep(0);
    })();
</script>

        <footer class="bottombar">© 2026 ITZAM</footer>

    </body>
</html>