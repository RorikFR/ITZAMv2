<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';       
require 'autorizacion.php';   

//RBAC
requerir_roles(['Médico']);

//Menu dinámico
require 'header.php';
?>
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
                        <button type="button" id="btn-buscar-curp" class="btn-small">🔍</button>
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
                    <textarea id="laboratorio-observ" maxlength="2000"></textarea> 
                    <small id="contador-laboratorio-observ" class="contador-char"></small>
                </fieldset>
            </div>

            <div class="tab" data-step="1" aria-hidden="true">
                <fieldset id="orden-laboratorio-datos-medicos">

                    <label for="laboratorio-diag-pre">*Diagnóstico preliminar:</label>
                    <textarea id="laboratorio-diag-pre" name="diagnostico_preliminar" rows="6" maxlength="5000" required></textarea>
                    <small id="contador-laboratorio-diag-pre" class="contador-char">Límite: 5000 caracteres</small>
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
//Cargar catálogos
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

    } catch (e) { console.error("Error cargando catálogos"); }
});

// Buscar Paciente por CURP
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
        btnBuscar.textContent = "🔍";
    }
});

// Contador de pasos y AJAX
(function(){
    const form = document.getElementById('multiStepForm');
    const tabs = Array.from(document.querySelectorAll('.tab'));
    const tabButtons = Array.from(document.querySelectorAll('.tab-btn'));
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const stepIndicator = document.getElementById('stepIndicator');
    const clearBtn = document.getElementById('clearBtn');
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

    //Limpiar y reiniciar formulario
    clearBtn.addEventListener('click', () => {
        if(confirm("¿Estás seguro de que deseas borrar todos los datos ingresados?")) {
            form.reset(); 
            document.getElementById('idPaciente').value = ''; 
            document.getElementById('curp-mensaje').style.display = 'none'; 
            showStep(0); 
        }
    });

    //Guardar datos
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

<script>
    //Auto-scroll
    document.addEventListener("DOMContentLoaded", function() {
        const formulario = document.getElementById("multiStepForm");
        
        if (formulario) {
            setTimeout(() => {
                formulario.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'     
                });
            }, 300);
        }
    });
</script>

<script src="Scripts/js/timeout.js"></script>

        <footer class="bottombar">© 2026 ITZAM</footer>

    </body>
</html>