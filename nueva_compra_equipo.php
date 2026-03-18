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
        <title>Sistema ITZAM — Registro de inventario</title>
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
      <h3>Formulario de registro de equipo médico en inventario</h3>
      </div>

    <div class="grid-wrapper">
    <div class="formulario-background-normal">
        <div class="tab-buttons" role="tablist" aria-label="Secciones del formulario">
            <button type="button" class="tab-btn active" data-step="0">Datos del equipo médico</button>
            <button type="button" class="tab-btn" data-step="1">Ingreso a inventario</button>
        </div>
        <span class="message">* Campos obligatorios</span>
        
        <form class="multiform" id="multiStepForm" novalidate autocomplete="off">
            <div class="tab active" data-step="0" aria-hidden="false">
                
                <fieldset style="background-color: #e8f4fd; border-radius: 8px; padding: 15px; margin-bottom: 15px; border: 1px solid #b6d4fe;">
                    <label for="idCatalogoEquipo" style="color: #084298;">🩺 <strong>Catálogo Oficial de Equipos:</strong></label>
                    <select class="form" id="idCatalogoEquipo" name="idCatalogoEquipo" style="border-color: #0d6efd;">
                        <option value="nuevo" selected>➕ Registrar un equipo nuevo en el catálogo</option>
                    </select>
                </fieldset>

                <fieldset id="inventario-datos-generales-equipo">
                    <label for="nombre">*Nombre del Equipo:</label>
                    <input class="form datos-equipo" type="text" id="nombre" name="nombre" maxlength="120" required placeholder="Ej. Monitor de Signos Vitales">
                    
                    <label for="marca">Marca:</label>
                    <input class="form datos-equipo" type="text" id="marca" name="marca" maxlength="65" placeholder="Ej. Philips, GE Healthcare">
                    
                    <label for="modelo">Modelo:</label>
                    <input class="form datos-equipo" type="text" id="modelo" name="modelo" maxlength="65" placeholder="Ej. IntelliVue X3">
                    
                    <label for="fabricante">Fabricante:</label>
                    <input class="form datos-equipo" type="text" id="fabricante" name="fabricante" maxlength="120" placeholder="Ej. Philips Medical Systems">
                </fieldset>
            </div>

            <div class="tab" data-step="1" aria-hidden="true">
                <fieldset id="inventario-datos-proveedor-equipo">
                    <label for="proveedor">*Proveedor:</label>
                    <select class="form" id="proveedor" name="idProveedor" required>
                        <option value="" disabled selected>Cargando opciones...</option>
                    </select>

                    <label for="fecha_compra">*Fecha de compra:</label>
                    <input class="form" type="date" id="fecha_compra" name="fecha_compra" required>  
                    
                    <label for="cantidad">*Cantidad a ingresar:</label>
                    <input class="form" type="number" id="cantidad" name="cantidad" min="1" required>
                </fieldset>
            </div>
        </form>
        
        <div class="step-controls">
            <button class="multi-btn" type="button" id="prevBtn">Anterior</button>
            <button class="multi-btn" type="button" id="nextBtn">Siguiente</button>
            <button class="multi-btn-clear" type="button" id="clearBtn" style="background-color: #6c757d;">Limpiar campos</button>             
            <button class="multi-btn-submit" type="submit" id="submitBtn" form="multiStepForm" style="display: none;">Registrar equipo</button>
            <div class="step-indicator" id="stepIndicator">Paso 1 de 2</div>
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
        const selectCatalogo = document.getElementById('idCatalogoEquipo');
        const inputsEquipo = Array.from(document.querySelectorAll('.datos-equipo'));
        
        let current = 0;
        const total = tabs.length;
        let catalogoEquipos = [];

        // Límite de fecha de compra (No puede ser en el futuro)
        const fechaCompra = document.getElementById('fecha_compra');
        fechaCompra.max = new Date().toISOString().split('T')[0];

        // --- CARGA DINÁMICA DE PROVEEDORES Y CATÁLOGO ---
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                // 1. Cargar Proveedores
                const resProv = await fetch('backend_catalogos.php?tabla=proveedores');
                const dataProv = await resProv.json();
                const selectProveedor = document.getElementById('proveedor');
                selectProveedor.innerHTML = '<option value="" disabled selected>Selecciona un proveedor</option>';
                dataProv.forEach(item => {
                    selectProveedor.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
                });

                // 2. Cargar Catálogo de Equipos
                const resCat = await fetch('backend_get_cat_equipo.php');
                catalogoEquipos = await resCat.json();
                catalogoEquipos.forEach(eq => {
                    const extraInfo = eq.modelo ? ` (Mod: ${eq.modelo})` : '';
                    selectCatalogo.innerHTML += `<option value="${eq.idCatalogoEquipo}">${eq.nombre} - ${eq.marca}${extraInfo}</option>`;
                });
            } catch (e) {
                console.error("Error cargando datos dinámicos:", e);
            }
        });

        // 🔥 LÓGICA DE AUTO-COMPLETADO CON EL CATÁLOGO
        selectCatalogo.addEventListener('change', (e) => {
            const valor = e.target.value;
            if (valor === 'nuevo') {
                // Desbloqueamos los campos
                inputsEquipo.forEach(input => {
                    input.value = '';
                    input.style.pointerEvents = 'auto';
                    input.style.backgroundColor = '';
                    input.readOnly = false;
                });
            } else {
                // Buscamos el equipo y bloqueamos
                const eq = catalogoEquipos.find(i => i.idCatalogoEquipo == valor);
                if (eq) {
                    document.getElementById('nombre').value = eq.nombre;
                    document.getElementById('marca').value = eq.marca || '';
                    document.getElementById('modelo').value = eq.modelo || '';
                    document.getElementById('fabricante').value = eq.fabricante || '';
                    
                    inputsEquipo.forEach(input => {
                        input.style.pointerEvents = 'none';
                        input.style.backgroundColor = '#e9ecef';
                        input.readOnly = true;
                    });
                }
            }
        });

        // --- LÓGICA DE TABS ---
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
                selectCatalogo.dispatchEvent(new Event('change')); // Desbloquea inputs
                showStep(0); 
            }
        });

        // --- INTEGRACIÓN AJAX ---
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!validateStep(current)) return;

            submitBtn.disabled = true;
            submitBtn.textContent = "Guardando...";

            const formData = new FormData(form);
            const dataObj = Object.fromEntries(formData.entries());

            try {
                const res = await fetch('backend_nuevo_equipo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataObj)
                });
                const data = await res.json();

                if(data.estatus === 'exito') {
                    alert("✅ ¡Excelente!\n\n" + data.mensaje);
                    form.reset();
                    selectCatalogo.dispatchEvent(new Event('change'));
                    showStep(0);
                } else {
                    alert("⚠️ Atención:\n\n" + data.mensaje);
                }
            } catch (error) {
                console.error(error);
                alert("❌ Error de conexión:\nNo se pudo contactar con el servidor. Intenta más tarde.");
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = "Registrar equipo";
            }
        });

        showStep(0);
    })();
</script>

        <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>
