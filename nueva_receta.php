<?php
session_start();

// Opcional pero recomendado: El escudo de seguridad
if (!isset($_SESSION['idUsuario'])) {
    header("Location: index.php");
    exit;
}

// Obligamos al servidor a usar la hora de Ciudad de México
date_default_timezone_set('America/Mexico_City'); 

?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>Sistema ITZAM — Registrar receta</title>
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
		<h3>Formulario de registro de receta médica</h3>
		</div>

<div class="grid-wrapper">
    <div class="formulario-background-normal">
        <div class="tab-buttons" role="tablist" aria-label="Secciones del formulario">
            <button type="button" class="tab-btn active" data-step="0">Datos de la Consulta</button>
            <button type="button" class="tab-btn" data-step="1">Prescripción Médica</button>
        </div>
        <span class="message">* Campos obligatorios</span>
        
        <form class="multiform" id="multiStepForm" novalidate autocomplete="off">
            
            <div class="tab active" data-step="0" aria-hidden="false">
                <fieldset id="datos-receta">
                    <legend>Referencia Clínica</legend>
                    
                    <label for="idConsulta">*ID de la Consulta de origen:</label>
                    <input class="form" type="number" id="idConsulta" name="idConsulta" placeholder="Ej. 1045" required />

                    <label for="prox_consulta">Próxima consulta (Opcional):</label>
                    <input class="form" type="date" id="prox_consulta" name="prox_consulta" min="<?= date('Y-m-d') ?>" />

                    <label for="indicaciones_generales">*Indicaciones Generales y Cuidados:</label>
                    <textarea class="form" id="indicaciones_generales" name="indicaciones_generales" rows="3" placeholder="Dieta blanda, reposo absoluto, etc." required></textarea>
                </fieldset>
            </div>

            <div class="tab" data-step="1" aria-hidden="true">
                <fieldset id="rx-receta">
                    <legend>Lista de Medicamentos a Surtir</legend>
                    
                    <div style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 15px; flex-wrap: wrap;">
                        <div style="flex: 3; min-width: 200px;">
                            <label for="med-select" style="font-size: 0.9em;">Fármaco en Inventario:</label>
                            <select id="med-select" class="form" style="margin-bottom: 0;">
                                <option value="" disabled selected>Cargando inventario...</option>
                            </select>
                        </div>
                        <div style="flex: 3; min-width: 150px;">
                            <label for="med-dosis" style="font-size: 0.9em;">Dosis (Ej. 1 c/8hrs):</label>
                            <input type="text" id="med-dosis" class="form" style="margin-bottom: 0;" />
                        </div>
                        <div style="flex: 1; min-width: 70px;">
                            <label for="med-cantidad" style="font-size: 0.9em;">Cajas:</label>
                            <input type="number" id="med-cantidad" class="form" min="1" value="1" style="margin-bottom: 0;" />
                        </div>
                        <div>
                            <button type="button" id="btn-add-med" class="btn" style="padding: 10px 15px; margin-bottom: 0; background-color: #17a2b8; font-weight: bold;" title="Agregar a la lista">+</button>
                        </div>
                    </div>

                    <div style="max-height: 180px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; background: #fff;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.9em; text-align: left;">
                            <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 1;">
                                <tr>
                                    <th style="padding: 8px; border-bottom: 2px solid #dee2e6;">Medicamento</th>
                                    <th style="padding: 8px; border-bottom: 2px solid #dee2e6;">Dosis</th>
                                    <th style="padding: 8px; border-bottom: 2px solid #dee2e6; text-align: center;">Cant.</th>
                                    <th style="padding: 8px; border-bottom: 2px solid #dee2e6; text-align: center;">Quitar</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-medicamentos">
                                <tr id="fila-vacia">
                                    <td colspan="4" style="padding: 15px; text-align: center; color: #6c757d; font-style: italic;">Sin medicamentos en la receta. Agregue uno arriba.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </fieldset>
            </div>
        </form>
        
        <div class="step-controls">
            <button class="multi-btn" type="button" id="prevBtn">Anterior</button>
            <button class="multi-btn" type="button" id="nextBtn">Siguiente</button>
            <button class="multi-btn-clear" type="button" id="clearBtn" style="background-color: #6c757d;">Limpiar campos</button>
            <button class="multi-btn-submit" type="submit" id="submitBtn" style="display: none;">Emitir Receta</button>
            <div class="step-indicator" id="stepIndicator">Paso 1 de 2</div>
        </div>
    </div>
</div>

<script>
// VARIABLES GLOBALES
let catalogoMedicamentos = []; 
let prescripcionVirtual = []; 

