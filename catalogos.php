<?php
date_default_timezone_set('America/Mexico_City');

require 'inactive.php';       // 1. Escudo de inactividad
require 'autorizacion.php';   // 2. Motor de roles

// 3. LA BARRERA DE HIERRO: Solo Administradores
requerir_roles(['Administrador']);

require 'header.php';
?>
<!doctype html>

    <div class="title-box">
        <h1>Catálogos del sistema</h1>
        <p style="text-align: center; color: #666;">Selecciona un diccionario de datos para gestionar sus registros.</p>
    </div>

    <div id="vista-tarjetas" style="display: flex; gap: 20px; flex-wrap: wrap; padding: 20px; justify-content: center; max-width: 1200px; margin: auto;">
        
        <div onclick="abrirCatalogo('cat_especialidades', 'Especialidades Médicas')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
            <h1 style="font-size: 3em; margin: 0; color: #007bff;">🩺</h1>
            <h3 style="margin: 10px 0;">Especialidades</h3>
            <p style="color: #666; font-size: 0.9em;">Pediatría, Cardiología, etc.</p>
        </div>

        <div onclick="abrirCatalogo('cat_puestos', 'Puestos de Personal')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
            <h1 style="font-size: 3em; margin: 0; color: #17a2b8;">👨‍⚕️</h1>
            <h3 style="margin: 10px 0;">Puestos</h3>
            <p style="color: #666; font-size: 0.9em;">Médico General, Enfermería, etc.</p>
        </div>

        <div onclick="abrirCatalogo('cat_tipo_consulta', 'Tipos de Consulta')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
            <h1 style="font-size: 3em; margin: 0; color: #6f42c1;">📋</h1>
            <h3 style="margin: 10px 0;">Tipos de Consulta</h3>
            <p style="color: #666; font-size: 0.9em;">General, Urgencias, Especialidad.</p>
        </div>

        <div onclick="abrirCatalogo('cat_motivos_asesoria', 'Motivos de Asesoría')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
            <h1 style="font-size: 3em; margin: 0; color: #e83e8c;">🗣️</h1>
            <h3 style="margin: 10px 0;">Motivos de Asesoría</h3>
            <p style="color: #666; font-size: 0.9em;">Vacunación, Tratamiento, etc.</p>
        </div>

        <div onclick="abrirCatalogo('cat_estudios_laboratorio', 'Estudios de Laboratorio')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
            <h1 style="font-size: 3em; margin: 0; color: #20c997;">🧪</h1>
            <h3 style="margin: 10px 0;">Estudios Médicos</h3>
            <p style="color: #666; font-size: 0.9em;">Biometría, Química Sanguínea, etc.</p>
        </div>

        <div onclick="abrirCatalogo('cat_prioridad_lab', 'Prioridades de Laboratorio')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
            <h1 style="font-size: 3em; margin: 0; color: #dc3545;">🚨</h1>
            <h3 style="margin: 10px 0;">Prioridades Lab.</h3>
            <p style="color: #666; font-size: 0.9em;">Urgente, Rutina, Programado.</p>
        </div>

        <div onclick="abrirCatalogo('proveedores', 'Proveedores Autorizados')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
            <h1 style="font-size: 3em; margin: 0; color: #fd7e14;">📦</h1>
            <h3 style="margin: 10px 0;">Proveedores</h3>
            <p style="color: #666; font-size: 0.9em;">Farmacéuticas, Material médico.</p>
        </div>

        <div onclick="abrirCatalogo('cat_afiliacion', 'Tipos de Afiliación')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
            <h1 style="font-size: 3em; margin: 0; color: #198754;">🤝</h1>
            <h3 style="margin: 10px 0;">Afiliaciones</h3>
            <p style="color: #666; font-size: 0.9em;">IMSS, ISSSTE, INSABI, etc.</p>
        </div>

        <div onclick="abrirCatalogo('cat_categoria', 'Categorías de Unidad')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
            <h1 style="font-size: 3em; margin: 0; color: #ffc107;">🏢</h1>
            <h3 style="margin: 10px 0;">Categorías</h3>
            <p style="color: #666; font-size: 0.9em;">Clínica, Hospital General, etc.</p>
        </div>

        <div onclick="abrirCatalogo('registro_unidad', 'Unidades Médicas')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #f8f9fa; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
            <h1 style="font-size: 3em; margin: 0; color: #0dcaf0;">🏥</h1>
            <h3 style="margin: 10px 0;">Unidades Médicas</h3>
            <p style="color: #666; font-size: 0.9em;"><em>Vista de solo lectura</em></p>
        </div>

        <div onclick="abrirCatalogo('catalogo_ubicacion', 'Códigos Postales')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #f8f9fa; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
            <h1 style="font-size: 3em; margin: 0; color: #6c757d;">📍</h1>
            <h3 style="margin: 10px 0;">Ubicaciones</h3>
            <p style="color: #666; font-size: 0.9em;"><em>Vista de solo lectura</em></p>
        </div>

    </div>

    <div id="vista-tabla" style="display: none; max-width: 1200px; margin: auto; padding: 20px;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <button onclick="volverTarjetas()" class="btn-cancel" style="margin: 0;">⬅ Volver al menú</button>
            <h2 id="titulo-catalogo-actual" style="margin: 0; color: #0056b3;">Nombre del Catálogo</h2>
            <button onclick="abrirModalNuevo()" class="btn-save" style="margin: 0;">+ Nuevo Registro</button>
        </div>

        <div class="tabla-container">
            <table id="tablaDetalleCatalogo" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th class="all" style="width: 15%;">ID</th>
                        <th class="all" style="width: 60%;">Valor / Nombre</th>
                        <th class="all" style="width: 25%;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaDetalle"></tbody>
            </table>
        </div>
    </div>

    <div id="modalEdicion" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header" id="modal-titulo">Gestionar Registro</div>
            
            <form id="formCatalogoModal" novalidate autocomplete="off">
                <input type="hidden" id="inputModalId"> 
                
                <div class="form-group">
                    <label for="inputModalValor">Valor / Nombre del registro:</label>
                    <input type="text" id="inputModalValor" required maxlength="150" placeholder="Ej. Cardiología, A+, Oral..." autofocus>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="button" class="btn-save" id="btnSaveCatalogo" onclick="guardarCambios()">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>


