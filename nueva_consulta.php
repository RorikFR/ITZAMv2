<?php
date_default_timezone_set('America/Mexico_City');

require 'inactive.php';       // Control de inactividad y cache
require 'autorizacion.php';   // Roles de usuario

// RBAC
requerir_roles(['Médico']);

//Menu de navegación dinámico
require 'header.php';
?>

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
                    <textarea id="consulta-sintomas" name="sintomas" rows="10" maxlength="5000" required></textarea>
                    <small style="color: gray; float: right;">Límite de caracteres: 5000</small>
                </fieldset>

                <fieldset id="antecedentes_fieldset">
                    <legend>Antecedentes Relevantes</legend>
                    <label for="alergias">*Alergias:</label>
                    <input class="form" type="text" id="alergias" name="alergias" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d1\s\.,;]+$" title="No se permiten números o símbolos. Ingrese 'Ninguna' si no aplica." required />

                    <label for="antecedentes">*Antecedentes familiares:</label>
                    <input class="form" type="text" id="antecedentes" name="antecedentes" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d1\s\.,;]+$" title="No se permiten números o símbolos. Ingrese 'Ninguno' si no aplica." required />

                    <label for="habitos">*Hábitos (Tabaquismo, Alcoholismo):</label>
                    <input class="form" type="text" id="habitos" name="habitos" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d1\s\.,;]+$" title="No se permiten números o símbolos. Ingrese 'Ninguno' si no aplica." required />
                </fieldset>
            </div>

            <div class="tab" data-step="2" aria-hidden="true">
                <label for="diagnostico">*Diagnóstico Médico:</label>
                <textarea id="consulta-diagnostico" name="diagnostico" rows="10" maxlength="5000" required></textarea>
                <small style="color: gray; float: right;">Límite de caracteres: 5000</small>
            </div>

            <div class="tab" data-step="3" aria-hidden="true">
                <label for="tratamiento">*Tratamiento</label>
                <textarea id="consulta-tratamiento" name="tratamiento" rows="10" maxlength="5000" required></textarea>
                <small style="color: gray; float: right;">Límite de caracteres: 5000</small>
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
document.addEventListener('DOMContentLoaded', async () => {
    // Cargar tipos de consulta
    try {
        const res = await fetch('backend_catalogos.php?tabla=cat_tipo_consulta');
        const datos = await res.json();
        const selectTipo = document.getElementById('tipo_consulta');
        selectTipo.innerHTML = '<option value="" disabled selected>Selecciona una opción:</option>';
        if(!datos.error) {
            datos.forEach(item => {
                selectTipo.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
            });
        }
    } catch (e) { console.error("Error cargando los tipos de consulta"); }
});

//Buscar paciente por CURP
const btnBuscar = document.getElementById('btn-buscar-curp');
const inputCurp = document.getElementById('curp');
const msgCurp = document.getElementById('curp-mensaje');

btnBuscar.addEventListener('click', async () => {
    const curp = inputCurp.value.trim().toUpperCase();
    inputCurp.value = curp;


    //Validar formato CURP
    const curpRegex = /^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/;
    if (!curpRegex.test(curp)) {
        msgCurp.style.color = '#d9534f';
        msgCurp.textContent = "El formato del CURP es incorrecto.";
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

//Contador de pasos
(function(){
    const form = document.getElementById('multiStepForm');
    const tabs = Array.from(document.querySelectorAll('.tab'));
    const tabButtons = Array.from(document.querySelectorAll('.tab-btn'));
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const clearBtn = document.getElementById('clearBtn'); 
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
        //Forzar búsqueda de paciente
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

    //Limpiar campos
    clearBtn.addEventListener('click', () => {
        if(confirm("¿Deseas borrar todos los datos de esta consulta?")) {
            form.reset(); 

            document.getElementById('idPaciente').value = '';
            document.getElementById('nombre_paciente').value = '';
            document.getElementById('curp-mensaje').style.display = 'none';
            // Reiniciar formulario
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

    // Envío AJAX
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
                let quiereReceta = confirm(`✅ ${data.mensaje}\n\n¿Desea emitir una receta médica derivada de esta consulta?`);
                
                if (quiereReceta) {
                    // Redirigir a módulo de recetas con ID de consulta
                    window.location.href = `nueva_receta.php?idConsulta=${data.idConsulta}`;
                } else {
                    //Limpiar formulario
                    form.reset();
                    document.getElementById('idPaciente').value = '';
                    document.getElementById('nombre_paciente').value = '';
                    showStep(0); 
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

<script src="Scripts/js/timeout.js"></script>