<?php
//Validaciones de seguridad e inactividad
require 'inactive.php';      
require 'autorizacion.php';   

//RBAC
requerir_roles(['Administrativo', 'Médico']);

require 'db_conn.php';

require 'header.php';

// Definimos lista de módulos permitidos
$modulos_permitidos = ['unidades', 'categorias', 'incidencia', 'personal', 'inventario', 'insumos', 'equipo', 'edades'];

// Recibimos el parámetro
$modulo_solicitado = $_GET['modulo'] ?? 'unidades';

// Verificamos solicitud, si no, enviar a unidades
if (in_array($modulo_solicitado, $modulos_permitidos)) {
    $modulo = $modulo_solicitado;
} else {
    $modulo = 'unidades'; 
}

// Variables por defecto
$titulo = "Detalle de Estadísticas";
$chartType = "bar";
$horizontal = true;
$col1 = "Etiqueta";
$col2 = "Valor";
$sql = "";

// Definimos la consulta y configuración según el módulo
switch ($modulo) {
    case 'unidades':
        $titulo = "Consultas por Unidad Médica";
        $col1 = "Unidad Médica"; $col2 = "Total de Consultas";
        $sql = "SELECT u.nombre AS etiqueta, COUNT(c.idConsulta) AS valor FROM registro_consultas c INNER JOIN registro_unidad u ON c.idUnidad = u.idUnidad GROUP BY u.idUnidad ORDER BY valor DESC";
        break;
        
    case 'categorias':
        $titulo = "Consultas por Tipo de Atención";
        $col1 = "Tipo de Atención"; $col2 = "Total de Consultas";
        $chartType = 'pie'; $horizontal = false;
        $sql = "SELECT tc.nombre_tipo AS etiqueta, COUNT(c.idConsulta) AS valor FROM registro_consultas c INNER JOIN cat_tipo_consulta tc ON c.idTipoConsulta = tc.idTipoConsulta GROUP BY tc.nombre_tipo ORDER BY valor DESC";
        break;
        
    case 'incidencia':
        $titulo = "Diagnósticos de Mayor Incidencia";
        $col1 = "Diagnóstico Médico"; $col2 = "Casos Registrados";
        $sql = "SELECT diagnostico AS etiqueta, COUNT(idConsulta) AS valor FROM registro_consultas GROUP BY diagnostico ORDER BY valor DESC";
        break;
        
    case 'personal':
        $titulo = "Productividad por Personal Médico";
        $col1 = "Médico / Especialista"; $col2 = "Pacientes Atendidos";
        $sql = "SELECT CONCAT_WS(' ', p.nombre, p.apellido_p, p.apellido_m) AS etiqueta, COUNT(c.idConsulta) AS valor FROM registro_consultas c INNER JOIN registro_personal p ON c.idPersonal = p.idPersonal GROUP BY p.idPersonal ORDER BY valor DESC";
        break;
        
    case 'inventario':
        $titulo = "Detalle de Inventario de Medicamentos";
        $col1 = "Medicamento (Ubicación)"; $col2 = "Unidades en Stock";
        $sql = "SELECT CONCAT(cat.nombre, ' (', u.nombre, ')') AS etiqueta, SUM(m.cantidad) AS valor 
                FROM inventario_medicamentos m 
                INNER JOIN cat_medicamentos cat ON m.idCatalogoMed = cat.idCatalogoMed 
                INNER JOIN registro_unidad u ON m.idUnidad = u.idUnidad 
                GROUP BY cat.nombre, u.nombre 
                ORDER BY valor DESC";
        break;
        
    case 'insumos':
        $titulo = "Detalle de Insumos Médicos";
        $col1 = "Insumo (Ubicación)"; $col2 = "Unidades en Stock";
        $sql = "SELECT CONCAT(cat.nombre, ' (', u.nombre, ')') AS etiqueta, SUM(i.cantidad) AS valor 
                FROM inventario_insumos i 
                INNER JOIN cat_insumos cat ON i.idCatalogoInsumo = cat.idCatalogoInsumo 
                INNER JOIN registro_unidad u ON i.idUnidad = u.idUnidad 
                GROUP BY cat.nombre, u.nombre 
                ORDER BY valor DESC";
        break;
        
    case 'equipo':
        $titulo = "Detalle de Equipo Médico Asignado";
        $col1 = "Equipo (Ubicación)"; $col2 = "Cantidad";
        $sql = "SELECT CONCAT(cat.nombre, ' (', u.nombre, ')') AS etiqueta, SUM(e.cantidad) AS valor 
                FROM inventario_equipo e 
                INNER JOIN cat_equipo cat ON e.idCatalogoEquipo = cat.idCatalogoEquipo 
                INNER JOIN registro_unidad u ON e.idUnidad = u.idUnidad 
                GROUP BY cat.nombre, u.nombre 
                ORDER BY valor DESC";
        break;
        
    case 'edades':
        $titulo = "Distribución de Edades en Consulta";
        $col1 = "Rango de Edad"; $col2 = "Total de Consultas";
        $chartType = 'doughnut'; $horizontal = false;
        $sql = "SELECT CASE WHEN ROUND(DATEDIFF(c.fecha_consulta, p.fecha_nac) / 365.25) < 18 THEN 'Menores de 18' WHEN ROUND(DATEDIFF(c.fecha_consulta, p.fecha_nac) / 365.25) BETWEEN 18 AND 59 THEN 'Adultos (18-59)' ELSE 'Adultos Mayores (60+)' END AS etiqueta, COUNT(c.idConsulta) AS valor FROM registro_consultas c INNER JOIN registro_paciente p ON c.idPaciente = p.idPaciente GROUP BY etiqueta";
        break;
        
    default:
        die("Módulo no reconocido.");
}

