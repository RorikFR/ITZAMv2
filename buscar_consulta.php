<?php
date_default_timezone_set('America/Mexico_City');

require 'inactive.php';       // Control de inactividad y cache
require 'autorizacion.php';   // Roles de usuario

// RBAC
requerir_roles(['Médico', 'Administrativo', 'Enfermería']);

//Menu de navegación dinámico
require 'header.php';
?>
<br>

       <div class="tabla-container">
            <table id="tablaConsultas" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th class="all">ID</th>
                        <th class="min-tablet">Nombre</th>
                        <th class="min-tablet">Apellido paterno</th>
                        <th class="min-tablet">Apellido materno</th>
                        <th class="all">CURP</th>
                        <th class="min-tablet">Fecha de nacimiento</th>
                        <th class="min-tablet">Género</th>
                        <th class="min-tablet">Atendió</th>
                        <th class="min-tablet">Tipo de consulta</th>
                        <th class="all">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTabla"></tbody>
            </table>
        </div>

    <div id="modalEdicion" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">Editar Registro</div>
            <input type="hidden" id="inputModalId"> 
            <div class="form-group">
                <label>Tipo de Consulta:</label>
                <select id="inputModalTipo" required>
                    <option value="" selected disabled>Cargando tipos de consulta...</option>
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
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        const cuerpoTabla = document.getElementById("cuerpoTabla");
        const modal = document.getElementById("modalEdicion");
        const inputModalId = document.getElementById("inputModalId");
        const inputModalTipo = document.getElementById("inputModalTipo");

        let tablaInstancia = null; 
        
        const idUsuarioLogueado = <?php echo $_SESSION['idUsuario']; ?>;
        const rolUsuario = "<?php echo $_SESSION['rol']; ?>";

        //Cargar catálogos
        async function cargarCatalogosModal() {
            try {
                const response = await fetch('backend_catalogos.php?tabla=cat_tipo_consulta');
                const datos = await response.json();

                if (!datos.error) {
                    inputModalTipo.innerHTML = '<option value="" disabled selected>Selecciona una opción:</option>';
                    datos.forEach(item => {
                        const opcion = document.createElement('option');
                        opcion.value = item.id;
                        opcion.textContent = item.valor;
                        inputModalTipo.appendChild(opcion);
                    });
                }
            } catch (error) {
                console.error("Error al cargar catálogo de consultas:", error);
                inputModalTipo.innerHTML = '<option value="" disabled selected>Error al cargar catálogo</option>';
            }
        }

        // Cargar datos 
        async function cargarDatosIniciales() {
            if (tablaInstancia !== null) {
                tablaInstancia.destroy();
                tablaInstancia = null;
            }

            cuerpoTabla.innerHTML = "<tr><td colspan='10' style='text-align:center'>Cargando base de datos...</td></tr>";

            try {
                const response = await fetch('backend_buscar-consulta.php');
                const datos = await response.json();
                
                if(datos.error) { alert(datos.error); return; }
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='10' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        // Renderizar e inicializar DataTables
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='10' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach(item => {
                
                let botonesAccion = "";
                if (item.idPersonal == idUsuarioLogueado || rolUsuario === 'Administrador') {
                    botonesAccion = `
                        <button class="btn-edit" onclick="abrirModal(${item.idConsulta}, ${item.idTipoConsulta || 'null'})">Editar</button>
                        <button class="btn-del" onclick="eliminarRegistro(${item.idConsulta})">Borrar</button>
                    `;
                } else {
                    botonesAccion = `<span style="color: gray; font-style: italic; font-size: 0.9em;">Solo lectura</span>`;
                }

                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><b>${item.idConsulta}</b></td>
                        <td>${item.nombre}</td>
                        <td>${item.apellido_p}</td>
                        <td>${item.apellido_m}</td>
                        <td style="font-family: monospace;">${item.curp}</td>
                        <td>${item.fecha_nac}</td>
                        <td>${item.genero}</td>
                        <td><b>${item.personal_medico}</b></td>
                        <td><span style="background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-size: 0.9em;">${item.tipo_consulta}</span></td>
                        <td>
                            ${botonesAccion}
                        </td>
                    </tr>
                `;
            });

            tablaInstancia = $('#tablaConsultas').DataTable({
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
                    "search": "Buscar paciente o consulta:",
                    "zeroRecords": "No se encontraron coincidencias",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": Activar para ordenar ascendente",
                        "sortDescending": ": Activar para ordenar descendente"
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
                        title: 'Reporte de consultas médicas - ITZAM',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] 
                        }
                    },
                    { 
                        extend: 'csvHtml5', 
                        text: 'Reporte general CSV', 
                        className: 'btn-exportar',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] 
                        }
                    }
                ],
                pageLength: 10,
                ordering: true,
                order: [[0, "desc"]]
            });
        }

        //Eliminar registro
        async function eliminarRegistro(idConsulta) {
            if(!confirm("¿Confirma que desea eliminar este registro permanentemente?")) return;

            try {
                const response = await fetch('backend_buscar-consulta.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'eliminar', idConsulta: idConsulta })
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

        //Editar registro
        function abrirModal(idConsulta, idTipoConsulta) {
            inputModalId.value = idConsulta;
            inputModalTipo.value = idTipoConsulta; 
            modal.classList.add("show");
        }

        function cerrarModal() { modal.classList.remove("show"); }

        async function guardarCambios() {
            const idConsulta = inputModalId.value;
            const idTipoConsulta = inputModalTipo.value; 

            if(!idTipoConsulta) {
                alert("Por favor, selecciona un tipo de consulta válido.");
                return;
            }

            try {
                const response = await fetch('backend_buscar-consulta.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'editar', 
                        idConsulta: idConsulta, 
                        idTipoConsulta: idTipoConsulta 
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
        cargarCatalogosModal();
        cargarDatosIniciales();

        // Cerrar modal 
        window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
    </script>

    <script src="Scripts/js/timeout.js"></script>

    <footer class="bottombar">© 2026 ITZAM</footer>

    </body>
</html>