<?php
date_default_timezone_set('America/Mexico_City');

require 'inactive.php';       // Control de inactividad y cache
require 'autorizacion.php';   // Roles de usuario

//RBAC
requerir_roles(['Médico', 'Enfermería']);

//Menu de navegación dinámico
require 'header.php';
?>

    <br>

        <div class="tabla-container">
            <table id="tablaAsesorias" class="display" style="width:100%; text-align: center;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>CURP</th>
                        <th>Nombre</th>
                        <th>Apellido paterno</th>
                        <th>Apellido materno</th>
                        <th>Motivo de la solicitud</th>
                        <th>Comentarios</th>
                        <th>Atendió</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
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
                <label>CURP:</label>
                <input class="form" type="text" id="inputModalCURP" readonly>
                
                <label>Motivo:</label>
                <select id="inputModalMotivo" class="form">
                  <option value="" disabled selected>Cargando motivos...</option>
                </select>
                
                <label>Comentarios:</label>
                <textarea id="inputModalComentarios"></textarea>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        const cuerpoTabla = document.getElementById("cuerpoTabla");
        const modal = document.getElementById("modalEdicion");
        const inputModalId = document.getElementById("inputModalId");
        const inputModalCURP = document.getElementById("inputModalCURP");
        const inputModalMotivo = document.getElementById("inputModalMotivo");
        const inputModalComentarios = document.getElementById("inputModalComentarios");

        let tablaInstancia = null; 
        
        //Variables de sesión para control de botones de acción
        const idUsuarioLogueado = <?php echo $_SESSION['idUsuario']; ?>;
        const rolUsuario = "<?php echo $_SESSION['rol']; ?>";

        //Cargar catálogo de motivos
        async function cargarCatalogosModal() {
            try {
                // Usamos nuestro backend dinámico para traer el catálogo de motivos
                const response = await fetch('backend_catalogos.php?tabla=cat_motivos_asesoria');
                const datos = await response.json();

                if (!datos.error) {
                    inputModalMotivo.innerHTML = '<option value="" disabled selected>Elige una opción:</option>';
                    datos.forEach(item => {
                        const opcion = document.createElement('option');
                        opcion.value = item.id;
                        opcion.textContent = item.valor;
                        inputModalMotivo.appendChild(opcion);
                    });
                }
            } catch (error) {
                console.error("Error al cargar motivos:", error);
                inputModalMotivo.innerHTML = '<option value="" disabled selected>Error al cargar</option>';
            }
        }

        // Carga inicial de datos
        async function cargarDatosIniciales() {
            if (tablaInstancia !== null) {
                tablaInstancia.destroy();
                tablaInstancia = null;
            }
            
            cuerpoTabla.innerHTML = "<tr><td colspan='10' style='text-align:center'>Cargando base de datos...</td></tr>";

            try {
                const response = await fetch('backend_asesoria.php');
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
            
            if(datos.length > 0) {
                datos.forEach(item => {
                    
                    // Mostrar botones solo al usuario creador o administrador
                    let botonesAccion = "";
                    if (item.idPersonal == idUsuarioLogueado || rolUsuario === 'Administrador') {
                        botonesAccion = `
                            <button class="btn-edit" onclick="abrirModal(${item.idAsesoria}, '${item.curp}', ${item.idMotivo}, '${item.comentarios.replace(/'/g, "\\'")}')">Editar</button>
                            <button class="btn-del" onclick="eliminarRegistro(${item.idAsesoria})">Borrar</button>
                        `;
                    } else {
                        botonesAccion = `<span style="color: gray; font-style: italic; font-size: 0.9em;">Solo lectura</span>`;
                    }

                    cuerpoTabla.innerHTML += `
                        <tr>
                            <td><b>${item.idAsesoria}</b></td>
                            <td style="font-family: monospace;">${item.curp}</td>
                            <td>${item.nombre}</td>
                            <td>${item.apellido_p}</td>
                            <td>${item.apellido_m}</td>
                            <td>${item.motivo}</td>
                            <td>${item.comentarios}</td>
                            <td><b>${item.personal_medico}</b></td> 
                            <td>${item.fecha_solicitud}</td>
                            <td>
                                ${botonesAccion}
                            </td>
                        </tr>
                    `;
                });
            }


            tablaInstancia = $('#tablaAsesorias').DataTable({
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
                        text: 'Descargar PDF', 
                        className: 'btn-exportar',
                        orientation: 'landscape', 
                        pageSize: 'LETTER',       
                        title: 'Reporte de Asesorías Clínicas - ITZAM',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] 
                        }
                    },
                    { 
                        extend: 'csvHtml5', 
                        text: 'Descargar CSV', 
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
        async function eliminarRegistro(idAsesoria) {
            if(!confirm("¿Confirma que desea eliminar este registro permanentemente?")) return;

            try {
                const response = await fetch('backend_asesoria.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'eliminar', idAsesoria: idAsesoria })
                });
                
                const res = await response.json();
                
                if(res.estatus === 'exito') {
                    alert("✅ " + res.mensaje);
                    cargarDatosIniciales(); 
                } else {
                    alert("⚠️ " + res.mensaje);
                }
            } catch (error) { 
                console.error(error);
                alert("❌ Error de red o del servidor al eliminar"); 
            }
        }

        //Editar registro
        function abrirModal(idAsesoria, curp, idMotivo, comentarios) {
            inputModalId.value = idAsesoria;
            inputModalCURP.value = curp;
            inputModalMotivo.value = idMotivo; 
            inputModalComentarios.value = comentarios;
            modal.classList.add("show");
        }

        function cerrarModal() { modal.classList.remove("show"); }

        async function guardarCambios() {
            const idAsesoria = inputModalId.value;
            const curp = inputModalCURP.value;
            const idMotivo = inputModalMotivo.value; 
            const comentarios = inputModalComentarios.value;

            if(!idMotivo) {
                alert("Por favor, selecciona un motivo.");
                return;
            }

            try {
                const response = await fetch('backend_asesoria.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'editar', 
                        idAsesoria: idAsesoria, 
                        curp: curp,
                        idMotivo: idMotivo, 
                        comentarios: comentarios,
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
                
            } catch (error) { 
                console.error(error);
                alert("❌ Error de red o del servidor al guardar cambios"); 
            }
        }

        // Cargas iniciales al abrir la página
        cargarCatalogosModal();
        cargarDatosIniciales();

        // Cerrar modal
        window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
    </script>

    <script src="Scripts/js/timeout.js"></script>

            <footer class="bottombar">© 2026 ITZAM</footer>

    </body>
</html>
