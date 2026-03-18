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
      <h3>Formulario de registro de medicamentos en inventario</h3>
      </div>

<div class="grid-wrapper">
    <div class="formulario-background-normal">
        <div class="tab-buttons" role="tablist" aria-label="Secciones del formulario">
            <button type="button" class="tab-btn active" data-step="0">Datos del medicamento</button>
            <button type="button" class="tab-btn" data-step="1">Ingreso a inventario</button>
        </div>
        <span class="message">* Campos obligatorios</span>
        
        <form class="multiform" id="multiStepForm" novalidate autocomplete="off">
            <div class="tab active" data-step="0" aria-hidden="false">
                
                <fieldset style="background-color: #e8f4fd; border-radius: 8px; padding: 15px; margin-bottom: 15px; border: 1px solid #b6d4fe;">
                    <label for="idCatalogoMed" style="color: #084298;">⚕️ <strong>Catálogo Oficial ITZAM:</strong></label>
                    <select class="form" id="idCatalogoMed" name="idCatalogoMed" style="border-color: #0d6efd;">
                        <option value="nuevo" selected>➕ Registrar un medicamento nuevo en el catálogo</option>
                    </select>
                </fieldset>

                <fieldset id="inventario-datos-generales-med">
                    <label for="nombre">*Nombre comercial:</label>
                    <input class="form datos-med" type="text" id="nombre" name="nombre" maxlength="120" required>

                    <label for="marca">*Marca / Laboratorio:</label>
                    <input class="form datos-med" type="text" id="marca" name="marca" maxlength="120" required>

                    <label for="presentacion">*Presentación:</label>
                    <input class="form datos-med" type="text" id="presentacion" name="presentacion" maxlength="45" required placeholder="Ej. Caja con 30 Tabletas">
                    
                    <label for="via_adm">*Vía de administración:</label>
                    <input class="form datos-med" type="text" id="via_adm" name="via_adm" maxlength="45" required placeholder="Ej. Oral">
                </fieldset>

                <fieldset id="inventario-datos-generales-med-2">
                    <label for="principio_activo">*Principio activo:</label>
                    <input class="form datos-med" type="text" id="principio_activo" name="principio_activo" maxlength="120" required placeholder="Ej. Paracetamol">
                    
                    <label for="concentracion">*Concentración:</label>
                    <input class="form datos-med" type="text" id="concentracion" name="concentracion" maxlength="120" required placeholder="Ej. 500mg">

                    <label for="refrigerado">*¿Requiere refrigeración?</label>
                    <select class="form datos-med" id="refrigerado" name="refrigerado" required>
                        <option value="" disabled selected>Selecciona una opción</option>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </fieldset>
            </div>

            <div class="tab" data-step="1" aria-hidden="true">
                <fieldset id="inventario-datos-proveedor-med">
                    <label for="proveedor">*Proveedor:</label>
                    <select class="form" id="proveedor" name="idProveedor" required>
                        <option value="" disabled selected>Cargando opciones...</option>
                    </select>

                    <label for="cantidad">*Cantidad a ingresar (Cajas/Unidades):</label>
                    <input class="form" type="number" id="cantidad" name="cantidad" min="1" max="10000" required>
                    
                    <label for="lote">*Lote (Código):</label>
                    <input class="form" type="text" id="lote" name="lote" maxlength="45" required>
                    
                    <label for="fecha_caducidad">*Fecha de caducidad:</label>
                    <input class="form" type="date" id="fecha_caducidad" name="fecha_caducidad" required>
                </fieldset>
            </div>
        </form>
        
        <div class="step-controls">
            <button class="multi-btn" type="button" id="prevBtn">Anterior</button>
            <button class="multi-btn" type="button" id="nextBtn">Siguiente</button>
            <button class="multi-btn-clear" type="button" id="clearBtn" style="background-color: #6c757d;">Limpiar campos</button>             
            <button class="multi-btn-submit" type="submit" id="submitBtn" form="multiStepForm" style="display: none;">Ingresar a inventario</button>
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
        const selectCatalogo = document.getElementById('idCatalogoMed');
        const inputsMed = Array.from(document.querySelectorAll('.datos-med'));
        let current = 0;
        const total = tabs.length;
        let catalogoMedicamentos = [];

        // 🔥 BLOQUEO DE FECHA DE CADUCIDAD
        const fechaInput = document.getElementById('fecha_caducidad');
        const manana = new Date();
        manana.setDate(manana.getDate() + 1);
        fechaInput.min = manana.toISOString().split('T')[0];

        // --- CARGA DINÁMICA ---
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                // 1. Cargar Proveedores
                const resProv = await fetch('backend_catalogos.php?tabla=proveedores');
                const dataProv = await resProv.json();
                const selectProveedor = document.getElementById('proveedor');
                dataProv.forEach(item => {
                    selectProveedor.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
                });

                // 2. Cargar Catálogo de Medicamentos
                const resMed = await fetch('backend_get_cat_meds.php');
                catalogoMedicamentos = await resMed.json();
                catalogoMedicamentos.forEach(med => {
                    selectCatalogo.innerHTML += `<option value="${med.idCatalogoMed}">${med.nombre} - ${med.concentracion} (${med.marca})</option>`;
                });
            } catch (e) {
                console.error("Error de carga:", e);
            }
        });

        // 🔥 LÓGICA DE AUTO-COMPLETADO CON EL CATÁLOGO
        selectCatalogo.addEventListener('change', (e) => {
            const valor = e.target.value;
            if (valor === 'nuevo') {
                // Si es nuevo, vaciamos y desbloqueamos los campos
                inputsMed.forEach(input => {
                    input.value = '';
                    input.style.pointerEvents = 'auto'; // Permitir clics
                    input.style.backgroundColor = '';
                    input.readOnly = false;
                });
            } else {
                // Buscamos el medicamento en la memoria por su ID
                const med = catalogoMedicamentos.find(m => m.idCatalogoMed == valor);
                if (med) {
                    document.getElementById('nombre').value = med.nombre;
                    document.getElementById('marca').value = med.marca;
                    document.getElementById('presentacion').value = med.presentacion;
                    document.getElementById('via_adm').value = med.via_adm;
                    document.getElementById('principio_activo').value = med.principio_activo;
                    document.getElementById('concentracion').value = med.concentracion;
                    document.getElementById('refrigerado').value = med.refrigerado;
                    
                    // Bloqueamos los campos visualmente para que el usuario sepa que no puede alterar el catálogo desde aquí
                    inputsMed.forEach(input => {
                        input.style.pointerEvents = 'none'; // Desactivar clics
                        input.style.backgroundColor = '#e9ecef'; // Gris
                        input.readOnly = true;
                    });
                }
            }
        });

        // --- LÓGICA DE NAVEGACIÓN (TABS) ---
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
                const res = await fetch('backend_nuevo_medicamento.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataObj)
                });
                const data = await res.json();

                if(data.estatus === 'exito') {
                    alert("✅ " + data.mensaje);
                    form.reset();
                    selectCatalogo.dispatchEvent(new Event('change'));
                    showStep(0);
                } else {
                    alert("⚠️ " + data.mensaje);
                }
            } catch (error) {
                alert("Error de conexión con el servidor.");
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = "Ingresar a inventario";
            }
        });

        showStep(0);
    })();
</script>

        <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>
