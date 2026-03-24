<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';     
require 'autorizacion.php';   

//RBAC
requerir_roles(['Médico']);

require 'header.php';
?>

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
                    <textarea class="form" id="indicaciones_generales" name="indicaciones_generales" rows="3" maxlength="1000" style="resize: none; width:100%;" required></textarea>
                    <small id="contador-indicaciones_generales" class="contador-char">Límite de caracteres: 1000</small>
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
                            <input type="text" id="med-dosis" class="form" style="margin-bottom: 0;" maxlength="100" />
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

<footer class="bottombar">© 2026 ITZAM</footer>

<script>
//Variables gloables
let catalogoMedicamentos = []; 
let prescripcionVirtual = []; 

document.addEventListener('DOMContentLoaded', async () => {
    
    //Obtener id de consulta a partir de URL
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

    //Cargar inventario de medicamentos
    const selectMed = document.getElementById('med-select');
    try {
        const res = await fetch('backend_nueva_receta.php?accion=obtener_medicamentos');
        const dataText = await res.text(); 
        
        try {
            const data = JSON.parse(dataText);
            
            if(data.estatus === 'exito') {
                catalogoMedicamentos = data.datos;
                
                if (catalogoMedicamentos.length === 0) {
                    selectMed.innerHTML = '<option value="" disabled selected>No hay medicamentos en stock en esta clínica</option>';
                } else {
                    selectMed.innerHTML = '<option value="" disabled selected>Seleccione un medicamento...</option>';
                    catalogoMedicamentos.forEach(med => {
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

    //Logica del formulario
    inicializarFormulario();
});

function inicializarFormulario() {
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
        //Menu
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
    //Validaciones
    btnAddMed.addEventListener('click', () => {
        if (!inputSelect.value || !inputDosis.value.trim() || !inputCantidad.value) {
            alert("Atención: Seleccione un fármaco, escriba la dosis y la cantidad antes de agregar.");
            return;
        }

        const idSeleccionado = inputSelect.value;
        const nombreSeleccionado = inputSelect.options[inputSelect.selectedIndex].text;
        const cantidadPedida = parseInt(inputCantidad.value);
        
        const medInfo = catalogoMedicamentos.find(m => m.idMed == idSeleccionado);
        
        if (cantidadPedida > medInfo.cantidad) {
            alert(`Atención: ¡Stock insuficiente! Solo quedan ${medInfo.cantidad} cajas disponibles de este fármaco.`);
            return; 
        }
        
        const existe = prescripcionVirtual.find(m => m.idMed === idSeleccionado);
        if (existe) {
            alert("Atención: Este medicamento ya está en la lista. Si desea cambiar la cantidad, quítelo y vuelva a agregarlo.");
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

    //AJAX y contador de pasos
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
            alert("Atención: Debe agregar al menos un medicamento a la lista antes de emitir la receta.");
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
                alert("Éxito: " + data.mensaje);
                form.reset();
                prescripcionVirtual = [];
                actualizarTabla();
                showStep(0);
            } else {
                alert("Atención: " + data.mensaje);
            }
        } catch (error) {
            alert("Error de conexión con la farmacia.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Emitir Receta";
        }
    });

    showStep(0);
}

document.addEventListener("DOMContentLoaded", function() {
    // Obtener textareas
    const textareas = document.querySelectorAll('textarea[maxlength]');

    textareas.forEach(function(textarea) {
        // Seleccionar textareas
        const contadorId = 'contador-' + textarea.id;
        const contadorEl = document.getElementById(contadorId);

        if (contadorEl) {
            //Obtener limite
            const limite = parseInt(textarea.getAttribute('maxlength'));

            // Logica contador
            const actualizarContador = function() {
                const caracteresEscritos = textarea.value.length;
                const restantes = limite - caracteresEscritos;
                
                contadorEl.textContent = restantes + " caracteres restantes";

                //Colores dinamicos
                if (restantes <= 50) {
                    contadorEl.style.color = 'var(--PANTONE7420C)'; 
                } else if (restantes <= (limite * 0.10)) { 
                    contadorEl.style.color = 'var(--PANTONE1255C)'; 
                } else {
                    contadorEl.style.color = 'gray';
                }
            };

            //Inicializar contador
            actualizarContador();

            // Escuchar eventos de entrada
            textarea.addEventListener('input', actualizarContador);
        }
    });
});
</script>

<script src="Scripts/js/timeout.js"></script>

    </body>
</html>