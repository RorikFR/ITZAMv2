<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';      
require 'autorizacion.php';  

//RBAC
requerir_roles(['Administrativo']);


//Menu dinamico
require 'header.php';
?>
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
                    <input class="form" type="text" id="nombre_unidad" name="nombre_unidad" placeholder="Ej. Hospital Central ITZAM" required maxlength="200" />

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
                    <input class="form" type="text" id="calle" name="calle" placeholder="Ej. Av. Lázaro Cárdenas 123" required maxlength="65" />

                    <label for="ubicacion">*Ubicación (CP - Colonia, Ciudad):</label>
                    <select class="form" id="ubicacion" name="idUbicacion" required>
                        <option value="" disabled selected>Cargando opciones...</option>
                    </select>
                </fieldset>
            </div>

            <div class="tab" data-step="2" aria-hidden="true">
                <fieldset id="unidad-datos-contacto">
                    <label for="telefono">*Teléfono de contacto (10 dígitos):</label>
                    <input class="form" type="tel" id="telefono" name="telefono" placeholder="Ej. 5512345678" required maxlength="10" pattern="^[0-9]{10}$" title="El teléfono debe tener exactamente 10 números, sin espacios ni guiones" />  

                    <label for="email">*Correo electrónico:</label>
                    <input class="form" type="email" id="email" name="email" placeholder="contacto@unidad.com" required maxlength="120" /> 
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

        //Carga de datos de catalogos
        async function cargarCatalogos() {
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
                        if (cat.id === 'ubicacion') {
                            select.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
                        } else {
                            select.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
                        }
                    });
                } catch (e) {
                    console.error(`Error cargando el catálogo ${cat.tabla}:`, e);
                }
            }
        }

        cargarCatalogos();

        //Logica del formulario
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

        //Validar pasos
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
        //Ejecutar formulario y deshabilitar botones
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
                    alert("Éxito: " + data.mensaje);
                    form.reset();
                    showStep(0);
                } else {
                    alert("Atención: " + data.mensaje);
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

<script src="Scripts/js/timeout.js"></script>

<footer class="bottombar">© 2026 ITZAM</footer>

</body>
</html>