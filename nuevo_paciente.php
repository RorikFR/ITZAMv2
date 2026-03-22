<?php
date_default_timezone_set('America/Mexico_City');

require 'inactive.php';       // 1. Escudo de inactividad y anti-caché (ya verifica que el usuario esté logueado)
require 'autorizacion.php';   // 2. Motor de roles

// 3. LA BARRERA DE HIERRO: Solo Médicos y SuperAdmins pueden entrar aquí
requerir_roles(['Médico', 'Enfermería', 'Administrativo']);

require 'header.php';
?>
<!doctype html>

        <div class="title-box">
            <h3>Formulario de registro de pacientes</h3>
        </div>

<div class="grid-wrapper">
    <div class="formulario-background-normal">
        <div class="tab-buttons" role="tablist" aria-label="Secciones del formulario">
            <button type="button" class="tab-btn active" data-step="0">Identificación</button>
            <button type="button" class="tab-btn" data-step="1">Contacto</button>
            <button type="button" class="tab-btn" data-step="2">Información sociocultural</button>
        </div>
        <span class="message">* Campos obligatorios</span>
        
        <form class="multiform" id="multiStepForm" novalidate>
            <div class="tab active" data-step="0" aria-hidden="false">
                <fieldset id="paciente-datos">
                    <legend>Nombre del paciente</legend>
                    <label for="nombre">*Nombre:</label>
                    <input class="form" type="text" id="nombre" name="nombre" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$" title="Solo se permiten letras" required />

                    <label for="apellido_paterno">*Apellido paterno:</label>
                    <input class="form" type="text" id="apellido_paterno" name="apellido_paterno" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$" title="Solo se permiten letras" required />

                    <label for="apellido_materno">*Apellido materno:</label>
                    <input class="form" type="text" id="apellido_materno" name="apellido_materno" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$" title="Solo se permiten letras" required />
                </fieldset>

                <fieldset id="paciente-datos-oficiales">
                    <legend>Datos oficiales</legend>
                    <label for="curp">*CURP:</label>
                    <input class="form" type="text" id="curp" name="curp" maxlength="18" style="text-transform: uppercase;" pattern="^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$" title="Ingrese un CURP válido de 18 caracteres" required />

                    <label for="fecha_nacimiento">*Fecha de nacimiento:</label>
                    <input class="form" type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
                           min="<?= date('Y-m-d', strtotime('-120 years')) ?>" 
                           max="<?= date('Y-m-d') ?>" 
                           title="Ingrese una fecha válida (límite de edad: 120 años)" required />

                    <label for="genero">*Género:</label>
                    <select class="form" id="genero" name="genero" required>
                        <option value="" disabled selected>Seleccione</option>
                        <option value="masculino">Masculino</option>
                        <option value="femenino">Femenino</option>
                        <option value="hombretrans">Hombre transgénero</option>
                        <option value="mujertrans">Mujer transgénero</option>
                        <option value="otro">Otro</option>
                    </select>
                </fieldset>
            </div>

            <div class="tab" data-step="1" aria-hidden="true">
                <fieldset id="paciente-direccion">
                    <legend>Domicilio</legend>
                        <label for="calle">*Calle:</label>
                        <input class="form" type="text" id="calle" name="calle" pattern="^[a-zA-ZÀ-ÿ\u00f1\u00d10-9\s\.,\-]+$" title="Solo se permiten letras, números, espacios, puntos, comas o guiones" required />

                        <label for="numero">Número (Exterior/Interior):</label>
                        <input class="form" type="text" id="numero" name="numero" pattern="^[a-zA-Z0-9\s\-\/]+$" title="Solo se permiten letras, números, espacios, diagonales o guiones" required />
                </fieldset>
            
                <fieldset id="paciente-direccion-2" style="background-color: #f8f9fa; border-radius: 8px; padding: 15px; border: 1px solid #dee2e6;">
                    <legend>Ubicación Geográfica</legend>
                    
                    <label for="codigo_postal">*Código postal:</label>
                    <input class="form" type="text" id="codigo_postal" name="codigo_postal" maxlength="5" pattern="^\d{5}$" placeholder="Ej. 01000" required />
                    <small id="cp-mensaje" style="display: none; margin-bottom: 10px; font-weight: bold;"></small>

                    <label for="idUbicacion" id="label_colonia">*Colonia:</label>
                    <select class="form" id="idUbicacion" name="idUbicacion" required>
                        <option value="" disabled selected>Primero ingrese un Código Postal válido...</option>
                    </select>
                    <input class="form" type="text" id="nueva_colonia" name="nueva_colonia" style="display: none;" placeholder="Escriba el nombre de la colonia" maxlength="100" />

                    <label for="ciudad">*Ciudad/Municipio:</label>
                    <input class="form" type="text" id="ciudad" name="ciudad" readonly style="background-color: #e9ecef; color: #6c757d;" required maxlength="100" />

                    <label for="estado">*Estado:</label>
                    <input class="form" type="text" id="estado" name="estado" readonly style="background-color: #e9ecef; color: #6c757d;" required maxlength="60" />
                </fieldset>

                <fieldset id="paciente-datos-contacto">
                    <legend>Medios de contacto</legend>
                    <label for="telefono">*Teléfono:</label>
                    <input class="form" type="tel" id="telefono" name="telefono" pattern="^\d{10}$" title="El teléfono debe tener exactamente 10 números, sin espacios ni guiones" required />

                    <label for="email">Correo electrónico (opcional):</label>
                    <input class="form" type="email" id="email" name="email" />
                </fieldset>
            </div>

            <div class="tab" data-step="2" aria-hidden="true">
                <fieldset id="paciente-datos-socioculturales">
                    <legend>Estadística Nacional</legend>
                    
                    <label for="nacionalidad">*Nacionalidad:</label>
                    <select class="form" id="nacionalidad" name="nacionalidad" required>
                        <option value="" disabled selected>Seleccione:</option>
                        <option value="mexicana">Mexicana</option>
                        <option value="extranjero">Extranjero</option>
                    </select>

                    <label for="indigena">*¿Se considera indígena?:</label>
                    <select class="form" id="indigena" name="indigena" required>
                        <option value="" disabled selected>Seleccione:</option>
                        <option value="1">Sí</option>   <option value="0">No</option>   
                    </select>   

                    <label for="afrodesc">*¿Es afrodescendiente?:</label>
                    <select class="form" id="afrodesc" name="afrodesc" required>
                        <option value="" disabled selected>Seleccione:</option>
                        <option value="1">Sí</option>   <option value="0">No</option>   
                    </select>   
                </fieldset>
            </div>
        </form>
        
        <div class="step-controls">
            <button class="multi-btn" type="button" id="prevBtn">Anterior</button>
            <button class="multi-btn" type="button" id="nextBtn">Siguiente</button>
            <button class="multi-btn-clear" type="button" id="clearBtn" style="background-color: #6c757d;">Limpiar campos</button>
            <button class="multi-btn-submit" type="submit" id="submitBtn" style="display: none;">Registrar paciente</button>
            <div class="step-indicator" id="stepIndicator">Paso 1 de 3</div>
        </div>
    </div>
