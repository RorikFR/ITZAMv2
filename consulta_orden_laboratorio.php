<?php
session_start();

// Opcional pero recomendado: El escudo de seguridad
if (!isset($_SESSION['idUsuario'])) {
    header("Location: index.php");
    exit;
}
?>

<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>Sistema ITZAM — Consultar orden</title>
        <link rel="stylesheet" href="styles.css" />
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    </head>
    <body>
        <header>
            <div class="topbar-container">
                <div>
                    <img class ="logo"src="Assets/itzam_logoV2.png" alt="LOGO" />
                </div>
                
                <div class="topbar-header">Consultar orden de laboratorio</div>
                
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

    <br>

        <div class="tabla-container">
            <table id="tablaLaboratorio" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del paciente</th>
                        <th>Apellido Paterno</th>
                        <th>Apellido Materno</th>
                        <th>CURP</th>
                        <th>Género</th>
                        <th>Médico solicitante</th>
                        <th>Prioridad</th>
                        <th>Estudio requerido</th>
                        <th>Diagnóstico preliminar</th>
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
            <input type="hidden" id="inputModalEstudioViejo"> <div class="form-group">
                <label>Estudio requerido:</label>
                <select id="inputModalEstudio">
                    <option value="" selected disabled>Cargando estudios...</option>
                </select>
            </div>
            <div class="form-group">
                <label>Prioridad:</label>
                <select id="inputModalPrioridad">
                    <option value="" selected disabled>Cargando prioridades...</option>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        const cuerpoTabla = document.getElementById("cuerpoTabla");
        const modal = document.getElementById("modalEdicion");
        const inputModalId = document.getElementById("inputModalId");
        const inputModalEstudioViejo = document.getElementById("inputModalEstudioViejo");
        const inputModalEstudio = document.getElementById("inputModalEstudio");
        const inputModalPrioridad = document.getElementById("inputModalPrioridad");

        let tablaInstancia = null; 

        // --- 0. CARGAR CATÁLOGOS DINÁMICOS PARA EL MODAL ---
        async function cargarCatalogosModal() {
            try {
                // Cargar Prioridades
                const resPrio = await fetch('backend_catalogos.php?tabla=cat_prioridad_lab');
                const datosPrio = await resPrio.json();
                inputModalPrioridad.innerHTML = '<option value="" disabled selected>Selecciona una opción:</option>';
                if(!datosPrio.error) {
                    datosPrio.forEach(item => {
                        const op = document.createElement('option');
                        op.value = item.id;
                        op.textContent = item.valor;
                        inputModalPrioridad.appendChild(op);
                    });
                }

                // Cargar Estudios
                const resEst = await fetch('backend_catalogos.php?tabla=cat_estudios_laboratorio');
                const datosEst = await resEst.json();
                inputModalEstudio.innerHTML = '<option value="" disabled selected>Selecciona un estudio:</option>';
                if(!datosEst.error) {
                    datosEst.forEach(item => {
                        const op = document.createElement('option');
                        op.value = item.id;
                        op.textContent = item.valor;
                        inputModalEstudio.appendChild(op);
                    });
                }
            } catch (error) {
                console.error("Error al cargar catálogos:", error);
            }
        }

        // --- 1. CARGAR DATOS INICIALES (GET TODO) ---
        async function cargarDatosIniciales() {
            if (tablaInstancia !== null) {
                tablaInstancia.destroy();
                tablaInstancia = null;
            }

            cuerpoTabla.innerHTML = "<tr><td colspan='11' style='text-align:center'>Cargando base de datos...</td></tr>";

            try {
                const response = await fetch('backend_buscar-laboratorio.php');
                const datos = await response.json();
                
                if(datos.error) { alert(datos.error); return; }
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='11' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        // --- RENDERIZAR E INICIALIZAR DATATABLES ---
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='11' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach(item => {
                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><b>${item.idOrdenLaboratorio}</b></td>
                        <td>${item.nombre_paciente}</td>
                        <td>${item.apellido_paterno}</td>
                        <td>${item.apellido_materno}</td>
                        <td style="font-family: monospace;">${item.curp}</td>
                        <td>${item.genero}</td>
                        <td>${item.medico_solicitante}</td>
                        <td style="font-weight: bold; color: ${item.prioridad === 'Urgente' ? 'red' : item.prioridad === 'Alta' ? 'orange' : 'black'}">${item.prioridad}</td>
                        <td>${item.estudio_requerido}</td>
                        <td>${item.diagnostico_preliminar}</td>
                        <td>
                            <button class="btn-edit" onclick="abrirModal(${item.idOrdenLaboratorio}, ${item.idEstudio}, ${item.idPrioridad})">Editar</button>
                            <button class="btn-del" onclick="eliminarRegistro(${item.idOrdenLaboratorio})">Borrar</button>
                        </td>
                    </tr>
                `;
            });

            tablaInstancia = $('#tablaLaboratorio').DataTable({
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
                        "sortAscending": ": Activar para ordenar la columna",
                        "sortDescending": ": Activar para ordenar la columna"
                    }
                },
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Descargar Excel', className: 'btn-exportar' },
                    { extend: 'csvHtml5', text: '📄 Descargar CSV', className: 'btn-exportar' }
                ],
                pageLength: 10,
                ordering: true,
                order: [[0, "desc"]]
            });
        }

        // --- 2. ELIMINAR (POST) ---
        async function eliminarRegistro(idOrdenLaboratorio) {
            if(!confirm("¿Confirma que desea eliminar este registro permanentemente?")) return;

            try {
                const response = await fetch('backend_buscar-laboratorio.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'eliminar', idOrdenLaboratorio: idOrdenLaboratorio})
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

        // --- 3. EDITAR (POST) ---
        function abrirModal(idOrdenLaboratorio, idEstudio, idPrioridad) {
            inputModalId.value = idOrdenLaboratorio;
            inputModalEstudioViejo.value = idEstudio; // Guardamos en secreto el ID viejo
            inputModalEstudio.value = idEstudio; // Pre-seleccionamos el select visible
            inputModalPrioridad.value = idPrioridad;
            modal.classList.add("show");
        }

        function cerrarModal() { modal.classList.remove("show"); }

        async function guardarCambios() {
            const idOrdenLaboratorio = inputModalId.value;
            const idEstudioViejo = inputModalEstudioViejo.value;
            const idEstudioNuevo = inputModalEstudio.value;
            const idPrioridad = inputModalPrioridad.value;

            if(!idEstudioNuevo || !idPrioridad) {
                alert("Por favor selecciona un estudio y una prioridad.");
                return;
            }

            try {
                const response = await fetch('backend_buscar-laboratorio.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'editar', 
                        idOrdenLaboratorio: idOrdenLaboratorio, 
                        idPrioridad: idPrioridad,
                        idEstudioNuevo: idEstudioNuevo, 
                        idEstudioViejo: idEstudioViejo 
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
        document.addEventListener('DOMContentLoaded', () => {
            cargarCatalogosModal();
            cargarDatosIniciales();
        });

        // Cerrar modal click fuera
        window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
    </script>

    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>