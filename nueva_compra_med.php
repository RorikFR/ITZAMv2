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
            <button type="button" class="tab-btn" data-step="1">Datos del proveedor</button>
        </div>
        <span class="message">* Campos obligatorios</span>
        
        <form id="multiStepForm" action="" method="post" novalidate>
            <!-- Step 1 -->
            <div class="tab active" data-step="0" aria-hidden="false">
              <fieldset id="inventario-datos-generales-med">
                <label>*Nombre comercial:</label>
                <input class="form" type="text" name="nombre" required>

                <label>*Marca:</label>
                <input class="form" type="text" name="marca" required>

                <label>*Presentación:</label>
                <input class="form" type="text" name="presentacion" required placeholder="Ej. Tabletas, Jarabe, Inyección">
                
                <label>*Via de administración:</label>
                <input class="form" type="text" name="via_administracion" required>
              </fieldset>

              <fieldset id="inventario-datos-generales-med">
                <label>*Principio activo:</label>
                <input class="form" type="text" name="principio_activo" required>
                
                <label>*Concentración:</label>
                <input class="form" type="text" name="concentracion" required>
              </fieldset>
            </div>

            <!-- Step 2 -->
            <div class="tab" data-step="1" aria-hidden="true">
              <fieldset id="inventario-datos-proveedor-med">
                <label>*Proveedor:</label>
                <input class="form" type="text" name="proveedor" required>

                <label>*Cantidad:</label>
                <input class="form" type="number" name="cantidad" required>
                
                <label>*Lote:</label>
                <input class="form" type="text" name="lote" required>
                
                <label>*Fecha de caducidad:</label>
                <input class="form" type="date" id="fecha_caducidad" name="fecha_caducidad" required>
                
                <label>*Requiere refrigeración:</label>
                <select class="form" name="refrigeracion" required>
                  <option value="">Selecciona una opción</option>
                  <option value="sí">Sí</option>
                  <option value="no">No</option>
                </select>
              </fieldset>
            </div>
        </form>
        
        <div class="step-controls">
            <button class="multi-btn" type="button" id="prevBtn">Anterior</button>
            <button class="multi-btn" type="button" id="nextBtn">Siguiente</button>
            <button class="multi-btn-clear" type="button" id="clearBtn">Limpiar campos</button>             
            <button class="multi-btn-submit" type="submit" id="submitBtn">Registrar medicamento</button>
            <div class="step-indicator" id="stepIndicator">Paso 1 de 2</div>
        </div>
    </div>
    </div>

        <script>
            // Simple multi-step/tab form logic
            (function(){
                const form = document.getElementById('multiStepForm');
                const tabs = Array.from(document.querySelectorAll('.tab'));
                const tabButtons = Array.from(document.querySelectorAll('.tab-btn'));
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                const submitBtn = document.getElementById('submitBtn');
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
                    prevBtn.style.display = n===0 ? 'none' : '';
                    nextBtn.style.display = n===total-1 ? 'none' : '';
                    submitBtn.style.display = n===total-1 ? '' : 'none';
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
                        // optional: validate current before jumping
                        if (step > current && !validateStep(current)) return;
                        showStep(step);
                    });
                });

                // final submit: optionally revalidate last step and entire form
                form.addEventListener('submit', (e)=>{
                    if (!validateStep(current)) {
                        e.preventDefault();
                        return;
                    }
                    // full form validity check; if invalid, prevent submit and show first invalid field
                    if (!form.checkValidity()){
                        e.preventDefault();
                        const firstInvalid = form.querySelector(':invalid');
                        if (firstInvalid){
                            const stepEl = firstInvalid.closest('.tab');
                            const stepIndex = tabs.indexOf(stepEl);
                            if (stepIndex >= 0) showStep(stepIndex);
                            firstInvalid.focus();
                            firstInvalid.reportValidity();
                        }
                    }
                    // otherwise allow normal submit (or perform AJAX here)
                });

                // initialize
                showStep(0);
            })();
        </script>

        <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>