//Ejecutar consulta
$tablaData = [];
$chartLabels = [];
$chartValues = [];

try {
    $stmt = $pdo->query($sql);
    $tablaData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Llenar la gráfica con los primeros 15 registros
    foreach ($tablaData as $index => $row) {
        if ($index < 15) {
            $chartLabels[] = $row['etiqueta'];
            $chartValues[] = (int)$row['valor']; // Forzar uso de enteros
        }
    }
} catch (PDOException $e) {
    die("Error interno de la base de datos.");
}
?>


<div class="container-detalle" id="area-impresion">
        <div class="header-acciones">
            <a href="estadisticas.php" class="btn-regresar">← Regresar al Dashboard</a>
            
            <button id="btn-exportar-pdf" class="btn-exportar-pdf">
                Descargar Reporte Completo (PDF)
            </button>
        </div>
        
        <h1><?php echo $titulo; ?></h1>
        <hr>

        <div class="seccion-grafica">
            <canvas id="graficaDetalle"></canvas>
        </div>

        <hr>

        <div class="seccion-tabla">
            <h3>Desglose de Datos</h3>
            <table id="tabla-datos" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo $col1; ?></th>
                        <th><?php echo $col2; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $contador = 1;
                    foreach ($tablaData as $fila): 
                    ?>
                        <tr>
                            <td><?php echo $contador++; ?></td>
                            <td><?php echo htmlspecialchars($fila['etiqueta']); ?></td>
                            <td><?php echo number_format($fila['valor']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="bottombar">© 2026 ITZAM</footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializamos la tabla
            const miTabla = $('#tabla-datos').DataTable({
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
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Descargar Excel', className: 'btn-exportar' },
                    { extend: 'csvHtml5', text: '📄 Descargar CSV', className: 'btn-exportar' }
                ],
                pageLength: 15
            });

            // Inicializamos la gráfica 
            const ctx = document.getElementById('graficaDetalle').getContext('2d');
            const labels = <?php echo json_encode($chartLabels); ?>;
            const values = <?php echo json_encode($chartValues); ?>;
            const chartType = '<?php echo $chartType; ?>';
            const horizontal = <?php echo $horizontal ? 'true' : 'false'; ?>;

            const miGrafica = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: '<?php echo $col2; ?>',
                        data: values,
                        backgroundColor: chartType === 'pie' || chartType === 'doughnut' 
                            ? ['#0056b3', '#17a2b8', '#20c997', '#ffc107', '#fd7e14', '#dc3545', '#6f42c1', '#e83e8c', '#28a745', '#007bff'] 
                            : 'rgba(0, 86, 179, 0.7)',
                        borderRadius: chartType === 'bar' ? 4 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: horizontal && chartType === 'bar' ? 'y' : 'x',
                    plugins: { 
                        legend: { display: chartType === 'pie' || chartType === 'doughnut' } 
                    },
                    scales: chartType === 'pie' || chartType === 'doughnut' ? {} : {
                        x: { beginAtZero: true, ticks: { precision: 0 } },
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });

            // Generar reporte PDF
            $('#btn-exportar-pdf').on('click', function() {
                const botonExportar = this;
                botonExportar.innerText = "⏳ Generando reporte...";
                botonExportar.disabled = true;

                miTabla.page.len(-1).draw();

                $('.header-acciones').hide(); 
                $('.dt-buttons').hide(); 
                $('#tabla-datos_filter').hide(); 
                $('#tabla-datos_info').hide(); 
                $('#tabla-datos_paginate').hide(); 

                const elemento = document.getElementById('area-impresion');
                const anchoPantalla = window.innerWidth;

                const opciones = {
                    margin:       10,
                    filename:     'Detalle_Reporte.pdf',
                    image:        { type: 'jpeg', quality: 1 },
                    html2canvas:  { 
                        scale: 2, 
                        useCORS: true,
                        width: 1280, 
                        windowWidth: anchoPantalla
                    },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
                    pagebreak:    { mode: ['css', 'legacy'], avoid: 'tr' } 
                };

                html2pdf().set(opciones).from(elemento).save().then(() => {
                    miTabla.page.len(15).draw(); 
                    
                    $('.header-acciones').show();
                    $('.dt-buttons').show();
                    $('#tabla-datos_filter').show();
                    $('#tabla-datos_info').show();
                    $('#tabla-datos_paginate').show();

                    botonExportar.innerText = "📄 Descargar Reporte Completo (PDF)";
                    botonExportar.disabled = false;
                });
            });
        });
    </script>

    <script src="Scripts/js/timeout.js"></script>
</body>
</html>