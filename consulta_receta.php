<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';     
require 'autorizacion.php';   

//RBAC
requerir_roles(['Médico', 'Enfermería']);

//Menu dinamico
require 'header.php';
?>
    <br>

        <div class="tabla-container">
            <table id="tablaRecetas" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th class="all">ID</th>
                        <th class="all">Nombre del paciente</th> 
                        <th class="all">CURP paciente</th>
                        <th class="all">Médico que receta</th>
                        <th class="none">Medicamento</th>
                        <th class="none">Dosis</th>
                        <th class="none">Cajas surtidas</th>
                        <th class="all">Fecha prox. consulta</th>
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
                    <label for="inputModalProxConsulta">Próxima consulta:</label>
                    <input type="date" id="inputModalProxConsulta" min="<?= date('Y-m-d') ?>">
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
        const inputModalProxConsulta = document.getElementById("inputModalProxConsulta");

        let tablaInstancia = null; 

        //Carga inicial de datos
        async function cargarDatosIniciales() {
            if (tablaInstancia !== null) {
                tablaInstancia.destroy();
                tablaInstancia = null;
            }

            cuerpoTabla.innerHTML = "<tr><td colspan='9' style='text-align:center'>Cargando base de datos...</td></tr>";

            try {
                const response = await fetch('backend_consulta_receta.php');
                const datos = await response.json();
                
                if(datos.error) { alert("Atención: " + datos.error); return; }
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='9' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        //Renderizar e iniciarlizar DataTables
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='9' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach(item => {
                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><b>${item.idReceta}</b></td>
                        <td>${item.paciente}</td> 
                        <td style="font-family: monospace;">${item.curp_paciente}</td>
                        <td>${item.medico}</td>
                        <td style="color: #0056b3; font-weight: bold;">${item.medicamento}</td>
                        <td>${item.dosis}</td>
                        <td>${item.cantidad_surtir}</td>
                        <td>${item.prox_consulta || 'N/A'}</td> 
                        <td style="display: flex; gap: 5px;">
                            <button class="btn-pdf" style="background-color: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;" onclick="descargarReceta(${item.idReceta})">PDF</button>
                            <button class="btn-edit" onclick="abrirModal(${item.idReceta}, '${item.prox_consulta || ''}')">Editar</button>
                            <button class="btn-del" onclick="eliminarRegistro(${item.idReceta})">Borrar</button>
                        </td>
                    </tr>
                `;
            });

            tablaInstancia = $('#tablaRecetas').DataTable({
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
                        text: 'Reporte General PDF', 
                        className: 'btn-exportar',
                        orientation: 'landscape', 
                        pageSize: 'LETTER',       
                        title: 'Reporte de Recetas Médicas - ITZAM',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7] 
                        },
                        customize: function (doc) {
                            doc.defaultStyle.fontSize = 6; 
                            doc.styles.tableHeader.fontSize = 7;
                            doc.defaultStyle.alignment = 'center';
                            doc.styles.tableHeader.alignment = 'center';

                            doc.pageMargins = [15, 20, 15, 20];

                            doc.content[1].table.widths = [
                                'auto', // 0: ID
                                '*',    // 1: Nombre Paciente
                                'auto', // 2: CURP
                                '*',    // 3: Médico
                                '*',    // 4: Medicamento
                                '*',    // 5: Dosis
                                'auto', // 6: Cajas
                                'auto'  // 7: Prox Consulta
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
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    }
                ],
                pageLength: 10,
                ordering: true,
                order: [[0, "desc"]] 
            });
        }

        //Generar receta PDF
        async function descargarReceta(idReceta) {
            try {
                // Obtener estructura del documento
                const response = await fetch(`backend_consulta_receta.php?accion=obtener_receta_pdf&idReceta=${idReceta}`);
                const data = await response.json();

                if (data.estatus !== 'exito') {
                    alert("Atención: " + data.mensaje);
                    return;
                }

                const receta = data.datos;
                
                // Formatear fecha de emision
                const fechaEmision = receta.fecha_consulta 
                    ? new Date(receta.fecha_consulta).toLocaleDateString('es-MX', { timeZone: 'UTC' }) 
                    : new Date().toLocaleDateString('es-MX');

                // Agregar medicamentos
                const filasMedicamentos = [
                    [
                        { text: 'Medicamento', style: 'tableHeader' },
                        { text: 'Dosis e Indicaciones', style: 'tableHeader' },
                        { text: 'Cajas', style: 'tableHeader', alignment: 'center' }
                    ]
                ];

                receta.medicamentos.forEach(med => {
                    const nombreCompleto = `${med.nombre} ${med.concentracion || ''} ${med.presentacion || ''}`.trim();
                    filasMedicamentos.push([
                        nombreCompleto,
                        med.dosis,
                        { text: med.cantidad_surtir.toString(), alignment: 'center' }
                    ]);
                });

                // Estructura del documento
                const docDefinition = {
                    pageSize: 'LETTER',
                    pageMargins: [40, 40, 40, 80],
                    content: [
                        // Membrete 
                        { text: 'Sistema ITZAM', style: 'header', alignment: 'center', color: '#084298' },
                        { text: 'Receta Médica', style: 'subheader', alignment: 'center', color: '#6c757d', margin: [0, 0, 0, 20] },
                        
                        // Datos del médico
                        { text: `Dr(a). ${receta.medico_nombre}`, style: 'doctorName', alignment: 'right' },
                        { text: `Cédula Profesional: ${receta.medico_cedula}`, style: 'doctorInfo', alignment: 'right' },
                        ...(receta.especialidad ? [{ text: `Especialidad: ${receta.especialidad}`, style: 'doctorInfo', alignment: 'right' }] : []),
                        ...(receta.medico_cedula_esp ? [{ text: `Cédula Esp: ${receta.medico_cedula_esp}`, style: 'doctorInfo', alignment: 'right' }] : []),
                        { text: '', margin: [0, 0, 0, 15] },

                        // Datos del paciente
                        {
                            columns: [
                                {
                                    width: '*',
                                    text: [
                                        { text: 'Datos del Paciente\n', style: 'boxTitle' },
                                        `Nombre: ${receta.paciente_nombre}\n`,
                                        `Edad: ${receta.edad} años`
                                    ],
                                    margin: [0, 0, 10, 0]
                                },
                                {
                                    width: 150,
                                    text: [
                                        { text: 'Folio y Fecha\n', style: 'boxTitle' },
                                        `Receta No: ${receta.idReceta.toString().padStart(6, '0')}\n`,
                                        `Fecha: ${fechaEmision}`
                                    ]
                                }
                            ],
                            columnGap: 10,
                            margin: [0, 0, 0, 15] 
                        },

                        // Diagnostico de consulta médica
                        ...(receta.diagnostico ? [
                            { text: 'Diagnóstico Médico:', style: 'sectionTitle', margin: [0, 0, 0, 5] },
                            { text: receta.diagnostico, margin: [0, 0, 0, 20] }
                        ] : []),
                        // ---------------------------------

                        // Prescripción
                        { text: 'PRESCRIPCIÓN MÉDICA', style: 'sectionTitle', color: '#084298', margin: [0, 0, 0, 10] },
                        {
                            table: {
                                headerRows: 1,
                                widths: ['*', '*', 50],
                                body: filasMedicamentos
                            },
                            layout: 'lightHorizontalLines',
                            margin: [0, 0, 0, 20]
                        },

                        // Indicaciones generales
                        { text: 'Indicaciones Generales y Cuidados:', style: 'sectionTitle', margin: [0, 0, 0, 5] },
                        { text: receta.indicaciones_generales, margin: [0, 0, 0, 15] },

                        // Proxima cita
                        ...(receta.prox_consulta ? [
                            { text: `Próxima Cita: ${new Date(receta.prox_consulta).toLocaleDateString('es-MX', { timeZone: 'UTC'})}`, style: 'sectionTitle' }
                        ] : [])
                    ],
                    
                    // Pie de página (Firma)
                    footer: function(currentPage, pageCount) {
                        return {
                            columns: [
                                {
                                    text: [
                                        '___________________________________________________\n',
                                        'Firma del Médico / Sello Institucional'
                                    ],
                                    alignment: 'center',
                                    margin: [0, 20, 0, 0]
                                }
                            ],
                            margin: [40, 0]
                        };
                    },
                    
                    // Estilos del documento
                    styles: {
                        header: { fontSize: 16, bold: true },
                        subheader: { fontSize: 10 },
                        doctorName: { fontSize: 11, bold: true },
                        doctorInfo: { fontSize: 9 },
                        boxTitle: { fontSize: 10, bold: true },
                        sectionTitle: { fontSize: 10, bold: true },
                        tableHeader: { bold: true, fontSize: 10, color: 'black', fillColor: '#f8f9fa', alignment: 'center' }
                    }
                };

                // Dar formato a nombre del paciente
                const nombreLimpio = receta.paciente_nombre.replace(/\s+/g, '_');
                const nombreArchivo = `Receta_${nombreLimpio}_Folio_${receta.idReceta}.pdf`;

                // Generar PDF y renombrar
                pdfMake.createPdf(docDefinition).download(nombreArchivo);

            } catch (error) {
                console.error(error);
                alert("Error al generar el PDF de la receta.");
            }
        }

        // Eliminar registro
        async function eliminarRegistro(idReceta) {
            if(!confirm("¿Confirma que desea eliminar este registro permanentemente?")) return;

            try {
                const response = await fetch('backend_consulta_receta.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'eliminar', idReceta: idReceta })
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

        // Editar registro
        function abrirModal(idReceta, prox_consulta) {
            inputModalId.value = idReceta;
            inputModalProxConsulta.value = prox_consulta;
            modal.classList.add("show");
        }

        function cerrarModal() { modal.classList.remove("show"); }

        async function guardarCambios() {
            const form = document.getElementById('formEdicionModal');
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const idReceta = inputModalId.value;
            const prox_consulta = inputModalProxConsulta.value;

            const btnSave = document.querySelector('.btn-save');
            btnSave.disabled = true;
            btnSave.textContent = "Guardando...";

            try {
                const response = await fetch('backend_consulta_receta.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'editar', 
                        idReceta: idReceta, 
                        prox_consulta: prox_consulta 
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