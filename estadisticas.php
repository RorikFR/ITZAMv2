<!doctype html>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<html lang="es">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width,initial-scale=1" />
		<title>Sistema ITZAM — Estadísticas</title>
		<link rel="stylesheet" href="styles.css" />
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

            <!-- Dropdown menu for Asesorías -->
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Asesorías</a>
            <div class="dropdown-content">
                <a href="mis_asesorias.php">Mis asesorías</a>
                <a href="nueva_asesoria.php">Registrar asesoría</a>
            </div>  
            </li>

            <!-- Dropdown menu for Consultas médicas -->
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Consultas médicas</a>
            <div class="dropdown-content">
                <a href="buscar_consulta.php">Buscar consulta</a>
                <a href="nueva_consulta.php">Registrar consulta</a>
            </div>
            </li>

            <li><a href="estadisticas.php">Estadísticas</a></li>

            <!-- Dropdown menu for Estudios -->
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Laboratorios</a>
            <div class="dropdown-content">
                <a href="consulta_orden_laboratorio.php">Buscar orden de laboratorio</a>
                <a href="nueva_orden_laboratorio.php">Crear orden de laboratorio</a>
            </div>
            </li>

            <!-- Dropdown menu for Inventario -->
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Inventario</a>
            <div class="dropdown-content">
                <a href="consulta_inventario.php">Buscar en inventario</a>
                <a href="nueva_compra_med.php">Registrar compra de medicamentos</a>
                <a href="nueva_compra_insumo.php">Registrar compra de insumos</a>
                <a href="nueva_compra_equipo.php">Registrar compra de equipo médico</a>
            </div>
            </li>

            <!-- Dropdown menu for Pacientes -->
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Pacientes</a>
                <div class="dropdown-content">
                    <a href="consulta_expediente.php">Consultar historia clínica</a>
                    <a href="consulta_paciente.php">Consultar paciente</a>
                    <a href="nuevo_paciente.php">Registrar paciente</a>
                </div>
            </li>

            <!-- Dropdown menu for Personal de salud -->
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Personal de salud</a>
            <div class="dropdown-content">
                <a href="consulta_personal.php">Consultar personal</a>
                <a href="nuevo_personal.php">Registrar personal</a>
            </div>
            </li>

            <!-- Dropdown menu for Recetas -->
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Recetas</a>
            <div class="dropdown-content">
                <a href="consulta_receta.php">Consultar receta</a>
                <a href="nueva_receta.php">Registrar receta</a>
            </div>
            </li>

            <!-- Dropdown menu for Unidades médicas -->
            <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Unidades médicas</a>
            <div class="dropdown-content">
                <a href="consulta_unidad.php">Consultar unidad médica</a>
                <a href="nueva_unidad.php">Registrar unidad médica</a>
            </div>
            </li>

        </ul>
    </nav>

		<div class="title-box">
            <h1>Estadísticas Operativas y Epidemiológicas</h1>
        </div>

        <div class="graph-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; padding: 20px;">
            
            <div id="tarjeta-unidad" class="chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <p class="card-subtitle" style="font-weight: bold; margin-bottom: 15px; color: #333;">Consultas médicas por unidad médica</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartUnidad"></canvas>
                </div>
                
                <div style="text-align: right; margin-top: 15px;">
                    <button class="btn-descarga" onclick="descargarPDF('tarjeta-unidad', 'Reporte_Consultas_Por_Unidad')" style="padding: 8px 15px; background-color: #0056b3; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        📄 Descargar PDF
                    </button>
                </div>
            </div>
            
            <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <p class="card-subtitle" style="font-weight: bold; margin-bottom: 15px; color: #333;">Consultas médicas por tipo de atención</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartCategoria"></canvas>
                </div>
            </div>
            
            <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <p class="card-subtitle" style="font-weight: bold; margin-bottom: 15px; color: #333;">Top 5: Diagnósticos de mayor incidencia</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartIncidencia"></canvas>
                </div>
            </div>
            
            <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <p class="card-subtitle" style="font-weight: bold; margin-bottom: 15px; color: #333;">Productividad por personal médico</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartPersonal"></canvas>
                </div>
            </div>
            
            <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <p class="card-subtitle" style="font-weight: bold; margin-bottom: 15px; color: #333;">Distribución de edades en consulta</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartEdad"></canvas>
                </div>
            </div>
            
            <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <p class="card-subtitle" style="font-weight: bold; margin-bottom: 15px; color: #333;">Top 10 Medicamentos en stock</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartInventario"></canvas>
                </div>
            </div>

            <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <p class="card-subtitle" style="font-weight: bold; margin-bottom: 15px; color: #333;">Top 10 Insumos médicos en stock</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartInsumos"></canvas>
                </div>
            </div>

            <div class="chart-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <p class="card-subtitle" style="font-weight: bold; margin-bottom: 15px; color: #333;">Top 10 Equipo médico asignado</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartEquipo"></canvas>
                </div>
            </div>

        </div>
        
        <footer class="bottombar">© 2026 ITZAM</footer>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', async () => {
                try {
                    const response = await fetch('backend_estadisticas.php');
                    const data = await response.json();

                    if(data.error) {
                        alert("Error al cargar las estadísticas: " + data.error);
                        return;
                    }

                    // Paleta de colores más institucional (Salud)
                    const colorAzul = 'rgba(0, 86, 179, 0.7)';
                    const colorVerde = 'rgba(40, 167, 69, 0.7)';
                    const colorRojo = 'rgba(220, 53, 69, 0.7)';
                    const paletaPastel = ['#0056b3', '#17a2b8', '#20c997', '#ffc107', '#fd7e14', '#dc3545'];

// Opciones base para que no se deformen
                    const opcionesComunes = {
                        responsive: true,
                        maintainAspectRatio: false
                    };

                    // --- NUEVO: OPCIONES SEPARADAS POR TIPO DE BARRA ---
                    
                    // Opciones para Barras VERTICALES (Inventario)
                    const opcionesVerticales = {
                        ...opcionesComunes,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                ticks: { precision: 0 } // Números en Y
                            },
                            x: {
                                ticks: {
                                    callback: function(value) {
                                        const label = this.getLabelForValue(value);
                                        return label.length > 15 ? label.substring(0, 15) + '...' : label;
                                    }
                                }
                            }
                        }
                    };

                    // Opciones para Barras HORIZONTALES (Unidades, Incidencias, Personal)
                    const opcionesHorizontales = {
                        ...opcionesComunes,
                        indexAxis: 'y', // La instrucción que acuesta la gráfica
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { 
                                beginAtZero: true,
                                ticks: { precision: 0 } // Ahora los números van en X
                            },
                            y: {
                                ticks: {
                                    // Al estar horizontal hay más espacio, cortamos a los 25 caracteres
                                    callback: function(value) {
                                        const label = this.getLabelForValue(value);
                                        return label.length > 35 ? label.substring(0, 25) + '...' : label;
                                    }
                                }
                            }
                        }
                    };

                    // ==========================================
                    // 1. Consultas por Unidad (AHORA ES HORIZONTAL)
                    // ==========================================
                    new Chart(document.getElementById('chartUnidad'), {
                        type: 'bar',
                        data: {
                            labels: data.unidades.labels,
                            datasets: [{
                                data: data.unidades.valores,
                                backgroundColor: colorAzul,
                                borderRadius: 4
                            }]
                        },
                        options: opcionesHorizontales
                    });

                    // ==========================================
                    // 2. Consultas por Categoría (Gráfica de Pastel)
                    // ==========================================
                    new Chart(document.getElementById('chartCategoria'), {
                        type: 'pie',
                        data: {
                            labels: data.categorias.labels,
                            datasets: [{
                                data: data.categorias.valores,
                                backgroundColor: paletaPastel,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            ...opcionesComunes,
                            plugins: {
                                legend: { position: 'bottom' }
                            }
                        }
                    });

                    // ==========================================
                    // 3. Top 5 Incidencias (Ranking - HORIZONTAL)
                    // ==========================================
                    new Chart(document.getElementById('chartIncidencia'), {
                        type: 'bar',
                        data: {
                            labels: data.incidencia.labels,
                            datasets: [{
                                data: data.incidencia.valores,
                                backgroundColor: colorRojo,
                                borderRadius: 4
                            }]
                        },
                        options: opcionesHorizontales
                    });

                    // ==========================================
                    // 4. Productividad por personal (AHORA ES HORIZONTAL)
                    // ==========================================
                    new Chart(document.getElementById('chartPersonal'), {
                        type: 'bar',
                        data: {
                            labels: data.personal.labels,
                            datasets: [{
                                data: data.personal.valores,
                                backgroundColor: colorVerde,
                                borderRadius: 4
                            }]
                        },
                        options: opcionesHorizontales
                    });

                    // ==========================================
                    // 5. Edades promedio (Gráfica de Dona)
                    // ==========================================
                    new Chart(document.getElementById('chartEdad'), {
                        type: 'doughnut',
                        data: {
                            labels: data.edades.labels,
                            datasets: [{
                                data: data.edades.valores,
                                backgroundColor: ['#6f42c1', '#20c997', '#fd7e14'],
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            ...opcionesComunes,
                            plugins: {
                                legend: { position: 'bottom' }
                            },
                            cutout: '65%'
                        }
                    });

                    // ==========================================
                    // 6. Top Medicamentos en Stock (AHORA ES HORIZONTAL)
                    // ==========================================
                    new Chart(document.getElementById('chartInventario'), {
                        type: 'bar', // Sigue siendo bar, pero las opciones lo acuestan
                        data: {
                            labels: data.inventario.labels,
                            datasets: [{
                                data: data.inventario.valores,
                                backgroundColor: '#17a2b8', // Color Cyan médico
                                borderRadius: 4
                            }]
                        },
                        options: opcionesHorizontales // ¡Usamos nuestra magia horizontal aquí!
                    });

                    // ==========================================
                    // 7. Top Insumos en Stock (HORIZONTAL)
                    // ==========================================
                    new Chart(document.getElementById('chartInsumos'), {
                        type: 'bar',
                        data: {
                            labels: data.insumos.labels,
                            datasets: [{
                                data: data.insumos.valores,
                                backgroundColor: '#fd7e14', // Naranja clínico
                                borderRadius: 4
                            }]
                        },
                        options: opcionesHorizontales
                    });

                    // ==========================================
                    // 8. Top Equipo Médico (HORIZONTAL)
                    // ==========================================
                    new Chart(document.getElementById('chartEquipo'), {
                        type: 'bar',
                        data: {
                            labels: data.equipo.labels,
                            datasets: [{
                                data: data.equipo.valores,
                                backgroundColor: '#6c757d', // Gris oscuro / metálico
                                borderRadius: 4
                            }]
                        },
                        options: opcionesHorizontales
                    });

                } catch (error) {
                    console.error("Error procesando los gráficos:", error);
                }
            });


// Función para generar y descargar el PDF de una gráfica específica
            function descargarPDF(idTarjeta, nombreArchivo) {
                const elemento = document.getElementById(idTarjeta);
                const boton = elemento.querySelector('.btn-descarga');

                // 1. Ocultamos el botón para que no salga en la impresión
                boton.style.display = 'none';

                // 2. Configuramos la calidad y formato del PDF
                const opciones = {
                    margin:       10, // Margen en milímetros
                    filename:     nombreArchivo + '.pdf',
                    image:        { type: 'jpeg', quality: 1 }, // Máxima calidad de imagen
                    html2canvas:  { scale: 2, useCORS: true }, // Escala 2 para que no se vea borroso al hacer zoom
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' } // Formato horizontal (ideal para gráficas)
                };

                // 3. Generamos el PDF
                html2pdf().set(opciones).from(elemento).save().then(() => {
                    // 4. Volvemos a mostrar el botón una vez que la descarga termina
                    boton.style.display = 'inline-block';
                });
            }
        </script>