document.addEventListener('DOMContentLoaded', async () => {
    
    // 1. ATRAPAMOS EL ID DE LA URL
    const parametrosURL = new URLSearchParams(window.location.search);
    const idConsultaAtrapado = parametrosURL.get('idConsulta');

    if (idConsultaAtrapado) {
        const inputConsulta = document.getElementById('idConsulta');
        inputConsulta.value = idConsultaAtrapado;
        inputConsulta.setAttribute('readonly', true);
        inputConsulta.style.backgroundColor = '#e9ecef';
        inputConsulta.style.cursor = 'not-allowed';
        inputConsulta.title = "ID vinculado automáticamente";
    }

    // 2. CARGA DE INVENTARIO (CON DEPURADOR SILENCIOSO)
    const selectMed = document.getElementById('med-select');
    try {
        const res = await fetch('backend_nueva_receta.php?accion=obtener_medicamentos');
        const dataText = await res.text(); // Leemos como texto primero para atrapar errores de PHP
        
        try {
            const data = JSON.parse(dataText);
            
            if(data.estatus === 'exito') {
                catalogoMedicamentos = data.datos;
                
                if (catalogoMedicamentos.length === 0) {
                    selectMed.innerHTML = '<option value="" disabled selected>No hay medicamentos en stock en esta clínica</option>';
                } else {
                    selectMed.innerHTML = '<option value="" disabled selected>Seleccione un medicamento...</option>';
                    catalogoMedicamentos.forEach(med => {
                        // Limpieza de datos visuales (Por si no tienen concentración)
                        const concentracion = med.concentracion ? ` ${med.concentracion}` : '';
                        const presentacion = med.presentacion ? ` (${med.presentacion})` : '';
                        
                        selectMed.innerHTML += `<option value="${med.idMed}">${med.nombre}${concentracion}${presentacion} - Disp: ${med.cantidad}</option>`;
                    });
                }
            } else {
                console.error("Error del backend:", data.mensaje);
                selectMed.innerHTML = '<option value="" disabled selected>Error del servidor</option>';
                alert("Atención: " + data.mensaje);
            }
        } catch (parseError) {
            console.error("Error crítico: El backend devolvió texto en lugar de JSON. Respuesta cruda:", dataText);
            selectMed.innerHTML = '<option value="" disabled selected>Error de conexión</option>';
        }
    } catch (e) {
        console.error("Error de Red al intentar contactar al servidor:", e);
        selectMed.innerHTML = '<option value="" disabled selected>Fallo de red</option>';
    }
});

// LÓGICA DEL ÁREA DE PREPARACIÓN
const btnAddMed = document.getElementById('btn-add-med');
const inputSelect = document.getElementById('med-select');
const inputDosis = document.getElementById('med-dosis');
const inputCantidad = document.getElementById('med-cantidad');
const tablaCuerpo = document.getElementById('tabla-medicamentos');

function actualizarTabla() {
    tablaCuerpo.innerHTML = ''; 
    
    if (prescripcionVirtual.length === 0) {
        tablaCuerpo.innerHTML = `<tr id="fila-vacia"><td colspan="4" style="padding: 15px; text-align: center; color: #6c757d; font-style: italic;">Sin medicamentos en la receta. Agregue uno arriba.</td></tr>`;
        return;
    }

    prescripcionVirtual.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid #eee';
        tr.innerHTML = `
            <td style="padding: 8px;">${item.nombreText}</td>
            <td style="padding: 8px;">${item.dosis}</td>
            <td style="padding: 8px; text-align: center; font-weight: bold;">${item.cantidad_surtir}</td>
            <td style="padding: 8px; text-align: center;">
                <button type="button" onclick="quitarMedicamento(${index})" style="background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; padding: 2px 6px; font-size: 0.8em;">X</button>
            </td>
        `;
        tablaCuerpo.appendChild(tr);
    });
}

window.quitarMedicamento = function(index) {
    prescripcionVirtual.splice(index, 1);
    actualizarTabla();
};

btnAddMed.addEventListener('click', () => {
    if (!inputSelect.value || !inputDosis.value.trim() || !inputCantidad.value) {
        alert("Por favor, seleccione un fármaco, escriba la dosis y la cantidad antes de agregar.");
        return;
    }

    const idSeleccionado = inputSelect.value;
    const nombreSeleccionado = inputSelect.options[inputSelect.selectedIndex].text;
    const cantidadPedida = parseInt(inputCantidad.value);
    
    const medInfo = catalogoMedicamentos.find(m => m.idMed == idSeleccionado);
    
    if (cantidadPedida > medInfo.cantidad) {
        alert(`¡Stock insuficiente! Solo quedan ${medInfo.cantidad} cajas disponibles de este fármaco.`);
        return; 
    }
    
    const existe = prescripcionVirtual.find(m => m.idMed === idSeleccionado);
    if (existe) {
        alert("Este medicamento ya está en la lista. Si desea cambiar la cantidad, quítelo y vuelva a agregarlo.");
        return;
    }

    prescripcionVirtual.push({
        idMed: idSeleccionado,
        nombreText: nombreSeleccionado.split(' - ')[0], 
        dosis: inputDosis.value.trim(),
        cantidad_surtir: parseInt(inputCantidad.value)
    });

    actualizarTabla();

    inputSelect.value = '';
    inputDosis.value = '';
    inputCantidad.value = '1';
    inputSelect.focus(); 
});


// LÓGICA DEL MULTI-STEP Y ENVÍO AJAX
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
        const inputs = Array.from(tabs[n].querySelectorAll('input:not(#med-dosis):not(#med-cantidad), select:not(#med-select), textarea'));
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

    clearBtn.addEventListener('click', () => {
        if(confirm("¿Deseas borrar toda la receta?")) {
            form.reset();
            prescripcionVirtual = [];
            actualizarTabla();
            showStep(0); 
        }
    });

    submitBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        
        if (!validateStep(current)) return;
        
        if (prescripcionVirtual.length === 0) {
            alert("Debe agregar al menos un medicamento a la lista antes de emitir la receta.");
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = "Procesando...";

        const formData = new FormData(form);
        const dataObj = Object.fromEntries(formData.entries());

        dataObj.medicamentos = prescripcionVirtual;

        try {
            const res = await fetch('backend_nueva_receta.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataObj)
            });
            const data = await res.json();

            if(data.estatus === 'exito') {
                alert("✅ " + data.mensaje);
                form.reset();
                prescripcionVirtual = [];
                actualizarTabla();
                showStep(0);
            } else {
                alert("⚠️ " + data.mensaje);
            }
        } catch (error) {
            alert("Error de conexión con la farmacia.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Emitir Receta";
        }
    });

    showStep(0);
})();
</script>

    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>