<script>
    // Variables de Vistas
    const vistaTarjetas = document.getElementById("vista-tarjetas");
    const vistaTabla = document.getElementById("vista-tabla");
    const tituloCatalogo = document.getElementById("titulo-catalogo-actual");
    const btnNuevoRegistro = document.querySelector(".btn-save[onclick='abrirModalNuevo()']"); 
    
    // Variables de Tabla y Modal
    const cuerpoTabla = document.getElementById("cuerpoTablaDetalle");
    const modal = document.getElementById("modalEdicion");
    const formModal = document.getElementById("formCatalogoModal");
    const inputModalId = document.getElementById("inputModalId");
    const inputModalValor = document.getElementById("inputModalValor");
    const btnSaveCatalogo = document.getElementById("btnSaveCatalogo");

    let tablaInstancia = null; 
    
    // Estado actual
    let catalogoActualBD = ""; 
    let catalogoActualNombre = "";
    
    // LISTA DE CATÁLOGOS COMPLEJOS (No editables desde este modal simple)
    const catalogosSoloLectura = ['registro_unidad', 'catalogo_ubicacion'];

    // --- NAVEGACIÓN ENTRE VISTAS ---
    function abrirCatalogo(tabla_bd, nombre_amigable) {
        catalogoActualBD = tabla_bd;
        catalogoActualNombre = nombre_amigable;
        
        tituloCatalogo.innerText = nombre_amigable;
        
        if (catalogosSoloLectura.includes(tabla_bd)) {
            btnNuevoRegistro.style.display = 'none'; 
            tituloCatalogo.innerText += " (Solo Lectura)";
        } else {
            btnNuevoRegistro.style.display = 'inline-block';
        }
        
        vistaTarjetas.style.display = "none";
        vistaTabla.style.display = "block";

        cargarDatosCatalogo();
    }

    function volverTarjetas() {
        vistaTabla.style.display = "none";
        vistaTarjetas.style.display = "flex";
        catalogoActualBD = "";
    }

    // --- 1. CARGAR DATOS DEL CATÁLOGO SELECCIONADO (GET) ---
    async function cargarDatosCatalogo() {
        if (tablaInstancia !== null) {
            tablaInstancia.destroy();
            tablaInstancia = null;
        }

        cuerpoTabla.innerHTML = "<tr><td colspan='3' style='text-align:center'>Cargando registros...</td></tr>";

        try {
            const response = await fetch(`backend_catalogos.php?tabla=${catalogoActualBD}`);
            const datos = await response.json();
            
            if(datos.error) { alert(datos.error); volverTarjetas(); return; }
            renderizar(datos);

        } catch (error) {
            cuerpoTabla.innerHTML = "<tr><td colspan='3' style='text-align:center; color:red'>Error de conexión</td></tr>";
        }
    }

    // --- RENDERIZAR E INICIALIZAR DATATABLES ---
    function renderizar(datos) {
        cuerpoTabla.innerHTML = "";
        
        const esSoloLectura = catalogosSoloLectura.includes(catalogoActualBD);
        
        if(datos.length === 0){
            cuerpoTabla.innerHTML = "<tr><td colspan='3' style='text-align:center; padding: 20px;'>El catálogo está vacío.</td></tr>";
        } else {
            datos.forEach(item => {
                // Sanitizamos las comillas para no romper el HTML de los botones
                const valorSafe = item.valor ? item.valor.replace(/'/g, "\\'").replace(/"/g, '&quot;') : ''; 

                let botonesAccion = `
                    <button class="btn-edit" onclick="abrirModalEditar(${item.id}, '${valorSafe}')">Editar</button>
                    <button class="btn-del" onclick="eliminarRegistro(${item.id})">Borrar</button>
                `;
                
                if (esSoloLectura) {
                    botonesAccion = `<span style="color: #6c757d; font-size: 0.9em;">Módulo especializado</span>`;
                }

                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><b>${item.id}</b></td>
                        <td style="font-size: 1.1em;">${item.valor}</td>
                        <td>${botonesAccion}</td>
                    </tr>
                `;
            });
        }

        tablaInstancia = $('#tablaDetalleCatalogo').DataTable({
            responsive: {
                details: {
                    renderer: function (api, rowIdx, columns) {
                        let data = $.map(columns, function (col, i) {
                            return col.hidden ?
                                '<div class="dtr-detalle-celda" data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                                    '<div class="dtr-detalle-titulo">' + col.title + '</div> ' +
                                    '<div class="dtr-detalle-dato">' + col.data + '</div>' +
                                '</div>' : '';
                        }).join('');
                        return data ? $('<div class="dtr-detalle-fila"/>').append(data) : false;
                    }
                }
            },
            language: {
                "decimal": "",
                "emptyTable": "No hay registros en este catálogo",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(Filtrado de _MAX_ registros totales)",
                "search": "Buscar registro:",
                "zeroRecords": "No se encontraron coincidencias",
                "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" }
            },
            dom: '<"top"Bf>rt<"bottom"lip><"clear">',
            buttons: [
                { 
                    extend: 'pdfHtml5', 
                    text: 'Exportar PDF', 
                    className: 'btn-exportar',
                    // Título dinámico
                    title: function() { return 'Catálogo de ' + catalogoActualNombre + ' - ITZAM'; },
                    exportOptions: {
                        columns: [0, 1] // Excluimos los botones
                    },
                    customize: function (doc) {
                        doc.defaultStyle.fontSize = 10;
                        doc.styles.tableHeader.fontSize = 11;
                        doc.content[1].table.widths = ['20%', '80%'];
                    }
                },
                { extend: 'csvHtml5', text: 'Exportar CSV', className: 'btn-exportar', exportOptions: { columns: [0, 1] } }
            ],
            pageLength: 10,
            ordering: true,
            order: [[1, "asc"]], 
            destroy: true 
        });
    }

    // --- 2. ELIMINAR (POST) ---
    async function eliminarRegistro(id) {
        if(!confirm(`¿Confirma que desea eliminar este registro del catálogo de ${catalogoActualNombre}?`)) return;

        try {
            const response = await fetch('backend_catalogos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    accion: 'eliminar', 
                    tabla: catalogoActualBD, 
                    id: id 
                })
            });
            const res = await response.json();
            
            if(res.estatus === 'exito') {
                cargarDatosCatalogo(); 
            } else {
                alert("Atención: " + res.mensaje);
            }
        } catch (error) { alert("Error al eliminar"); }
    }

    // --- 3. GESTIÓN DEL MODAL (NUEVO / EDITAR) ---
    function abrirModalNuevo() {
        document.getElementById("modal-titulo").innerText = `Nuevo Registro - ${catalogoActualNombre}`;
        formModal.reset();
        inputModalId.value = ""; 
        modal.classList.add("show");
    }

    function abrirModalEditar(id, valor) {
        document.getElementById("modal-titulo").innerText = `Editar Registro - ${catalogoActualNombre}`;
        inputModalId.value = id;
        inputModalValor.value = valor;
        modal.classList.add("show");
    }

    function cerrarModal() { 
        modal.classList.remove("show"); 
    }

    async function guardarCambios() {
        if (!formModal.checkValidity()) {
            formModal.reportValidity();
            return;
        }

        const id = inputModalId.value;
        const valor = inputModalValor.value.trim();
        const accion = id === "" ? 'crear' : 'editar'; 

        btnSaveCatalogo.disabled = true;
        btnSaveCatalogo.textContent = "Guardando...";

        try {
            const response = await fetch('backend_catalogos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    accion: 'editar', // Tu backend parece usar la acción base para guardar o insertar
                    tabla: catalogoActualBD,
                    id: id, 
                    valor: valor 
                })
            });
            const res = await response.json();
            
            if(res.estatus === 'exito') {
                cerrarModal();
                cargarDatosCatalogo(); 
            } else {
                alert("Atención: " + res.mensaje);
            }
        } catch (error) { 
            alert("Error al guardar cambios de red."); 
        } finally {
            btnSaveCatalogo.disabled = false;
            btnSaveCatalogo.textContent = "Guardar";
        }
    }

    window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
</script>

<script src="Scripts/js/timeout.js"></script>

    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>