</div>

<footer class="bottombar">© 2026 ITZAM</footer>

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
    
    // Convertir CURP a mayúsculas en tiempo real
    const inputCurp = document.getElementById('curp');
    inputCurp.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // LÓGICA DE AUTOCOMPLETADO DE UBICACIÓN POR CP
    const inputCP = document.getElementById('codigo_postal');
    const selectColonia = document.getElementById('idUbicacion');
    const inputNuevaColonia = document.getElementById('nueva_colonia');
    const inputCiudad = document.getElementById('ciudad');
    const inputEstado = document.getElementById('estado');
    const msgCP = document.getElementById('cp-mensaje');

    inputCP.addEventListener('input', async function() {
        this.value = this.value.replace(/\D/g, ''); // Solo números
        
        if (this.value.length === 5) {
            try {
                msgCP.style.display = 'none';
                selectColonia.innerHTML = '<option value="" disabled selected>Buscando...</option>';
                
                const res = await fetch(`backend_catalogos.php?accion=buscar_cp&cp=${this.value}`);
                const datos = await res.json();
                
                if (datos.estatus === 'exito' && datos.data.length > 0) {
                    // MODO: CP ENCONTRADO
                    // Bloqueamos inputs y mostramos el select
                    inputCiudad.readOnly = true;
                    inputCiudad.style.backgroundColor = '#e9ecef';
                    inputCiudad.value = datos.data[0].ciudad;
                    
                    inputEstado.readOnly = true;
                    inputEstado.style.backgroundColor = '#e9ecef';
                    inputEstado.value = datos.data[0].estado;
                    
                    inputNuevaColonia.style.display = 'none';
                    inputNuevaColonia.required = false;
                    inputNuevaColonia.value = '';
                    
                    selectColonia.style.display = 'block';
                    selectColonia.required = true;
                    selectColonia.innerHTML = '<option value="" disabled selected>Seleccione su colonia</option>';
                    
                    datos.data.forEach(ubicacion => {
                        selectColonia.innerHTML += `<option value="${ubicacion.idUbicacion}">${ubicacion.colonia}</option>`;
                    });
                } else {
                    throw new Error('CP no encontrado');
                }
            } catch (e) {
                // MODO: CP NUEVO / NO ENCONTRADO
                // Ocultamos el select y mostramos el input libre
                selectColonia.style.display = 'none';
                selectColonia.required = false;
                selectColonia.innerHTML = ''; 
                
                inputNuevaColonia.style.display = 'block';
                inputNuevaColonia.required = true;
                
                // Desbloqueamos Ciudad y Estado para que el usuario escriba
                inputCiudad.readOnly = false;
                inputCiudad.style.backgroundColor = '#ffffff';
                inputCiudad.value = '';
                
                inputEstado.readOnly = false;
                inputEstado.style.backgroundColor = '#ffffff';
                inputEstado.value = '';
                
                // Mensaje informativo (No es un error rojo, es una instrucción azul)
                msgCP.style.color = '#0dcaf0'; 
                msgCP.textContent = "CP no registrado. Por favor, ingrese la colonia, ciudad y estado manualmente.";
                msgCP.style.display = 'block';
            }
        } else {
            // MODO: RESET (Menos de 5 dígitos)
            inputCiudad.value = ''; inputCiudad.readOnly = true; inputCiudad.style.backgroundColor = '#e9ecef';
            inputEstado.value = ''; inputEstado.readOnly = true; inputEstado.style.backgroundColor = '#e9ecef';
            
            inputNuevaColonia.style.display = 'none'; inputNuevaColonia.required = false;
            
            selectColonia.style.display = 'block'; selectColonia.required = true;
            selectColonia.innerHTML = '<option value="" disabled selected>Primero ingrese un Código Postal válido...</option>';
            
            msgCP.style.display = 'none';
        }
    });

    // --- Lógica del Multi-Step ---
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
            // Reseteamos visualmente el bloque de ubicación
            selectColonia.innerHTML = '<option value="" disabled selected>Primero ingrese un Código Postal válido...</option>';
            msgCP.style.display = 'none';
            showStep(0); 
        }
    });

    // Envío por AJAX
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
            const res = await fetch('backend_nuevo_paciente.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataObj)
            });
            const data = await res.json();

            if(data.estatus === 'exito') {
                alert("Éxito: " + data.mensaje);
                form.reset();
                selectColonia.innerHTML = '<option value="" disabled selected>Primero ingrese un Código Postal válido...</option>';
                showStep(0);
            } else {
                alert("Atención: " + data.mensaje);
            }
        } catch (error) {
            alert("Error de conexión al guardar.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Registrar paciente";
        }
    });

    showStep(0);
})();
</script>

<script src="Scripts/js/timeout.js"></script>