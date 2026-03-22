<?php
date_default_timezone_set('America/Mexico_City');

require 'inactive.php';       // Control de cache e inactividad
require 'autorizacion.php';   // RBAC

// Roles autorizados
requerir_roles(['Médico', 'Administrativo', 'Enfermería']);

//Menú de navegación dinámico
require 'header.php';
?>
    <br>

        <div class="tabla-container">
            <table id="tablaPacientes" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th class="all">ID</th>
                        <th class="all">CURP</th>
                        <th class="all">Nombre</th>
                        <th class="all">Apellido paterno</th>
                        <th class="all">Apellido materno</th>
                        <th class="all">Fecha de nacimiento</th>
                        <th class="all">Género</th>
                        <th class="all">Teléfono</th>   
                        <th class="all">Email</th>
                        <th>Indígena</th>
                        <th>Afrodesc</th>
                        <th>Nacionalidad</th>
                        <th class="all">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTabla"></tbody>
            </table>
        </div>

    <div id="modalEdicion" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">Editar Registro</div>
            
            <form id="formEdicionModal" novalidate autocomplete="off">
                <input type="hidden" id="inputModalId"> 
                
                <div class="form-group">
                    <label for="inputModalTel">*Teléfono (10 dígitos):</label>
                    <input type="text" id="inputModalTel" required maxlength="10" pattern="^[0-9]{10}$" title="El teléfono debe contener exactamente 10 números, sin espacios ni guiones." placeholder="Ej. 5512345678">
                </div>
                
                <div class="form-group">
                    <label for="inputModalEmail">Correo electrónico:</label>
                    <input type="email" id="inputModalEmail" maxlength="120" placeholder="usuario@correo.com">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="button" class="btn-save" onclick="guardarCambios()">Guardar Cambios</button>
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

    <script>
        const cuerpoTabla = document.getElementById("cuerpoTabla");
        const modal = document.getElementById("modalEdicion");
        const inputModalId = document.getElementById("inputModalId");
        const inputModalTel = document.getElementById("inputModalTel");
        const inputModalEmail = document.getElementById("inputModalEmail");

        // Inicializar DataTables
        let tablaInstancia = null; 

        // Cargar datos
        async function cargarDatosIniciales() {
            if (tablaInstancia !== null) {
                tablaInstancia.destroy();
                tablaInstancia = null;
            }

            cuerpoTabla.innerHTML = "<tr><td colspan='13' style='text-align:center'>Cargando base de datos...</td></tr>";

            try {
                const response = await fetch('backend_consulta_paciente.php');
                const datos = await response.json();
                
                if(datos.error) { alert(datos.error); return; }
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='13' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        // Renderizar e inicializar DataTables
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='13' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach(item => {
                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><b>${item.idPaciente}</b></td>
                        <td style="font-family: monospace;">${item.curp}</td>
                        <td>${item.nombre}</td>
                        <td>${item.apellido_paterno}</td>
                        <td>${item.apellido_materno}</td>
                        <td>${item.fecha_nac}</td>
                        <td>${item.genero}</td>
                        <td>${item.telefono}</td>
                        <td>${item.correo_electronico}</td>
                        <td>${item.indigena == 1 || item.indigena == 'Sí' ? 'Sí' : 'No'}</td>
                        <td>${item.afrodescendencia == 1 || item.afrodescendencia == 'Sí' ? 'Sí' : 'No'}</td>
                        <td>${item.nacionalidad}</td>
                        <td>
                            <button class="btn-edit" onclick="abrirModal(${item.idPaciente}, '${item.telefono}', '${item.correo_electronico}')">Editar</button>
                            <button class="btn-del" onclick="eliminarRegistro(${item.idPaciente})">Borrar</button>
                        </td>
                    </tr>
                `;
            });

            tablaInstancia = $('#tablaPacientes').DataTable({
                responsive: {
                    details: {
                        renderer: function (api, rowIdx, columns) {
                            let data = $.map(columns, function (col, i) {
                                return col.hidden ?
                                    '<div class="dtr-detalle-celda" data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                                        '<div class="dtr-detalle-titulo">' + col.title + '</div> ' +
                                        '<div class="dtr-detalle-dato">' + col.data + '</div>' +
                                    '</div>' :
                                    '';
                            }).join('');

                            return data ?
                                $('<div class="dtr-detalle-fila"/>').append(data) :
                                false;
                        }
                    }
                },
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
                        title: 'Reporte de Pacientes - ITZAM',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] 
                        },
                        customize: function (doc) {
                            doc.defaultStyle.fontSize = 6;
                            doc.styles.tableHeader.fontSize = 7;
                            doc.defaultStyle.alignment = 'center';
                            doc.styles.tableHeader.alignment = 'center';

                            doc.pageMargins = [15, 20, 15, 20];

                            doc.content[1].table.widths = [
                                'auto', // 0: ID
                                'auto', // 1: CURP
                                '*',    // 2: Nombre
                                '*',    // 3: Apellido Paterno
                                '*',    // 4: Apellido Materno
                                'auto', // 5: Fecha Nac.
                                'auto', // 6: Género
                                'auto', // 7: Indígena
                                'auto', // 8: Afrodesc
                                'auto', // 9: Nacionalidad
                                'auto', // 10: Teléfono
                                '*'     // 11: Email
                            ];

                            var objLayout = {};
                            objLayout['hLineWidth'] = function(i) { return 0.5; };
                            objLayout['vLineWidth'] = function(i) { return 0.5; };
                            objLayout['hLineColor'] = function(i) { return '#aaa'; };
                            objLayout['vLineColor'] = function(i) { return '#aaa'; };
                            objLayout['paddingLeft'] = function(i) { return 2; };
                            objLayout['paddingRight'] = function(i) { return 2; };
                            doc.content[1].layout = objLayout;
                        }
                    },
                    { 
                        extend: 'csvHtml5', 
                        text: 'Reporte general CSV', 
                        className: 'btn-exportar',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] 
                        }
                    }
                ],
                pageLength: 10,
                ordering: true,
                order: [[0, "desc"]]
            });
        }

        //Eliminar registro
        async function eliminarRegistro(idPaciente) {
            if(!confirm("¿Confirma que desea eliminar este registro permanentemente?")) return;

            try {
                const response = await fetch('backend_consulta_paciente.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'eliminar', idPaciente: idPaciente })
                });
                const res = await response.json();
                
                if(res.estatus === 'exito') {
                    alert("Éxito: " + res.mensaje);
                    cargarDatosIniciales();
                } else {
                    alert("Atención: " + res.mensaje);
                }
            } catch (error) { alert("Error al eliminar"); }
        }

        //Editar registro
        function abrirModal(idPaciente, telefono, correo_electronico) {
            inputModalId.value = idPaciente;
            inputModalTel.value = telefono;
            inputModalEmail.value = correo_electronico;
            modal.classList.add("show");
        }

        function cerrarModal() { modal.classList.remove("show"); }

        // ✨ Función actualizada con validación HTML5
        async function guardarCambios() {
            const form = document.getElementById('formEdicionModal');
            
            // Escudo Frontend: Detenemos la función si los datos no cumplen los patrones HTML5
            if (!form.checkValidity()) {
                form.reportValidity(); // Muestra los globos de error del navegador
                return;
            }

            const idPaciente = inputModalId.value;
            const telefono = inputModalTel.value;
            const correo_electronico = inputModalEmail.value;

            // Bloqueamos el botón temporalmente para evitar doble clic
            const btnSave = document.querySelector('.btn-save');
            btnSave.disabled = true;
            btnSave.textContent = "Guardando...";

            try {
                const response = await fetch('backend_consulta_paciente.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'editar', 
                        idPaciente: idPaciente, 
                        telefono: telefono, 
                        correo_electronico: correo_electronico 
                    })
                });
                const res = await response.json();
                
                if(res.estatus === 'error') {
                    alert("Atención:\n\n" + res.mensaje);
                } 
                else if (res.estatus === 'exito') {
                    alert("Éxito: " + res.mensaje);
                    cerrarModal();
                    cargarDatosIniciales();
                }
            } catch (error) { 
                alert("Error al guardar cambios"); 
            } finally {
                // Restauramos el botón
                btnSave.disabled = false;
                btnSave.textContent = "Guardar Cambios";
            }
        }

        // Recargar datos
        cargarDatosIniciales();

        // Cerrar modal
        window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
    </script>

    <script src="Scripts/js/timeout.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>