<?php
date_default_timezone_set('America/Mexico_City');

//Validación de seguridad e inactividad
require 'inactive.php';       
require 'autorizacion.php';   

//RBAC
requerir_roles(['Administrativo', 'Médico']);

//Menú de navegación dinámico
require 'header.php';
?>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

		<div class="title-box">
            <h1>Estadísticas Operativas y Epidemiológicas</h1>
        </div>

        <div style="text-align: center;">
            <button class="btn-reporte-completo" onclick="descargarReporteCompleto()">
                📊 Descargar Reporte Completo
            </button>
        </div>

        <div id="dashboard-completo" class="graph-container">
            
            <div id="tarjeta-unidad" class="chart-card">
                <p class="card-subtitle">Consultas médicas por unidad médica</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartUnidad"></canvas>
                </div>
                <div class="botones-accion">
                    <a href="detalle_estadistica.php?modulo=unidades" class="btn-detalle">Ver Detalles</a>
                </div>
            </div>
            
            <div id="tarjeta-categoria" class="chart-card">
                <p class="card-subtitle">Consultas médicas por tipo de atención</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartCategoria"></canvas>
                </div>
                <div class="botones-accion">
                    <a href="detalle_estadistica.php?modulo=categorias" class="btn-detalle">Ver Detalles</a>
                </div>
            </div>
            
            <div id="tarjeta-incidencia" class="chart-card">
                <p class="card-subtitle">Top 5: Diagnósticos de mayor incidencia</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartIncidencia"></canvas>
                </div>
                <div class="botones-accion">
                    <a href="detalle_estadistica.php?modulo=incidencia" class="btn-detalle">Ver Detalles</a>
                </div>
            </div>
            
            <div id="tarjeta-personal" class="chart-card">
                <p class="card-subtitle">Productividad por personal médico</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartPersonal"></canvas>
                </div>
                <div class="botones-accion">
                    <a href="detalle_estadistica.php?modulo=personal" class="btn-detalle">Ver Detalles</a>
                </div>
            </div>
            
            <div id="tarjeta-edad" class="chart-card">
                <p class="card-subtitle">Distribución de edades en consulta</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartEdad"></canvas>
                </div>
                <div class="botones-accion">
                    <a href="detalle_estadistica.php?modulo=edades" class="btn-detalle">Ver Detalles</a>
                </div>
            </div>
            
            <div id="tarjeta-inventario" class="chart-card">
                <p class="card-subtitle">Top 10 Medicamentos en stock</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartInventario"></canvas>
                </div>
                <div class="botones-accion">
                    <a href="detalle_estadistica.php?modulo=inventario" class="btn-detalle">Ver Detalles</a>
                </div>
            </div>

            <div id="tarjeta-insumos" class="chart-card">
                <p class="card-subtitle">Top 10 Insumos médicos en stock</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartInsumos"></canvas>
                </div>
                <div class="botones-accion">
                    <a href="detalle_estadistica.php?modulo=insumos" class="btn-detalle">Ver Detalles</a>
                </div>
            </div>

            <div id="tarjeta-equipo" class="chart-card">
                <p class="card-subtitle">Top 10 Equipo médico asignado</p>
                <div style="position: relative; height: 300px;">
                    <canvas id="chartEquipo"></canvas>
                </div>
                <div class="botones-accion">
                    <a href="detalle_estadistica.php?modulo=equipo" class="btn-detalle">Ver Detalles</a>
                </div>
            </div>
        </div>
        
        <footer class="bottombar">© 2026 ITZAM</footer>

        <script>
            document.addEventListener('DOMContentLoaded', async () => {
                try {
                    const response = await fetch('backend_estadisticas.php');
                    const data = await response.json();

                    if(data.error) {
                        alert("Error al cargar las estadísticas: " + data.error);
                        return;
                    }

                    const colorAzul = 'rgba(0, 86, 179, 0.7)';
                    const colorVerde = 'rgba(40, 167, 69, 0.7)';
                    const colorRojo = 'rgba(220, 53, 69, 0.7)';
                    const paletaPastel = ['#0056b3', '#17a2b8', '#20c997', '#ffc107', '#fd7e14', '#dc3545'];

                    const opcionesComunes = { responsive: true, maintainAspectRatio: false };

                    const opcionesVerticales = {
                        ...opcionesComunes,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } },
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

                    const opcionesHorizontales = {
                        ...opcionesComunes,
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, ticks: { precision: 0 } },
                            y: {
                                ticks: {
                                    callback: function(value) {
                                        const label = this.getLabelForValue(value);
                                        return label.length > 35 ? label.substring(0, 35) + '...' : label;
                                    }
                                }
                            }
                        }
                    };

                    new Chart(document.getElementById('chartUnidad'), { type: 'bar', data: { labels: data.unidades.labels, datasets: [{ data: data.unidades.valores, backgroundColor: colorAzul, borderRadius: 4 }] }, options: opcionesHorizontales });
                    new Chart(document.getElementById('chartCategoria'), { type: 'pie', data: { labels: data.categorias.labels, datasets: [{ data: data.categorias.valores, backgroundColor: paletaPastel, hoverOffset: 10 }] }, options: { ...opcionesComunes, plugins: { legend: { position: 'bottom' } } } });
                    new Chart(document.getElementById('chartIncidencia'), { type: 'bar', data: { labels: data.incidencia.labels, datasets: [{ data: data.incidencia.valores, backgroundColor: colorRojo, borderRadius: 4 }] }, options: opcionesHorizontales });
                    new Chart(document.getElementById('chartPersonal'), { type: 'bar', data: { labels: data.personal.labels, datasets: [{ data: data.personal.valores, backgroundColor: colorVerde, borderRadius: 4 }] }, options: opcionesHorizontales });
                    new Chart(document.getElementById('chartEdad'), { type: 'doughnut', data: { labels: data.edades.labels, datasets: [{ data: data.edades.valores, backgroundColor: ['#6f42c1', '#20c997', '#fd7e14'], hoverOffset: 10 }] }, options: { ...opcionesComunes, plugins: { legend: { position: 'bottom' } }, cutout: '65%' } });
                    new Chart(document.getElementById('chartInventario'), { type: 'bar', data: { labels: data.inventario.labels, datasets: [{ data: data.inventario.valores, backgroundColor: '#17a2b8', borderRadius: 4 }] }, options: opcionesHorizontales });
                    new Chart(document.getElementById('chartInsumos'), { type: 'bar', data: { labels: data.insumos.labels, datasets: [{ data: data.insumos.valores, backgroundColor: '#fd7e14', borderRadius: 4 }] }, options: opcionesHorizontales });
                    new Chart(document.getElementById('chartEquipo'), { type: 'bar', data: { labels: data.equipo.labels, datasets: [{ data: data.equipo.valores, backgroundColor: '#6c757d', borderRadius: 4 }] }, options: opcionesHorizontales });

                } catch (error) {
                    console.error("Error procesando los gráficos:", error);
                }
            });

            // Generar reporte completo
            function descargarReporteCompleto() {
                const elemento = document.getElementById('dashboard-completo');
                const botonesAccion = elemento.querySelectorAll('.botones-accion');
                const botonMaestro = document.querySelector('.btn-reporte-completo');
                const tarjetas = elemento.querySelectorAll('.chart-card'); 

                // Ocultar botones
                botonesAccion.forEach(btn => btn.style.display = 'none');
                botonMaestro.innerText = "⏳ Generando presentación ejecutiva...";
                botonMaestro.disabled = true;

                // Presentar en columna
                const estiloOriginal = elemento.getAttribute('style') || '';
                elemento.style.display = 'block'; 
                elemento.style.width = '1280px';  
                elemento.style.margin = 'auto';
                elemento.style.padding = '50px';

                // Insertar título
                const tituloPDF = document.createElement('h1');
                tituloPDF.innerText = "Estadísticas Operativas y Epidemiológicas";
                tituloPDF.style.textAlign = 'center';
                tituloPDF.style.marginBottom = '30px';
                tituloPDF.style.color = '#333';
                tituloPDF.style.fontFamily = 'sans-serif';
                tituloPDF.id = 'titulo-temporal-pdf'; 
                elemento.prepend(tituloPDF);

                // Insertar divisores
                for(let i = 0; i < tarjetas.length - 1; i++) {
                    let divisor = document.createElement('div');
                    divisor.className = 'divisor-magico html2pdf__page-break';
                    tarjetas[i].parentNode.insertBefore(divisor, tarjetas[i].nextSibling);
                }

                // Redibujar gráficas
                for (let id in Chart.instances) {
                    Chart.instances[id].resize();
                }

                // Capturar datos
                setTimeout(() => {
                    const opciones = {
                        margin:       25, 
                        filename:     'Presentacion_Directiva_ITZAM.pdf',
                        image:        { type: 'jpeg', quality: 1 },
                        html2canvas:  { 
                            scale: 2, 
                            useCORS: true,
                            width: 1920,         
                            windowWidth: 1920
                        },
                        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
                        pagebreak:    { mode: ['legacy'] } 
                    };

                    html2pdf().set(opciones).from(elemento).save().then(() => {
                        // Restaurar estilos
                        elemento.setAttribute('style', estiloOriginal);
                        
                        // Borrar titulo temporal
                        const tituloInsertado = document.getElementById('titulo-temporal-pdf');
                        if (tituloInsertado) tituloInsertado.remove();
                        
                        // Borrar divisores
                        document.querySelectorAll('.divisor-magico').forEach(el => el.remove());

                        // Quitar margenes
                        tarjetas.forEach(t => t.style.marginTop = '');

                        // Restaurar tamaño de las graficas
                        for (let id in Chart.instances) {
                            Chart.instances[id].resize();
                        }

                        // Mostrar los botones
                        botonesAccion.forEach(btn => btn.style.display = '');
                        botonMaestro.innerText = "📊 Descargar Reporte Completo";
                        botonMaestro.disabled = false;
                    });
                }, 800); 
            }
        </script>

        <script src="Scripts/js/timeout.js"></script>