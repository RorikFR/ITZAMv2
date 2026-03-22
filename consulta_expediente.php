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
            <table id="tablaExpediente" class="display" style="width:100%">
                <thead>
                    <tr>    
                        <th>ID Consulta</th>
                        <th>Nombre del paciente</th>
                        <th>CURP</th>
                        <th>Médico que atendió</th>
                        <th>Cédula</th>
                        <th>Unidad Médica</th>
                        <th>Tipo Consulta</th>
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
                <label>Médico que atendió:</label>
                <select id="inputModalMedico">
                    <option value="">Cargando médicos...</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Unidad médica:</label>
                <select id="inputModalUnidad">
                    <option value="">Cargando unidades...</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tipo de Consulta:</label>
                <select id="inputModalTipo">
                    <option value="" disabled selected>Cargando tipos de consulta...</option>
                </select>
            </div>
            
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-save" onclick="guardarCambios()">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <div style="display: none; width: 800px; height: 400px;">
        <canvas id="graficaOcultaVitales" width="800" height="400"></canvas>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script src="Scripts/js/logo_base64.js"></script>

    <script>
        const cuerpoTabla = document.getElementById("cuerpoTabla");
        const modal = document.getElementById("modalEdicion");
        const inputModalId = document.getElementById("inputModalId");
        const inputModalMedico = document.getElementById("inputModalMedico");
        const inputModalUnidad = document.getElementById("inputModalUnidad");
        const inputModalTipo = document.getElementById("inputModalTipo");

        let tablaInstancia = null; 
        let datosCompletosExpediente = [];
        let chartInstancia = null; 

        // Carga de datos modal
        async function cargarSelectsModal() {
            try {
                const response = await fetch('backend_consulta_expediente.php?accion=cargar_catalogos');
                const datos = await response.json();
                
                const selectMedico = document.getElementById('inputModalMedico');
                selectMedico.innerHTML = '<option value="" disabled selected>Seleccione un médico...</option>';
                datos.medicos.forEach(medico => {
                    const opcion = document.createElement('option');
                    opcion.value = medico.idPersonal;
                    opcion.textContent = medico.nombre_completo;
                    selectMedico.appendChild(opcion);
                });

                const selectUnidad = document.getElementById('inputModalUnidad');
                selectUnidad.innerHTML = '<option value="" disabled selected>Seleccione una unidad...</option>';
                datos.unidades.forEach(unidad => {
                    const opcion = document.createElement('option');
                    opcion.value = unidad.idUnidad;
                    opcion.textContent = unidad.nombre;
                    selectUnidad.appendChild(opcion);
                });

                const resTipos = await fetch('backend_catalogos.php?tabla=cat_tipo_consulta');
                const datosTipos = await resTipos.json();

                const selectTipo = document.getElementById('inputModalTipo');
                selectTipo.innerHTML = '<option value="" disabled selected>Selecciona una opción:</option>';
                if (!datosTipos.error) {
                    datosTipos.forEach(item => {
                        const opcion = document.createElement('option');
                        opcion.value = item.id;
                        opcion.textContent = item.valor;
                        selectTipo.appendChild(opcion);
                    });
                }
            } catch (error) {
                console.error("Error al cargar los catálogos:", error);
            }
        }
        
        // Cargar datos iniciales
        async function cargarDatosIniciales() {
            if (tablaInstancia !== null) {
                tablaInstancia.destroy();
                tablaInstancia = null;
            }
            cuerpoTabla.innerHTML = "<tr><td colspan='9' style='text-align:center'>Cargando base de datos...</td></tr>";

            try {
                const response = await fetch('backend_consulta_expediente.php');
                const datos = await response.json();
                
                if(datos.error) { alert(datos.error); return; }
                datosCompletosExpediente = datos; 
                renderizar(datos);
            } catch (error) {
                cuerpoTabla.innerHTML = "<tr><td colspan='9' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        // Renderizar tabla
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='9' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach((item, index) => {
                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><b>${item.idConsulta}</b></td>
                        <td>${item.nombre_paciente}</td>
                        <td style="font-family: monospace;">${item.curp}</td>
                        <td>${item.medico_que_atendio}</td>
                        <td>${item.cedula_medico}</td>
                        <td>${item.unidad_medica}</td>
                        <td><span style="background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-size: 0.9em;">${item.tipo_consulta}</span></td>
                        <td>${item.fecha_consulta}</td>
                        <td style="white-space: nowrap;">
                            <button class="btn-edit" style="background-color: #0d6efd; color: white; padding: 4px 8px; border: none; border-radius: 4px; cursor: pointer;" onclick="generarPDFIndividual(${index})" title="Generar Nota de Esta Consulta">PDF</button>
                            <button class="btn-edit" style="background-color: #198754; color: white; padding: 4px 8px; border: none; border-radius: 4px; cursor: pointer;" onclick="generarHistoriaClinica('${item.curp}')" title="Generar Historia Clínica Completa">Historial</button>
                            <button class="btn-edit" onclick="abrirModal(${item.idConsulta}, ${item.idPersonal}, ${item.idUnidad}, ${item.idTipoConsulta || 'null'})">Editar</button>
                            <button class="btn-del" onclick="eliminarRegistro(${item.idConsulta})">Borrar</button>
                        </td>
                    </tr>
                `;
            });

            tablaInstancia = $('#tablaExpediente').DataTable({
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
                        title: 'Reporte de Asesorías Clínicas - ITZAM',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7] 
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

        // Crear gráfica reporte
        async function generarGraficaVitales(historial) {
            return new Promise((resolve) => {
                // Organizar cronologicamente
                const cronologico = [...historial].reverse();

                const fechas = cronologico.map(c => c.fecha_consulta);
                const pesos = cronologico.map(c => c.peso ? parseFloat(c.peso) : null);
                
                const sistolica = cronologico.map(c => {
                    if(!c.presion_arte) return null;
                    const partes = c.presion_arte.split('/');
                    return partes[0] ? parseInt(partes[0]) : null;
                });
                const diastolica = cronologico.map(c => {
                    if(!c.presion_arte) return null;
                    const partes = c.presion_arte.split('/');
                    return partes[1] ? parseInt(partes[1]) : null;
                });

                const ctx = document.getElementById('graficaOcultaVitales').getContext('2d');
                
                if (chartInstancia) { chartInstancia.destroy(); }

                chartInstancia = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: fechas,
                        datasets: [
                            { label: 'Sistólica (mmHg)', data: sistolica, borderColor: '#dc3545', backgroundColor: '#dc3545', fill: false, tension: 0.3 },
                            { label: 'Diastólica (mmHg)', data: diastolica, borderColor: '#0d6efd', backgroundColor: '#0d6efd', fill: false, tension: 0.3 },
                            { label: 'Peso (kg)', data: pesos, borderColor: '#198754', backgroundColor: '#198754', fill: false, tension: 0.3, yAxisID: 'yPeso' }
                        ]
                    },
                    options: {
                        animation: false, 
                        responsive: false,
                        plugins: {
                            legend: { position: 'top' },
                            title: { display: true, text: 'Curva de Tendencias de Signos Vitales', font: { size: 16 } }
                        },
                        scales: {
                            y: { title: { display: true, text: 'Presión Arterial (mmHg)' }, min: 50, max: 200 },
                            yPeso: { position: 'right', title: { display: true, text: 'Peso (kg)' }, grid: { drawOnChartArea: false } }
                        }
                    }
                });

                setTimeout(() => {
                    const imagenBase64 = document.getElementById('graficaOcultaVitales').toDataURL('image/png', 1.0);
                    resolve(imagenBase64);
                }, 100);
            });
        }

        // Reporte médico individual
        function generarPDFIndividual(index) {
            const info = datosCompletosExpediente[index];
            const docDefinition = {
                content: [
                    {
                        columns: [
                            { image: typeof logoItzamBase64 !== 'undefined' ? logoItzamBase64 : '', width: 50 }, 
                            { text: 'Nota Médica de Evolución - Sistema ITZAM\n', style: 'header', alignment: 'right', margin: [0, 10, 0, 0] }
                        ],
                        margin: [0, 0, 0, 20]
                    },
                    { text: `Fecha de Consulta: ${info.fecha_consulta}`, alignment: 'right', margin: [0, 0, 0, 20] },
                    { text: 'Datos del Paciente', style: 'subheader' },
                    {
                        table: { widths: ['*', '*'], body: [ [{ text: 'Nombre:', bold: true, fillColor: '#f2f2f2' }, info.nombre_paciente], [{ text: 'CURP:', bold: true, fillColor: '#f2f2f2' }, info.curp] ] },
                        layout: 'lightHorizontalLines', margin: [0, 0, 0, 20]
                    },
                    { text: 'Datos Generales de la Consulta', style: 'subheader' },
                    {
                        table: { widths: ['*', '*'], body: [ [{ text: 'Folio de Consulta:', bold: true, fillColor: '#f2f2f2' }, info.idConsulta], [{ text: 'Tipo de Atención:', bold: true, fillColor: '#f2f2f2' }, info.tipo_consulta], [{ text: 'Unidad Médica:', bold: true, fillColor: '#f2f2f2' }, info.unidad_medica], [{ text: 'Médico Tratante:', bold: true, fillColor: '#f2f2f2' }, `${info.medico_que_atendio} (Cédula: ${info.cedula_medico})`] ] },
                        layout: 'lightHorizontalLines', margin: [0, 0, 0, 20]
                    },
                    { text: 'Evaluación Clínica', style: 'subheader' },
                    { text: 'Sintomatología / Motivo de Consulta:', style: 'tituloSeccion' },
                    { text: info.sintomas || 'Sin registro', margin: [0, 0, 0, 15] },
                    { text: 'Diagnóstico Médico:', style: 'tituloSeccion' },
                    { text: info.diagnostico || 'Sin registro', margin: [0, 0, 0, 15] },
                    { text: 'Tratamiento y Notas:', style: 'tituloSeccion' },
                    { text: info.tratamiento || 'Sin registro', margin: [0, 0, 0, 30] },
                    { text: '______________________________________________', alignment: 'center', margin: [0, 40, 0, 5] },
                    { text: `Dr(a). ${info.medico_que_atendio}`, alignment: 'center', bold: true },
                    { text: `Cédula Profesional: ${info.cedula_medico}`, alignment: 'center', fontSize: 10 }
                ],
                styles: { header: { fontSize: 16, bold: true, color: '#084298' }, subheader: { fontSize: 14, bold: true, color: '#495057', margin: [0, 10, 0, 5], decoration: 'underline' }, tituloSeccion: { fontSize: 11, bold: true, margin: [0, 5, 0, 2], color: '#212529' } },
                defaultStyle: { fontSize: 10 }
            };
            pdfMake.createPdf(docDefinition).download(`NotaMedica_${info.curp}_${info.fecha_consulta}.pdf`);
        }

        // Tabla de expedientes
        async function generarHistoriaClinica(curpPaciente) {
            console.log("Extrayendo expediente profundo y generando gráfica...");

            try {
                const response = await fetch(`backend_consulta_expediente.php?accion=obtener_historial&curp=${curpPaciente}`);
                const data = await response.json();

                if (data.error) { alert("Atención: No se pudo obtener el historial: " + data.error); return; }

                const paciente = data.paciente;
                const historial = data.historial;
                
                if (!paciente) {
                    alert("Error crítico: El servidor no devolvió los datos del paciente.");
                    return;
                }
                
                const nombreCompleto = `${paciente.nombre} ${paciente.apellido_p} ${paciente.apellido_m || ''}`.trim();
                const horaImpresion = new Date().toLocaleString('es-MX', { timeZone: 'America/Mexico_City' });

                const imagenGrafica = await generarGraficaVitales(historial);

                // Dashboard con gráfica y logos
                let contenidoPDF = [
                    {
                        columns: [
                            { image: typeof logoItzamBase64 !== 'undefined' ? logoItzamBase64 : '', width: 60 },
                            {
                                text: [
                                    { text: 'ITZAM - Inteligencia Médica Integrada\n', fontSize: 16, bold: true, color: '#198754' },
                                    { text: 'Expediente Clínico Electrónico Longitudinal', fontSize: 11, color: '#6c757d' }
                                ], alignment: 'right', margin: [0, 10, 0, 0]
                            }
                        ], margin: [0, 0, 0, 20]
                    },
                    {
                        table: {
                            widths: ['*', '*', '*'],
                            body: [
                                [
                                    { text: `Paciente: ${nombreCompleto}\nCURP: ${curpPaciente}\nGénero: ${paciente.genero}`, margin: [5, 5, 5, 5] },
                                    { text: `Alergias Conocidas:\n${paciente.alergias || 'Ninguna reportada / Sin registro'}`, margin: [5, 5, 5, 5], fillColor: '#fff3cd', color: '#854001' },
                                    { text: `Antecedentes Médicos:\n${paciente.antecedentes || 'Ninguno reportado / Sin registro'}`, margin: [5, 5, 5, 5], fillColor: '#f8d7da', color: '#842029' }
                                ]
                            ]
                        }, margin: [0, 0, 0, 15]
                    },
                    
                    { text: 'Análisis de Tendencias Clínicas', style: 'subheader', margin: [0, 10, 0, 10] },
                    { image: imagenGrafica, width: 500, alignment: 'center', margin: [0, 0, 0, 25] },

                    // Salto de página
                    { text: `Desglose Clínico Detallado (${historial.length} consultas registradas)`, style: 'headerTablas', pageBreak: 'before', margin: [0, 0, 0, 15] }
                ];

                // Tabla de expedientes
                let cuerpoTablaHistorial = [
                    // Cabecera de la tabla
                    [
                        { text: 'Fecha / Atención', style: 'tablaHeader' },
                        { text: 'Médico / Signos Vitales', style: 'tablaHeader' },
                        { text: 'Evaluación y Tratamiento', style: 'tablaHeader' }
                    ]
                ];

                historial.forEach((consulta) => {
                    let signosVitalesTexto = `Peso: ${consulta.peso || '--'} kg\nPA: ${consulta.presion_arte || '--'} mmHg\nTemp: ${consulta.temperatura || '--'} °C\nFC: ${consulta.freq_card || '--'} lpm\nSpO2: ${consulta.sat_oxigeno || '--'} %`;

                    cuerpoTablaHistorial.push([
                        // Columna 1: Fecha y tipo
                        { text: `Fecha: ${consulta.fecha_consulta}\n\nUnidad: ${consulta.unidad_medica}\nTipo: ${consulta.tipo_consulta}`, fontSize: 9, margin: [0, 5, 0, 5] },
                        
                        // Columna 2: Médico y signos
                        { text: `Médico: ${consulta.medico}\n\nSignos Vitales:\n${signosVitalesTexto}`, fontSize: 9, margin: [0, 5, 0, 5] },
                        
                        // Columna 3: Datos clínicos
                        { 
                            text: [
                                { text: 'Sintomatología: ', bold: true, color: '#084298' }, `${consulta.sintomas || 'Sin registro'}\n\n`,
                                { text: 'Diagnóstico: ', bold: true, color: '#198754' }, `${consulta.diagnostico || 'Sin registro'}\n\n`,
                                { text: 'Tratamiento/Notas: ', bold: true, color: '#212529' }, `${consulta.tratamiento || 'Sin registro'}`
                            ], 
                            fontSize: 9, 
                            margin: [0, 5, 0, 5] 
                        }
                    ]);
                });

                // Añadimos la tabla al PDF
                contenidoPDF.push({
                    table: {
                        headerRows: 1,
                        widths: ['20%', '25%', '55%'], // Ajuste de columnas
                        body: cuerpoTablaHistorial
                    },
                    layout: {
                        // Diseño de tabla
                        hLineWidth: function (i, node) { return 1; },
                        vLineWidth: function (i, node) { return 0; },
                        hLineColor: function (i, node) { return '#dee2e6'; },
                        paddingTop: function(i, node) { return 8; },
                        paddingBottom: function(i, node) { return 8; }
                    }
                });

                //Marca de agua
                const docDefinition = {
                    content: contenidoPDF,
                    watermark: { text: 'DOCUMENTO CONFIDENCIAL', color: 'gray', opacity: 0.1, bold: true, italics: false },
                    footer: function(currentPage, pageCount) {
                        return {
                            //Pie de pagina
                            columns: [
                                { text: `Generado por ITZAM el ${horaImpresion}`, fontSize: 8, color: '#aaaaaa', margin: [30, 0, 0, 0] },
                                { text: `Página ${currentPage} de ${pageCount}`, alignment: 'right', fontSize: 9, bold: true, color: '#198754', margin: [0, 0, 30, 0] }
                            ], margin: [0, 10, 0, 0]
                        };
                    },
                    //Estilos
                    styles: {
                        header: { fontSize: 16, bold: true, color: '#198754', alignment: 'center', margin: [0, 0, 0, 15] },
                        headerTablas: { fontSize: 16, bold: true, color: '#084298', alignment: 'left' },
                        subheader: { fontSize: 14, bold: true, color: '#495057', margin: [0, 10, 0, 5], decoration: 'underline' },
                        tablaHeader: { fontSize: 11, bold: true, color: '#ffffff', fillColor: '#084298', alignment: 'center', margin: [0, 5, 0, 5] }
                    },
                    defaultStyle: { fontSize: 10 },
                    pageMargins: [30, 30, 30, 40] 
                };

                pdfMake.createPdf(docDefinition).download(`Expediente_Longitudinal_${curpPaciente}.pdf`);

            } catch (error) {
                console.error(error);
                alert("Error al generar el documento PDF.");
            }
        }

        // Eliminar registro
        async function eliminarRegistro(idConsulta) {
            if(!confirm("¿Confirma que desea eliminar este registro permanentemente?")) return;
            try {
                const response = await fetch('backend_consulta_expediente.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'eliminar', idConsulta: idConsulta })
                });
                const res = await response.json();
                if(res.estatus === 'exito') { alert("Éxito: " + res.mensaje); cargarDatosIniciales(); } 
                else { alert("Atención: " + res.mensaje); }
            } catch (error) { alert("Error al eliminar"); }
        }

        // Editar registro
        function abrirModal(idConsulta, idPersonal, idUnidad, idTipoConsulta) {
            inputModalId.value = idConsulta; inputModalMedico.value = idPersonal;
            inputModalUnidad.value = idUnidad; inputModalTipo.value = idTipoConsulta; 
            modal.classList.add("show");
        }

        function cerrarModal() { modal.classList.remove("show"); }

        async function guardarCambios() {
            const idConsulta = inputModalId.value; const idPersonal = inputModalMedico.value;
            const idUnidad = inputModalUnidad.value; const idTipoConsulta = inputModalTipo.value; 

            if(!idTipoConsulta || !idPersonal || !idUnidad) { alert("Por favor, llena todos los campos del formulario."); return; }

            try {
                const response = await fetch('backend_consulta_expediente.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'editar', idConsulta: idConsulta, idPersonal: idPersonal, idUnidad: idUnidad, idTipoConsulta: idTipoConsulta })
                });
                const res = await response.json();
                
                if(res.estatus === 'error') { alert("Atención:\n\n" + res.mensaje); } 
                else if (res.estatus === 'exito') { alert("Éxito: " + res.mensaje); cerrarModal(); cargarDatosIniciales(); }
            } catch (error) { alert("Error al guardar cambios"); }
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarSelectsModal();
            cargarDatosIniciales();
        });

        window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
    </script>
    <script src="Scripts/js/timeout.js"></script>
    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>