<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';       
require 'autorizacion.php';   

//RBAC
requerir_roles(['Médico', 'Enfermería']);

//Menú dinámico
require 'header.php';
?>
    <br>

        <div class="tabla-container">
            <table id="tablaLaboratorio" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th class="all">ID</th>
                        <th class="min-tablet">Nombre del paciente</th>
                        <th class="min-tablet">Apellido Paterno</th>
                        <th class="min-tablet">Apellido Materno</th>
                        <th class="min-tablet">CURP</th>
                        <th class="min-tablet">Género</th>
                        <th class="min-tablet">Médico solicitante</th>
                        <th class="all">Prioridad</th>
                        <th class="all">Estudio requerido</th>
                        <th class="min-tablet">Diagnóstico preliminar</th>
                        <th class="min-tablet">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTabla"></tbody>
            </table>
        </div>

   <div id="modalEdicion" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">Editar Registro</div>
            <input type="hidden" id="inputModalId"> 
            <input type="hidden" id="inputModalEstudioViejo"> <div class="form-group">
                <label>Estudio requerido:</label>
                <select id="inputModalEstudio">
                    <option value="" selected disabled>Cargando estudios...</option>
                </select>
            </div>
            <div class="form-group">
                <label>Prioridad:</label>
                <select id="inputModalPrioridad">
                    <option value="" selected disabled>Cargando prioridades...</option>
                </select>
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-save" onclick="guardarCambios()">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        const cuerpoTabla = document.getElementById("cuerpoTabla");
        const modal = document.getElementById("modalEdicion");
        const inputModalId = document.getElementById("inputModalId");
        const inputModalEstudioViejo = document.getElementById("inputModalEstudioViejo");
        const inputModalEstudio = document.getElementById("inputModalEstudio");
        const inputModalPrioridad = document.getElementById("inputModalPrioridad");

        let tablaInstancia = null; 
        
        // Variables de sesión extraídas de PHP
        const idUsuarioLogueado = <?php echo $_SESSION['idUsuario']; ?>;
        const rolUsuario = "<?php echo $_SESSION['rol']; ?>";

        // Cargar catálogos
        async function cargarCatalogosModal() {
            try {
                // Cargar Prioridades
                const resPrio = await fetch('backend_catalogos.php?tabla=cat_prioridad_lab');
                const datosPrio = await resPrio.json();
                inputModalPrioridad.innerHTML = '<option value="" disabled selected>Selecciona una opción:</option>';
                if(!datosPrio.error) {
                    datosPrio.forEach(item => {
                        const op = document.createElement('option');
                        op.value = item.id;
                        op.textContent = item.valor;
                        inputModalPrioridad.appendChild(op);
                    });
                }

                // Cargar Estudios
                const resEst = await fetch('backend_catalogos.php?tabla=cat_estudios_laboratorio');
                const datosEst = await resEst.json();
                inputModalEstudio.innerHTML = '<option value="" disabled selected>Selecciona un estudio:</option>';
                if(!datosEst.error) {
                    datosEst.forEach(item => {
                        const op = document.createElement('option');
                        op.value = item.id;
                        op.textContent = item.valor;
                        inputModalEstudio.appendChild(op);
                    });
                }
            } catch (error) {
                console.error("Error al cargar catálogos:", error);
            }
        }

        // Carga inicial
        async function cargarDatosIniciales() {
            if (tablaInstancia !== null) {
                tablaInstancia.destroy();
                tablaInstancia = null;
            }

            cuerpoTabla.innerHTML = "<tr><td colspan='11' style='text-align:center'>Cargando base de datos...</td></tr>";

            try {
                const response = await fetch('backend_buscar-laboratorio.php');
                const datos = await response.json();
                
                if(datos.error) { alert(datos.error); return; }
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='11' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        // Renderizar e inicializar DataTables
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='11' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach(item => {
                
                let botonesAccion = "";
                if (item.idPersonal_solicitante == idUsuarioLogueado || rolUsuario === 'Administrador') {
                    botonesAccion = `
                        <button class="btn-edit" onclick="abrirModal(${item.idOrdenLaboratorio}, ${item.idEstudio}, ${item.idPrioridad})">Editar</button>
                        <button class="btn-del" onclick="eliminarRegistro(${item.idOrdenLaboratorio})">Borrar</button>
                    `;
                } else {
                    botonesAccion = `<span style="color: gray; font-style: italic; font-size: 0.9em;">Solo lectura</span>`;
                }

                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><b>${item.idOrdenLaboratorio}</b></td>
                        <td>${item.nombre_paciente}</td>
                        <td>${item.apellido_paterno}</td>
                        <td>${item.apellido_materno}</td>
                        <td style="font-family: monospace;">${item.curp}</td>
                        <td>${item.genero}</td>
                        <td><b>${item.medico_solicitante}</b></td>
                        <td style="font-weight: bold; color: ${item.prioridad === 'Urgente' ? 'red' : item.prioridad === 'Alta' ? 'orange' : 'black'}">${item.prioridad}</td>
                        <td>${item.estudio_requerido}</td>
                        <td>${item.diagnostico_preliminar}</td>
                        <td>
                            ${botonesAccion}
                        </td>
                    </tr>
                `;
            });

            tablaInstancia = $('#tablaLaboratorio').DataTable({
                responsive: true,
                language: {
                    "decimal": "",
                    "emptyTable": "No hay información en la base de datos",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(Filtrado de _MAX_ registros totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron coincidencias",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                },
                dom: '<"top"Bf>rt<"bottom"lip><"clear">',
                buttons: [
                    { 
                        extend: 'pdfHtml5', 
                        text: 'Reporte general PDF', 
                        className: 'btn-exportar',
                        orientation: 'landscape', 
                        pageSize: 'LETTER',       
                        title: 'Reporte de ordenes de laboratorio - ITZAM',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] 
                        }
                    },
                    { 
                        extend: 'csvHtml5', 
                        text: 'Reporte general CSV', 
                        className: 'btn-exportar',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] 
                        }
                    }
                ],
                pageLength: 10,
                ordering: true,
                order: [[0, "desc"]]
            });
        }

        // Eliminar
        async function eliminarRegistro(idOrdenLaboratorio) {
            if(!confirm("¿Confirma que desea eliminar este registro permanentemente?")) return;

            try {
                const response = await fetch('backend_buscar-laboratorio.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'eliminar', idOrdenLaboratorio: idOrdenLaboratorio})
                });
                const res = await response.json();
                                
                if(res.estatus === 'exito') {
                    alert("✅ " + res.mensaje);
                    cargarDatosIniciales();
                } else {
                    alert("⚠️ " + res.mensaje);
                }

            } catch (error) { alert("Error al eliminar"); }
        }

        // Editar
        function abrirModal(idOrdenLaboratorio, idEstudio, idPrioridad) {
            inputModalId.value = idOrdenLaboratorio;
            inputModalEstudioViejo.value = idEstudio; 
            inputModalEstudio.value = idEstudio; 
            inputModalPrioridad.value = idPrioridad;
            modal.classList.add("show");
        }

        function cerrarModal() { modal.classList.remove("show"); }

        async function guardarCambios() {
            const idOrdenLaboratorio = inputModalId.value;
            const idEstudioViejo = inputModalEstudioViejo.value;
            const idEstudioNuevo = inputModalEstudio.value;
            const idPrioridad = inputModalPrioridad.value;

            if(!idEstudioNuevo || !idPrioridad) {
                alert("Por favor selecciona un estudio y una prioridad.");
                return;
            }

            try {
                const response = await fetch('backend_buscar-laboratorio.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'editar', 
                        idOrdenLaboratorio: idOrdenLaboratorio, 
                        idPrioridad: idPrioridad,
                        idEstudioNuevo: idEstudioNuevo, 
                        idEstudioViejo: idEstudioViejo 
                    })
                });
                const res = await response.json();

                if(res.estatus === 'error') {
                    alert("⚠️ Atención:\n\n" + res.mensaje);
                } 
                else if (res.estatus === 'exito') {
                    alert("✅ " + res.mensaje);
                    cerrarModal();
                    cargarDatosIniciales();
                }
            } catch (error) { alert("Error al guardar cambios"); }
        }

        // Cargas iniciales
        document.addEventListener('DOMContentLoaded', () => {
            cargarCatalogosModal();
            cargarDatosIniciales();
        });

        // Cerrar modal 
        window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
    </script>

    <script src="Scripts/js/timeout.js"></script>

    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>