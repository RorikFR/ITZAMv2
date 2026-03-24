<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';       
require 'autorizacion.php';   

//RBAC
requerir_roles(['Administrador']);

//Menu dinamico
require 'header.php';
?>

    <div class="title-box">
        <h1>Catálogos del sistema</h1>
    </div>

<div id="vista-tarjetas" class="vista-tarjetas">
        
        <div class="tarjeta-catalogo" onclick="abrirCatalogo('cat_especialidades', 'Especialidades Médicas')">
            <h1 class="tarjeta-icono">🩺</h1>
            <h3 class="tarjeta-titulo">Especialidades</h3>
            <p class="tarjeta-desc">Pediatría, Cardiología, etc.</p>
        </div>

        <div class="tarjeta-catalogo" onclick="abrirCatalogo('cat_puestos', 'Puestos de Personal')">
            <h1 class="tarjeta-icono">👨‍⚕️</h1>
            <h3 class="tarjeta-titulo">Puestos</h3>
            <p class="tarjeta-desc">Médico General, Enfermería, etc.</p>
        </div>

        <div class="tarjeta-catalogo" onclick="abrirCatalogo('cat_tipo_consulta', 'Tipos de Consulta')">
            <h1 class="tarjeta-icono">📋</h1>
            <h3 class="tarjeta-titulo">Tipos de Consulta</h3>
            <p class="tarjeta-desc">General, Urgencias, Especialidad.</p>
        </div>

        <div class="tarjeta-catalogo" onclick="abrirCatalogo('cat_motivos_asesoria', 'Motivos de Asesoría')">
            <h1 class="tarjeta-icono">🗣️</h1>
            <h3 class="tarjeta-titulo">Motivos de Asesoría</h3>
            <p class="tarjeta-desc">Vacunación, Tratamiento, etc.</p>
        </div>

        <div class="tarjeta-catalogo" onclick="abrirCatalogo('cat_estudios_laboratorio', 'Estudios de Laboratorio')">
            <h1 class="tarjeta-icono">🧪</h1>
            <h3 class="tarjeta-titulo">Estudios Médicos</h3>
            <p class="tarjeta-desc">Biometría, Química Sanguínea, etc.</p>
        </div>

        <div class="tarjeta-catalogo" onclick="abrirCatalogo('cat_prioridad_lab', 'Prioridades de Laboratorio')">
            <h1 class="tarjeta-icono">🚨</h1>
            <h3 class="tarjeta-titulo">Prioridades Lab.</h3>
            <p class="tarjeta-desc">Urgente, Rutina, Programado.</p>
        </div>

        <div class="tarjeta-catalogo" onclick="abrirCatalogo('proveedores', 'Proveedores Autorizados')">
            <h1 class="tarjeta-icono">📦</h1>
            <h3 class="tarjeta-titulo">Proveedores</h3>
            <p class="tarjeta-desc">Farmacéuticas, Material médico.</p>
        </div>

        <div class="tarjeta-catalogo" onclick="abrirCatalogo('cat_afiliacion', 'Tipos de Afiliación')">
            <h1 class="tarjeta-icono">🤝</h1>
            <h3 class="tarjeta-titulo">Afiliaciones</h3>
            <p class="tarjeta-desc">IMSS, ISSSTE, INSABI, etc.</p>
        </div>

        <div class="tarjeta-catalogo" onclick="abrirCatalogo('cat_categoria', 'Categorías de Unidad')">
            <h1 class="tarjeta-icono">🏢</h1>
            <h3 class="tarjeta-titulo">Categorías</h3>
            <p class="tarjeta-desc">Clínica, Hospital General, etc.</p>
        </div>

        <div class="tarjeta-catalogo readonly" onclick="abrirCatalogo('registro_unidad', 'Unidades Médicas')">
            <h1 class="tarjeta-icono">🏥</h1>
            <h3 class="tarjeta-titulo">Unidades Médicas</h3>
            <p class="tarjeta-desc"><em>Vista de solo lectura</em></p>
        </div>

        <div class="tarjeta-catalogo readonly" onclick="abrirCatalogo('catalogo_ubicacion', 'Códigos Postales')">
            <h1 class="tarjeta-icono">📍</h1>
            <h3 class="tarjeta-titulo">Ubicaciones</h3>
            <p class="tarjeta-desc"><em>Vista de solo lectura</em></p>
        </div>

    </div>

    <div id="vista-tabla" style="display: none; max-width: 1200px; margin: auto; padding: 20px;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <button onclick="volverTarjetas()" class="btn-cancel" style="margin: 0;">⬅ Volver al menú</button>
            <h2 id="titulo-catalogo-actual">Nombre del Catálogo</h2>
            <button onclick="abrirModalNuevo()" class="btn-save" style="margin: 0;">+ Nuevo Registro</button>
        </div>

        <div class="tabla-container-cat">
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
                    <input type="text" id="inputModalValor" required maxlength="150" autofocus>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-save" id="btnSaveCatalogo" onclick="guardarCambios()">Guardar</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
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
    
    //Catalogos solo lectura
    const catalogosSoloLectura = ['registro_unidad', 'catalogo_ubicacion'];

    //Navegacion
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

    //Cargar datos de catalogo
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

    //Renderizar e inicializar DataTables
    function renderizar(datos) {
        cuerpoTabla.innerHTML = "";
        
        const esSoloLectura = catalogosSoloLectura.includes(catalogoActualBD);
        
        if(datos.length === 0){
            cuerpoTabla.innerHTML = "<tr><td colspan='3' style='text-align:center; padding: 20px;'>El catálogo está vacío.</td></tr>";
        } else {
            datos.forEach(item => {
                //Sanitizar entradas
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

    //Eliminar registro
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

    //Crear registro
    function abrirModalNuevo() {
        document.getElementById("modal-titulo").innerText = `Nuevo Registro - ${catalogoActualNombre}`;
        formModal.reset();
        inputModalId.value = ""; 
        modal.classList.add("show");
    }

    //Editar registro
    function abrirModalEditar(id, valor) {
        document.getElementById("modal-titulo").innerText = `Editar Registro - ${catalogoActualNombre}`;
        inputModalId.value = id;
        inputModalValor.value = valor;
        modal.classList.add("show");
    }

    function cerrarModal() { 
        modal.classList.remove("show"); 
    }

    //Guardar cambios
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
                    accion: 'editar', 
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