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
        <title>Sistema ITZAM — Expediente electrónico</title>
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
                
                <div class="topbar-header">Consultar historia clínica</div>
                
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        const cuerpoTabla = document.getElementById("cuerpoTabla");
        const modal = document.getElementById("modalEdicion");
        const inputModalId = document.getElementById("inputModalId");
        const inputModalMedico = document.getElementById("inputModalMedico");
        const inputModalUnidad = document.getElementById("inputModalUnidad");
        const inputModalTipo = document.getElementById("inputModalTipo");

        let tablaInstancia = null; 

        // --- FUNCIÓN PARA CARGAR SELECTS DEL MODAL ---
        async function cargarSelectsModal() {
            try {
                // 1. Cargar Médicos y Unidades (desde el backend propio)
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

                // 2. 🔥 NUEVO: Cargar Tipos de Consulta (desde nuestra API dinámica)
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
                document.getElementById('inputModalMedico').innerHTML = '<option value="">Error al cargar</option>';
                document.getElementById('inputModalUnidad').innerHTML = '<option value="">Error al cargar</option>';
                document.getElementById('inputModalTipo').innerHTML = '<option value="">Error al cargar</option>';
            }
        }
        
        // --- 1. CARGAR DATOS INICIALES (GET TODO) ---
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
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='9' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        // --- RENDERIZAR E INICIALIZAR DATATABLES ---
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='9' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach(item => {
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
                        <td>
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
                        "sortAscending": ": Activar para ordenar ascendente",
                        "sortDescending": ": Activar para ordenar descendente"
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
        async function eliminarRegistro(idConsulta) {
            if(!confirm("¿Confirma que desea eliminar este registro permanentemente?")) return;

            try {
                const response = await fetch('backend_consulta_expediente.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'eliminar', idConsulta: idConsulta })
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
        function abrirModal(idConsulta, idPersonal, idUnidad, idTipoConsulta) {
            inputModalId.value = idConsulta;
            inputModalMedico.value = idPersonal;
            inputModalUnidad.value = idUnidad;
            inputModalTipo.value = idTipoConsulta; // Seleccionamos por ID
            modal.classList.add("show");
        }

        function cerrarModal() { modal.classList.remove("show"); }

        async function guardarCambios() {
            const idConsulta = inputModalId.value;
            const idPersonal = inputModalMedico.value;
            const idUnidad = inputModalUnidad.value;
            const idTipoConsulta = inputModalTipo.value; // Guardamos el ID

            if(!idTipoConsulta || !idPersonal || !idUnidad) {
                alert("Por favor, llena todos los campos del formulario.");
                return;
            }

            try {
                const response = await fetch('backend_consulta_expediente.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'editar', 
                        idConsulta: idConsulta, 
                        idPersonal: idPersonal, 
                        idUnidad: idUnidad,
                        idTipoConsulta: idTipoConsulta // 👈 Nueva llave JSON esperada por PHP
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
            cargarSelectsModal();
            cargarDatosIniciales();
        });

        // Cerrar modal click fuera
        window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
    </script>

    <footer class="bottombar">© 2026 ITZAM</footer>

    </body>
</html>