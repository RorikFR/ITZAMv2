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
        <h3>Formulario de registro de insumos en inventario</h3>
      </div>

   <div class="grid-wrapper">
    <div class="formulario-background-normal">
        <div class="tab-buttons" role="tablist" aria-label="Secciones del formulario">
            <button type="button" class="tab-btn active" data-step="0">Datos del insumo</button>
            <button type="button" class="tab-btn" data-step="1">Ingreso a inventario</button>
        </div>
        <span class="message">* Campos obligatorios</span>
        
        <form class="multiform" id="multiStepForm" novalidate autocomplete="off">
            <div class="tab active" data-step="0" aria-hidden="false">
                
                <fieldset style="background-color: #e8f4fd; border-radius: 8px; padding: 15px; margin-bottom: 15px; border: 1px solid #b6d4fe;">
                    <label for="idCatalogoInsumo" style="color: #084298;">📦 <strong>Catálogo Oficial de Insumos:</strong></label>
                    <select class="form" id="idCatalogoInsumo" name="idCatalogoInsumo" style="border-color: #0d6efd;">
                        <option value="nuevo" selected>➕ Registrar un insumo nuevo en el catálogo</option>
                    </select>
                </fieldset>

                <fieldset id="inventario-datos-generales-insumo">
                <label for="nombre">*Nombre del Insumo:</label>
                <input class="form datos-insumo" type="text" id="nombre" name="nombre" maxlength="120" pattern="^[\w\s.-]+$" required placeholder="Ej. Guantes Quirúrgicos">
                
                <label for="material">Material:</label>
                <input class="form datos-insumo" type="text" id="material" name="material" maxlength="65" pattern="^[\w\s.-]*$" placeholder="Ej. Látex, Plástico, Algodón">
                
                <label for="presentacion">Presentación:</label>
                <input class="form datos-insumo" type="text" id="presentacion" name="presentacion" maxlength="65" pattern="^[\w\s.-]*$" placeholder="Ej. Caja, Bolsa, Unidad">

                <label for="piezas_unidad">Piezas por paquete/unidad:</label>
                <input class="form datos-insumo" type="number" id="piezas_unidad" name="piezas_unidad" min="1" step="1" placeholder="Ej. 100">
                
                <label for="tamano">Tamaño/Calibre:</label>
                <input class="form datos-insumo" type="text" id="tamano" name="tamano" maxlength="45" pattern="^[\w\s.-]*$" placeholder="Ej. Mediano, Unisex">
              </fieldset>
            </div>

            <div class="tab" data-step="1" aria-hidden="true">
              <fieldset id="inventario-datos-proveedor-insumo">
                <label for="proveedor">*Proveedor:</label>
                <select class="form" id="proveedor" name="idProveedor" required>
                    <option value="" disabled selected>Elige una opción:</option>
                </select>

                <?php if (in_array($_SESSION['rol'], ['Administrador', 'SuperAdmin'])): ?>
                    <label for="idUnidadDestino" style="color: #d63384;">*Unidad Médica Destino:</label>
                    <select class="form" id="idUnidadDestino" name="idUnidadDestino" style="border-color: #d63384;" required>
                        <option value="" disabled selected>Elige una opción:</option>
                    </select>
                <?php endif; ?>
                
                <label for="cantidad">*Cantidad a ingresar (Paquetes/Cajas):</label>
                <input class="form" type="number" id="cantidad" name="cantidad" min="1" step="1" required>

                <label for="marca">Marca (Opcional):</label>
                <input class="form" type="text" id="marca" name="marca" maxlength="45" pattern="^[\w\s.-]*$" placeholder="Ej. MediGloves">
                
                <label for="lote">Lote (Opcional):</label>
                <input class="form" type="text" id="lote" name="lote" maxlength="45" pattern="^[a-zA-Z0-9_-]*$">
                
                <label for="fecha_caducidad">Fecha de caducidad (Si aplica):</label>  
                <input class="form" type="date" id="fecha_caducidad" name="fecha_caducidad">
              </fieldset>
            </div>
        </form>
        
        <div class="step-controls">
            <button class="multi-btn" type="button" id="prevBtn">Anterior</button>
            <button class="multi-btn" type="button" id="nextBtn">Siguiente</button>
            <button class="multi-btn-clear" type="button" id="clearBtn" style="background-color: #6c757d;">Limpiar campos</button>             
            <button class="multi-btn-submit" type="submit" id="submitBtn" form="multiStepForm" style="display: none;">Registrar insumos</button>
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
        const selectCatalogo = document.getElementById('idCatalogoInsumo');
        const inputsInsumo = Array.from(document.querySelectorAll('.datos-insumo'));
        let current = 0;
        const total = tabs.length;
        let catalogoInsumos = [];

        // Validar fecha de caducidad no sea "hoy" o anterior
        const fechaInput = document.getElementById('fecha_caducidad');
        const manana = new Date();
        manana.setDate(manana.getDate() + 1);
        fechaInput.min = manana.toISOString().split('T')[0];

        //Carga de datos
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                // Cargar proveedores
                const resProv = await fetch('backend_catalogos.php?tabla=proveedores');
                const dataProv = await resProv.json();
                const selectProveedor = document.getElementById('proveedor');
                dataProv.forEach(item => {
                    selectProveedor.innerHTML += `<option value="${item.id}">${item.valor}</option>`;
                });

                // Cargar insumos
                const resCat = await fetch('backend_get_cat_insumos.php');
                catalogoInsumos = await resCat.json();
                catalogoInsumos.forEach(ins => {
                    const extraInfo = ins.tamano ? ` (${ins.tamano})` : '';
                    selectCatalogo.innerHTML += `<option value="${ins.idCatalogoInsumo}">${ins.nombre}${extraInfo} - ${ins.presentacion}</option>`;
                });

                // Cargar unidades médicas (solo si usuario es admin)
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
                // Si es nuevo, vaciamos y desbloqueamos los campos
                inputsInsumo.forEach(input => {
                    input.value = '';
                    input.style.pointerEvents = 'auto'; 
                    input.style.backgroundColor = '';
                    input.readOnly = false;
                });
            } else {
                const ins = catalogoInsumos.find(i => i.idCatalogoInsumo == valor);
                if (ins) {
                    document.getElementById('nombre').value = ins.nombre;
                    document.getElementById('material').value = ins.material || '';
                    document.getElementById('presentacion').value = ins.presentacion || '';
                    document.getElementById('piezas_unidad').value = ins.piezas_unidad || '';
                    document.getElementById('tamano').value = ins.tamano || '';
                    
                    inputsInsumo.forEach(input => {
                        input.style.pointerEvents = 'none'; 
                        input.style.backgroundColor = '#e9ecef'; 
                        input.readOnly = true;
                    });
                }
            }
        });

        //Contador de pasos formulario
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
                const res = await fetch('backend_nuevo_insumo.php', {
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
                submitBtn.textContent = "Registrar insumos";
            }
        });

        showStep(0);
    })();
</script>

<script src="Scripts/js/timeout.js"></script>

        <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>