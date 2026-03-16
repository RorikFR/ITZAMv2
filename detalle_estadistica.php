<?php
session_start();

require 'db_conn.php';

// 1. Identificamos qué módulo quiere ver el usuario (por defecto 'unidades')
$modulo = $_GET['modulo'] ?? 'unidades';

// Variables por defecto
$titulo = "Detalle de Estadísticas";
$chartType = "bar";
$horizontal = true;
$col1 = "Etiqueta";
$col2 = "Valor";
$sql = "";

// 2. Definimos la consulta y configuración según el módulo
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
        // 🔥 CORRECCIÓN 3FN APLICADA AQUÍ:
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
        $sql = "SELECT CONCAT(m.nombre, ' (', u.nombre, ')') AS etiqueta, SUM(m.cantidad) AS valor FROM inventario_medicamentos m INNER JOIN registro_unidad u ON m.idUnidad = u.idUnidad GROUP BY m.nombre, u.nombre ORDER BY valor DESC";
        break;
    case 'insumos':
        $titulo = "Detalle de Insumos Médicos";
        $col1 = "Insumo (Ubicación)"; $col2 = "Unidades en Stock";
        $sql = "SELECT CONCAT(i.nombre, ' (', u.nombre, ')') AS etiqueta, SUM(i.cantidad) AS valor FROM inventario_insumos i INNER JOIN registro_unidad u ON i.idUnidad = u.idUnidad GROUP BY i.nombre, u.nombre ORDER BY valor DESC";
        break;
    case 'equipo':
        $titulo = "Detalle de Equipo Médico Asignado";
        $col1 = "Equipo (Ubicación)"; $col2 = "Cantidad";
        $sql = "SELECT CONCAT(e.nombre, ' (', u.nombre, ')') AS etiqueta, SUM(e.cantidad) AS valor FROM inventario_equipo e INNER JOIN registro_unidad u ON e.idUnidad = u.idUnidad GROUP BY e.nombre, u.nombre ORDER BY valor DESC";
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

// 3. Ejecutamos la consulta maestra
$tablaData = [];
$chartLabels = [];
$chartValues = [];

try {
    $stmt = $pdo->query($sql);
    $tablaData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Llenamos la gráfica SOLO con los primeros 15 registros para no saturar el canvas
    foreach ($tablaData as $index => $row) {
        if ($index < 15) {
            $chartLabels[] = $row['etiqueta'];
            $chartValues[] = $row['valor'];
        }
    }
} catch (PDOException $e) {
    die("Error de BD: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Sistema ITZAM — <?php echo $titulo; ?></title>
    <link rel="stylesheet" href="styles.css" />
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    
    <style>
        .container-detalle { max-width: 1200px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .btn-regresar { display: inline-block; margin-bottom: 20px; padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn-regresar:hover { background: #5a6268; }
        .seccion-grafica { position: relative; height: 400px; margin-bottom: 40px; }
        .seccion-tabla { margin-top: 30px; }

        /* Evita que los renglones de la tabla se partan a la mitad en el PDF */
        #tabla-datos tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    </style>
</head>
<body>
    <header>
        <div class="topbar-container">
          <div>
            <img class ="logo"src="Assets/itzam_logoV2.png" alt="LOGO" />
          </div>
          
          <div class="topbar-header">Sistema web consulta de información clínica - ITZAM</div>
          
        <div class="user-menu">
            <div class="user-menu">
                <img id="header-user-photo" class="user-photo user-icon" src="<?php echo isset($_SESSION['foto_perfil']) && $_SESSION['foto_perfil'] ? $_SESSION['foto_perfil'] : 'Assets/think.jpg'; ?>" onclick="toggleMenu()">
            </div>
            
            <div class="dropdown-menu" id="userDropdown">
                <p class="user-menu-title" style="font-weight: bold;"><?= htmlspecialchars($_SESSION['nombre_usuario']) ?></p>
                <hr></hr>
                <a class="dropdown-item" href="administracion.php">Administración</a>
                <a class="dropdown-item" href="catalogos.php">Catálogos</a>
                <a class="dropdown-item" href="configuracion_cuenta.php">Configuración</a>
                <a class="dropdown-item" href="logout.php">Cerrar sesión</a>
            </div>
        </div>
    </header>
    
    <nav>   
        <ul>
            <li><a href="home.php" class="active">Inicio</a></li>
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Asesorías</a>
            <div class="dropdown-content">
                <a href="mis_asesorias.php">Mis asesorías</a>
                <a href="nueva_asesoria.php">Registrar asesoría</a>
            </div>  
            </li>
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Consultas médicas</a>
            <div class="dropdown-content">
                <a href="buscar_consulta.php">Buscar consulta</a>
                <a href="nueva_consulta.php">Registrar consulta</a>
            </div>
            </li>
            <li><a href="estadisticas.php">Estadísticas</a></li>
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Laboratorios</a>
            <div class="dropdown-content">
                <a href="consulta_orden_laboratorio.php">Buscar orden de laboratorio</a>
                <a href="nueva_orden_laboratorio.php">Crear orden de laboratorio</a>
            </div>
            </li>
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Inventario</a>
            <div class="dropdown-content">
                <a href="consulta_inventario.php">Buscar en inventario</a>
                <a href="nueva_compra_med.php">Registrar compra de medicamentos</a>
                <a href="nueva_compra_insumo.php">Registrar compra de insumos</a>
                <a href="nueva_compra_equipo.php">Registrar compra de equipo médico</a>
            </div>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Pacientes</a>
                <div class="dropdown-content">
                    <a href="consulta_expediente.php">Consultar historia clínica</a>
                    <a href="consulta_paciente.php">Consultar paciente</a>
                    <a href="nuevo_paciente.php">Registrar paciente</a>
                </div>
            </li>
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Personal de salud</a>
            <div class="dropdown-content">
                <a href="consulta_personal.php">Consultar personal</a>
                <a href="nuevo_personal.php">Registrar personal</a>
            </div>
            </li>
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Recetas</a>
            <div class="dropdown-content">
                <a href="consulta_receta.php">Consultar receta</a>
                <a href="nueva_receta.php">Registrar receta</a>
            </div>
            </li>
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Unidades médicas</a>
            <div class="dropdown-content">
                <a href="consulta_unidad.php">Consultar unidad médica</a>
                <a href="nueva_unidad.php">Registrar unidad médica</a>
            </div>
            </li>
        </ul>
    </nav>

<div class="container-detalle" id="area-impresion">
        <div class="header-acciones" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <a href="estadisticas.php" class="btn-regresar">← Regresar al Dashboard</a>
            
            <button id="btn-exportar-pdf" style="padding: 10px 15px; background-color: #dc3545; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
                📄 Descargar Reporte Completo (PDF)
            </button>
        </div>
        
        <h1><?php echo $titulo; ?></h1>
        <hr>

        <div class="seccion-grafica">
            <canvas id="graficaDetalle"></canvas>
        </div>

        <hr>

        <div class="seccion-tabla">
            <h3>Desglose de Datos en Crudo</h3>
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
            // 1. Inicializamos la tabla
            const miTabla = $('#tabla-datos').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json' },
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Descargar Excel', className: 'btn-exportar' },
                    { extend: 'csvHtml5', text: '📄 Descargar CSV', className: 'btn-exportar' }
                ],
                pageLength: 15
            });

            // 2. Inicializamos la Gráfica (Sintaxis JS corregida)
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

            // 3. LA MAGIA DEL PDF
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
</body>
</html>