<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';       
require 'autorizacion.php';   


//RBAC
requerir_roles(['Administrativo', 'Administrador']);

//Menú dinámico
require 'header.php';
?>

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

                <fieldset id="inventario-datos-generales-equipo">
                    <label for="idCatalogoEquipo" style="color: var(--PANTONE627C);"><strong>Catálogo de Equipos:</strong></label>
                    <select class="form" id="idCatalogoEquipo" name="idCatalogoEquipo" style="border-color: var(--PANTONE627C);">
                        <option value="nuevo" selected>➕ Registrar un equipo nuevo en el catálogo</option>
                    </select>

                    <label for="nombre">*Nombre del Equipo:</label>
                    <input class="form datos-equipo" type="text" id="nombre" name="nombre" maxlength="120" pattern="^[\w\s.-]+$" required placeholder="Ej. Monitor de Signos Vitales">
                    
                    <label for="marca">Marca:</label>
                    <input class="form datos-equipo" type="text" id="marca" name="marca" maxlength="65" pattern="^[\w\s.-]*$" placeholder="Ej. Philips, GE Healthcare">
                    
                    <label for="modelo">Modelo:</label>
                    <input class="form datos-equipo" type="text" id="modelo" name="modelo" maxlength="65" pattern="^[\w\s.-]*$" placeholder="Ej. IntelliVue X3">
                    
                    <label for="fabricante">Fabricante:</label>
                    <input class="form datos-equipo" type="text" id="fabricante" name="fabricante" maxlength="120" pattern="^[\w\s.-]*$" placeholder="Ej. Philips Medical Systems">
                </fieldset>
            </div>

            <div class="tab" data-step="1" aria-hidden="true">
                <fieldset id="inventario-datos-proveedor-equipo">
                    <label for="proveedor">*Proveedor:</label>
                    <select class="form" id="proveedor" name="idProveedor" required>
                        <option value="" disabled selected>Cargando opciones...</option>
                    </select>

                    <?php if (in_array($_SESSION['rol'], ['Administrador'])): ?>
                        <label for="idUnidadDestino" style="color: #d63384;">*Unidad Médica Destino:</label>
                        <select class="form" id="idUnidadDestino" name="idUnidadDestino" style="border-color: #d63384;" required>
                            <option value="" disabled selected>Elige una opción:</option>
                        </select>
                    <?php endif; ?>

                    <label for="fecha_compra">*Fecha de compra:</label>
                    <input class="form" type="date" id="fecha_compra" name="fecha_compra" required>  
                    
                    <label for="cantidad">*Cantidad a ingresar:</label>
                    <input class="form" type="number" id="cantidad" name="cantidad" min="1" step="1" required>
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

        // Validar fecha de compra
        const fechaCompra = document.getElementById('fecha_compra');
        fechaCompra.max = new Date().toISOString().split('T')[0];

        //Carga de datos
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                // Cargar proveedores
                const resProv = await fetch('backend_catalogos.php?tabla=proveedores');
                const dataProv = await resProv.json();
                const selectProveedor = document.getElementById('proveedor');
                selectProveedor.innerHTML = '<option value="" disabled selected>Selecciona un proveedor</option>';
                dataProv.forEach(item => {
                    selectProveedor.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
                });

                // Cargar catalogo de equipos
                const resCat = await fetch('backend_get_cat_equipo.php');
                catalogoEquipos = await resCat.json();
                catalogoEquipos.forEach(eq => {
                    const extraInfo = eq.modelo ? ` (Mod: ${eq.modelo})` : '';
                    selectCatalogo.innerHTML += `<option value="${eq.idCatalogoEquipo}">${eq.nombre} - ${eq.marca}${extraInfo}</option>`;
                });

                // Si el usuario es admin, cargar unidades médicas
                const selectUnidad = document.getElementById('idUnidadDestino');
                if (selectUnidad) {
                    const resUnidad = await fetch('backend_catalogos.php?tabla=registro_unidad');
                    const dataUnidad = await resUnidad.json();
                    if (!dataUnidad.error) {
                        dataUnidad.forEach(item => {
                            selectUnidad.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
                        });
                    }
                }
            } catch (e) {
                console.error("Error cargando datos dinámicos:", e);
            }
        });

        // Auto llenado de campos
        selectCatalogo.addEventListener('change', (e) => {
            const valor = e.target.value;
            if (valor === 'nuevo') {
                inputsEquipo.forEach(input => {
                    input.value = '';
                    input.style.pointerEvents = 'auto';
                    input.style.backgroundColor = '';
                    input.readOnly = false;
                });
            } else {
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

        // Contador de pasos del formulario
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
                selectCatalogo.dispatchEvent(new Event('change')); 
                showStep(0); 
            }
        });

        // AJAX
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