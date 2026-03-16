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
        <title>Sistema ITZAM — Consultar usuario del sistema</title>
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
                
                <div class="topbar-header">Consultar usuario del sistema</div>
                
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
        <table id="tablaUsuarios" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre de usuario</th>
                    <th>Email</th>
                    <th>Estatus</th>
                    <th>Rol</th>
                    <th>Fecha de creación</th>
                    <th>Fecha de suspensión</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla"></tbody>
        </table>
    </div>

  <div id="modalEdicion" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">Administrar Usuario</div>
            
            <input type="hidden" id="inputModalIdUsuario"> 
            
            <div class="form-group">
                <label>Nombre de Usuario:</label>
                <input type="text" id="inputModalUsuario" disabled style="background-color: #e9ecef; cursor: not-allowed;">
            </div>
            
            <div class="form-group">
                <label>Correo Electrónico:</label>
                <input type="email" id="inputModalEmail">
            </div>
            
            <div class="form-group">
                <label>Rol en el Sistema:</label>
                <select id="inputModalRol">
                    <option value="Administrador">Administrador</option>
                    <option value="Médico">Médico</option>
                    <option value="Enfermería">Enfermería</option>
                    <option value="Almacén">Almacén</option>
                    <option value="Recepción">Recepción</option>
                </select>
            </div>

            <div class="form-group">
                <label>Estatus de la Cuenta:</label>
                <select id="inputModalEstatus">
                    <option value="Activo">Activo</option>
                    <option value="Suspendido">Suspendido</option>
                </select>
            </div>
            
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-save" onclick="guardarCambiosUsuario()">Guardar Cambios</button>
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
        
        // --- VARIABLES DEL MODAL ---
        const modal = document.getElementById("modalEdicion");
        const inputModalIdUsuario = document.getElementById("inputModalIdUsuario"); 
        const inputModalUsuario = document.getElementById("inputModalUsuario");
        const inputModalEmail = document.getElementById("inputModalEmail"); 
        const inputModalRol = document.getElementById("inputModalRol");
        const inputModalEstatus = document.getElementById("inputModalEstatus");

        // Variable global para la instancia de DataTables
        let tablaInstancia = null; 

        // --- 1. CARGAR DATOS INICIALES (GET TODO) ---
        async function cargarDatosIniciales() {
            if (tablaInstancia !== null) {
                tablaInstancia.destroy();
                tablaInstancia = null;
            }

            cuerpoTabla.innerHTML = "<tr><td colspan='8' style='text-align:center'>Cargando base de datos de usuarios...</td></tr>";

            try {
                // Fetch directo al backend sin parámetro de búsqueda
                const response = await fetch('backend_consulta_usuarios_admin.php');
                const datos = await response.json();
                
                if(datos.error) { alert(datos.error); return; }
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='8' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        // --- 2. RENDERIZAR E INICIALIZAR DATATABLES ---
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='8' style='text-align:center; padding: 20px;'>No se encontraron usuarios registrados</td></tr>";
                return;
            }

            datos.forEach(item => {
                // Color dinámico para el estatus (Mantenido de tu código original)
                let colorEstatus = item['Estatus'] === 'Activo' ? '#198754' : '#dc3545';
                
                // Si la fecha de suspensión es null o vacía, mostramos un guion
                let fechaSuspension = item['Fecha de suspensión'] ? item['Fecha de suspensión'] : '<span style="color:gray;">-</span>';

                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><b>${item.idUsuario}</b></td>
                        <td style="font-family: monospace; font-weight: bold;">@${item['Nombre de usuario']}</td>
                        <td>${item.Email}</td>
                        <td style="color: ${colorEstatus}; font-weight: bold;">${item['Estatus']}</td>
                        <td><span style="background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-size: 0.9em; border: 1px solid #ced4da;">${item['Rol']}</span></td>
                        <td>${item['Fecha de creación']}</td>
                        <td>${fechaSuspension}</td>
                        <td>
                            <button class="btn-edit" onclick="abrirModal(${item.idUsuario}, '${item['Nombre de usuario']}', '${item.Email}', '${item['Rol']}', '${item['Estatus']}')">Editar</button>
                            
                            <button class="btn-del" onclick="suspenderUsuario(${item.idUsuario}, '${item['Estatus']}')">Suspender</button>
                        </td>
                    </tr>
                `;
            });

            // Inicializamos DataTables con diccionario en español
            tablaInstancia = $('#tablaUsuarios').DataTable({
                language: {
                    "decimal": "",
                    "emptyTable": "No hay información en la base de datos",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ usuarios",
                    "infoEmpty": "Mostrando 0 a 0 de 0 usuarios",
                    "infoFiltered": "(Filtrado de _MAX_ usuarios totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ usuarios por página",
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
                    { extend: 'excelHtml5', text: '📊 Exportar Auditoría', className: 'btn-exportar' },
                    { extend: 'csvHtml5', text: '📄 Exportar CSV', className: 'btn-exportar' }
                ],
                pageLength: 10,
                ordering: true,
                order: [[0, "desc"]] // Ordena por ID descendente por defecto
            });
        }

        // --- 3. SUSPENDER USUARIO (POST) ---
        async function suspenderUsuario(idUsuario, estatus) {
            // Si ya está suspendido, no tiene caso volver a suspenderlo
            if(estatus === 'Suspendido') {
                alert("Este usuario ya se encuentra suspendido.");
                return;
            }

            if(!confirm("¿Confirma que desea suspender el acceso a este usuario? Ya no podrá iniciar sesión.")) return;

            try {
                const response = await fetch('backend_consulta_usuarios_admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'suspender', idUsuario: idUsuario })
                });
                
                const res = await response.json();
                
                if(res.estatus === 'exito') {
                    alert("✅ Usuario suspendido correctamente.");
                    cargarDatosIniciales(); // Recargar tabla usando la nueva función
                } else {
                    alert("Error: " + res.mensaje);
                }
            } catch (error) { 
                alert("Error de conexión al suspender usuario"); 
            }
        }

        // --- 4. EDITAR USUARIO (ABRIR Y GUARDAR MODAL) ---
        function abrirModal(idUsuario, nombre, email, rol, estatus) {
            inputModalIdUsuario.value = idUsuario;
            inputModalUsuario.value = nombre;
            inputModalEmail.value = email;
            inputModalRol.value = rol;
            inputModalEstatus.value = estatus;
            
            modal.classList.add("show");
        }

        function cerrarModal() { 
            modal.classList.remove("show"); 
        }

        async function guardarCambiosUsuario() {
            const idUsuario = inputModalIdUsuario.value;
            const email = inputModalEmail.value.trim();
            const rol = inputModalRol.value;
            const estatus = inputModalEstatus.value;

            // Validación de formato de correo
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email)) {
                alert("Por favor, ingresa un correo electrónico válido.");
                return;
            }

            try {
                const response = await fetch('backend_consulta_usuarios_admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'editar', 
                        idUsuario: idUsuario, 
                        email: email, 
                        rol: rol,
                        estatus: estatus
                    })
                });
                
                const res = await response.json();
                
                if(res.estatus === 'exito') {
                    alert("✅ Cambios guardados correctamente.");
                    cerrarModal();
                    cargarDatosIniciales(); // Recargar tabla usando la nueva función
                } else {
                    alert("⚠️ Error: " + res.mensaje);
                }
            } catch (error) { 
                alert("Error al guardar cambios de usuario"); 
            }
        }

        // Carga inicial al abrir la página
        cargarDatosIniciales();

        // Cerrar modal al dar click fuera del recuadro
        window.onclick = function(ev) { 
            if (ev.target == modal) cerrarModal(); 
        }
    </script>

    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>