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
        <title>Sistema ITZAM — Catálogos</title>
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
        <h1>Catálogos del sistema</h1>
        </div>



            <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Nombre tabla</th>
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
                <label>Nombre del Paciente:</label>
                <input type="text" id="inputModalPaciente">
            </div>
            <div class="form-group">
                <label>Tipo de Consulta:</label>
                <select id="inputModalTipo">
                    <option value="General">General</option>
                    <option value="Urgencia">Urgencia</option>
                    <option value="Especialidad">Especialidad</option>
                    <option value="Laboratorio">Laboratorio</option>
                </select>
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-save" onclick="guardarCambios()">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <script>
        const cuerpoTabla = document.getElementById("cuerpoTabla");
        const modal = document.getElementById("modalEdicion");
        const inputModalId = document.getElementById("inputModalId");
        const inputModalPaciente = document.getElementById("inputModalPaciente");
        const inputModalTipo = document.getElementById("inputModalTipo");

        // --- 1. CARGAR DATOS (GET) ---
        async function buscarDatos() {
            const texto = document.getElementById("buscar_curp").value;
            cuerpoTabla.innerHTML = "<tr><td colspan='9' style='text-align:center'>Cargando...</td></tr>";

            try {
                // Llamada real al backend
                const response = await fetch(`backend.php?q=${texto}`);
                const datos = await response.json();
                
                if(datos.error) { alert(datos.error); return; }
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='9' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='9' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach(item => {
                let badgeClass = 'badge-general';
                if(item.tipo === 'Urgencia') badgeClass = 'badge-urgencia';
                if(item.tipo === 'Especialidad') badgeClass = 'badge-especialidad';

                // Importante: Escapamos las comillas en los strings para evitar errores en onclick
                const nombreSafe = item.paciente.replace(/'/g, "\\'"); 

                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><b>${item.id}</b></td>
                        <td>${item.paciente}</td>
                        <td style="font-family: monospace;">${item.curp}</td>
                        <td>${item.unidad}</td>
                        <td><span class="${badgeClass}">${item.tipo}</span></td>
                        <td>${item.fecha}</td>
                        <td>${item.medico}</td>
                        <td>${item.cedula}</td>
                        <td>
                            <button class="btn-edit" onclick="abrirModal(${item.id}, '${nombreSafe}', '${item.tipo}')">Editar</button>
                            <button class="btn-del" onclick="eliminarRegistro(${item.id})">Borrar</button>
                        </td>
                    </tr>
                `;
            });
        }

        // --- 2. ELIMINAR (POST) ---
        async function eliminarRegistro(id) {
            if(!confirm("¿Confirma que desea eliminar este registro permanentemente?")) return;

            try {
                const response = await fetch('backend.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'eliminar', id: id })
                });
                const res = await response.json();
                
                if(res.mensaje) {
                    buscarDatos(); // Recargar tabla
                }
            } catch (error) { alert("Error al eliminar"); }
        }

        // --- 3. EDITAR (POST) ---
        function abrirModal(id, paciente, tipo) {
            inputModalId.value = id;
            inputModalPaciente.value = paciente;
            inputModalTipo.value = tipo;
            modal.classList.add("show");
        }

        function cerrarModal() { modal.classList.remove("show"); }

        async function guardarCambios() {
            const id = inputModalId.value;
            const paciente = inputModalPaciente.value;
            const tipo = inputModalTipo.value;

            try {
                const response = await fetch('backend.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'editar', 
                        id: id, 
                        paciente: paciente, 
                        tipo: tipo 
                    })
                });
                const res = await response.json();
                
                if(res.mensaje) {
                    cerrarModal();
                    buscarDatos(); // Recargar tabla
                }
            } catch (error) { alert("Error al guardar cambios"); }
        }

        // Carga inicial
        buscarDatos();

        // Cerrar modal click fuera
        window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
    </script>


        <footer class="bottombar">© 2026 ITZAM</footer>

    </body>
</html>
