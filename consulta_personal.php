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

    <br>

        <div class="tabla-container">
            <table id="tablaPersonal" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th class="all">ID</th>
                        <th class="all">Nombre</th>
                        <th class="all">Apellido paterno</th>
                        <th class="all">Apellido materno</th>   
                        <th>Cédula profesional</th>
                        <th>Email institucional</th>
                        <th>Teléfono celular</th>
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
                    <label for="inputModalEmail">*Email institucional:</label>
                    <input type="email" id="inputModalEmail" maxlength="120" required placeholder="usuario@itzam.com">
                </div>

                <div class="form-group">
                    <label for="inputModalTel">*Teléfono celular (10 dígitos):</label>
                    <input type="text" id="inputModalTel" required maxlength="10" pattern="^[0-9]{10}$" title="El teléfono debe contener exactamente 10 números, sin espacios ni guiones." placeholder="Ej. 5512345678">
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
        const inputModalEmail = document.getElementById("inputModalEmail");
        const inputModalTel = document.getElementById("inputModalTel");

        let tablaInstancia = null; 

        //Carga de datos 
        async function cargarDatosIniciales() {
            if (tablaInstancia !== null) {
                tablaInstancia.destroy();
                tablaInstancia = null;
            }

            cuerpoTabla.innerHTML = "<tr><td colspan='8' style='text-align:center'>Cargando base de datos...</td></tr>";

            try {
                const response = await fetch('backend_consulta-personal.php');
                const datos = await response.json();
                
                if(datos.error) { alert("Atención: " + datos.error); return; }
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='8' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        //Renderizar e inicializar DataTables
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='8' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach(item => {
                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><b>${item.idPersonal}</b></td>
                        <td>${item.nombre}</td>
                        <td>${item.apellido_paterno}</td>
                        <td>${item.apellido_materno}</td>
                        <td style="font-family: monospace; color: #0056b3;">${item.cedula_profesional}</td>
                        <td>${item.email_institucional}</td>
                        <td>${item.telefono_celular}</td>
                        <td>
                            <button class="btn-edit" onclick="abrirModal(${item.idPersonal},'${item.email_institucional}', '${item.telefono_celular}')">Editar</button>
                            <button class="btn-del" onclick="eliminarRegistro(${item.idPersonal})">Borrar</button>
                        </td>
                    </tr>
                `;
            });

            tablaInstancia = $('#tablaPersonal').DataTable({
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
                        title: 'Reporte de Personal - ITZAM',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6] 
                        },
                        customize: function (doc) {
                            // Ajustes para 7 columnas
                            doc.defaultStyle.fontSize = 8;
                            doc.styles.tableHeader.fontSize = 9;
                            doc.defaultStyle.alignment = 'center';
                            doc.styles.tableHeader.alignment = 'center';

                            doc.pageMargins = [20, 20, 20, 20];

                            doc.content[1].table.widths = [
                                'auto', // ID
                                '*',    // Nombre
                                '*',    // Apellido P
                                '*',    // Apellido M
                                'auto', // Cédula
                                '*',    // Email
                                'auto'  // Teléfono
                            ];

                            var objLayout = {};
                            objLayout['hLineWidth'] = function(i) { return 0.5; };
                            objLayout['vLineWidth'] = function(i) { return 0.5; };
                            objLayout['hLineColor'] = function(i) { return '#aaa'; };
                            objLayout['vLineColor'] = function(i) { return '#aaa'; };
                            objLayout['paddingLeft'] = function(i) { return 4; };
                            objLayout['paddingRight'] = function(i) { return 4; };
                            doc.content[1].layout = objLayout;
                        }
                    },
                    { 
                        extend: 'csvHtml5', 
                        text: 'Reporte general CSV', 
                        className: 'btn-exportar',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    }
                ],
                pageLength: 10,
                ordering: true,
                order: [[0, "desc"]]
            });
        }

        //Eliminar registros
        async function eliminarRegistro(idPersonal) {
            if(!confirm("¿Confirma que desea eliminar este registro permanentemente?")) return;

            try {
                const response = await fetch('backend_consulta-personal.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'eliminar', idPersonal: idPersonal })
                });
                const res = await response.json();
                
                if(res.estatus === 'exito') {
                    alert("Éxito: " + res.mensaje);
                    cargarDatosIniciales();
                } else {
                    alert("Atención: " + res.mensaje);
                }
            } catch (error) { alert("Error al eliminar el registro."); }
        }

        //Editar registros
        function abrirModal(idPersonal, email_institucional, telefono_celular) {
            inputModalId.value = idPersonal;
            inputModalEmail.value = email_institucional;
            inputModalTel.value = telefono_celular;
            modal.classList.add("show");
        }

        function cerrarModal() { modal.classList.remove("show"); }

        async function guardarCambios() {
            const form = document.getElementById('formEdicionModal');
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const idPersonal = inputModalId.value;
            const email_institucional = inputModalEmail.value;
            const telefono_celular = inputModalTel.value;

            const btnSave = document.querySelector('.btn-save');
            btnSave.disabled = true;
            btnSave.textContent = "Guardando...";

            try {
                const response = await fetch('backend_consulta-personal.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'editar', 
                        idPersonal: idPersonal, 
                        email_institucional: email_institucional, 
                        telefono_celular: telefono_celular 
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
                alert("Error de conexión al guardar cambios."); 
            } finally {
                btnSave.disabled = false;
                btnSave.textContent = "Guardar Cambios";
            }
        }

        //Recargar datos
        cargarDatosIniciales();

        // Cerrar modal
        window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
    </script>

    <script src="Scripts/js/timeout.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>