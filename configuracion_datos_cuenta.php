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
        <title>Sistema ITZAM — Mi cuenta</title>
        <link rel="stylesheet" href="styles.css" />
    </head>
    <body>
        <header>
            <div class="topbar-container">
                <div>
                    <img class ="logo"src="Assets/itzam_logoV2.png" alt="LOGO" />
                </div>
                
                <div class="topbar-header">Sistema web de consulta de información clínica ITZAM</div>
                
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

        <h1>Datos de la cuenta</h1>
        <hr>

            <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Nombre de usuario</th>
                    <th>Email</th>
                    <th>Fecha de registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla"></tbody>
        </table>
    </div>

<div id="modalEdicion" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">Editar Correo Electrónico</div>
            
            <input type="hidden" id="inputModalIdUsuario"> 
            
            <div class="form-group">
                <label>Nombre de Usuario:</label>
                <input type="text" id="inputModalUsuario" disabled style="background-color: #e9ecef; cursor: not-allowed;">
            </div>
            
            <div class="form-group">
                <label>Nuevo Correo Electrónico:</label>
                <input type="email" id="inputModalEmail" placeholder="ejemplo@correo.com">
            </div>
            
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-save" onclick="guardarCambios()">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <div id="modalPassword" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">Cambiar Contraseña</div>
            
            <input type="hidden" id="inputModalIdPass"> 
            
            <div class="form-group">
                <label>Contraseña Actual:</label>
                <input type="password" id="inputPassActual" placeholder="Ingresa tu contraseña actual">
            </div>
            
            <div class="form-group">
                <label>Nueva Contraseña:</label>
                <input type="password" id="inputPassNuevo" placeholder="Mínimo 8 caracteres">
            </div>

            <div class="form-group">
                <label>Confirmar Nueva Contraseña:</label>
                <input type="password" id="inputPassConfirmar" placeholder="Repite la nueva contraseña">
            </div>
            
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModalPassword()">Cancelar</button>
                <button class="btn-save" onclick="guardarPassword()" style="background-color: #28a745; color: white;">Actualizar Contraseña</button>
            </div>
        </div>
    </div>

    <script>
        const cuerpoTabla = document.getElementById("cuerpoTabla");
        
        // Variables Modal Correo
        const modal = document.getElementById("modalEdicion");
        const inputModalIdUsuario = document.getElementById("inputModalIdUsuario");
        const inputModalUsuario = document.getElementById("inputModalUsuario");
        const inputModalEmail = document.getElementById("inputModalEmail");

        // Variables Modal Contraseña
        const modalPassword = document.getElementById("modalPassword");
        const inputModalIdPass = document.getElementById("inputModalIdPass");
        const inputPassActual = document.getElementById("inputPassActual");
        const inputPassNuevo = document.getElementById("inputPassNuevo");
        const inputPassConfirmar = document.getElementById("inputPassConfirmar");

        // --- 1. CARGAR DATOS (GET) ---
        async function buscarDatos() {
            cuerpoTabla.innerHTML = "<tr><td colspan='4' style='text-align:center'>Cargando...</td></tr>";

            try {
                const response = await fetch('backend_config_cuenta.php');
                const datos = await response.json();
                
                if(datos.error) { alert(datos.error); return; }
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='4' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        // --- 2. RENDERIZAR TABLA ---
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='4' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach(item => {
                cuerpoTabla.innerHTML += `
                    <tr>
                        <td>${item['Nombre de usuario']}</td>
                        <td>${item.Email}</td>
                        <td>${item['Fecha de registro']}</td>
                        <td>
                            <button class="btn-edit" onclick="abrirModal(${item.idUsuario}, '${item['Nombre de usuario']}', '${item.Email}')" style="margin-right: 5px;">Actualizar Email</button>
                            <button class="btn-edit" onclick="abrirModalPassword(${item.idUsuario})" style="background-color: #343a40; color: white;">Cambiar Contraseña</button>
                        </td>
                    </tr>
                `;
            });
        }

        // --- 3. FUNCIONES DEL MODAL DE CORREO ---
        function abrirModal(id, nombre, email) {
            inputModalIdUsuario.value = id;
            inputModalUsuario.value = nombre;
            inputModalEmail.value = email;
            modal.style.display = "flex"; // Ajustado para que funcione directo si quitaste la clase .show
        }

        function cerrarModal() { 
            modal.style.display = "none"; 
        }

        async function guardarCambios() {
            const id = inputModalIdUsuario.value;
            const email = inputModalEmail.value.trim();

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email)) {
                alert("Por favor, ingresa un correo electrónico válido.");
                return;
            }

            try {
                const response = await fetch('backend_config_cuenta.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'editar', idUsuario: id, email: email })
                });
                
                const res = await response.json();
                
                if(res.estatus === 'exito') {
                    alert("✅ " + res.mensaje);
                    cerrarModal();
                    buscarDatos(); 
                } else {
                    alert("⚠️ Error: " + res.mensaje);
                }
            } catch (error) { 
                alert("Error al intentar guardar los cambios."); 
            }
        }

        // --- 4. FUNCIONES DEL MODAL DE CONTRASEÑA ---
        function abrirModalPassword(id) {
            inputModalIdPass.value = id;
            inputPassActual.value = '';
            inputPassNuevo.value = '';
            inputPassConfirmar.value = '';
            modalPassword.style.display = "flex"; 
        }

        function cerrarModalPassword() { 
            modalPassword.style.display = "none"; 
        }

        async function guardarPassword() {
            const id = inputModalIdPass.value;
            const actual = inputPassActual.value.trim();
            const nuevo = inputPassNuevo.value.trim();
            const confirmar = inputPassConfirmar.value.trim();

            if (!actual || !nuevo || !confirmar) {
                alert("Todos los campos son obligatorios.");
                return;
            }

            if (nuevo.length < 8) {
                alert("La nueva contraseña debe tener al menos 8 caracteres para ser segura.");
                return;
            }

            if (nuevo !== confirmar) {
                alert("Las nuevas contraseñas no coinciden. Intenta de nuevo.");
                return;
            }

            try {
                const response = await fetch('backend_config_cuenta.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'cambiar_password', 
                        idUsuario: id, 
                        passwordActual: actual,
                        passwordNuevo: nuevo
                    })
                });
                
                const res = await response.json();
                
                if(res.estatus === 'exito') {
                    alert("✅ " + res.mensaje);
                    cerrarModalPassword();
                } else {
                    alert("⚠️ Error: " + res.mensaje);
                }
            } catch (error) { 
                alert("Error de conexión al intentar cambiar la contraseña."); 
            }
        }

        // --- CARGA INICIAL Y CIERRE DE MODALES ---
        buscarDatos();

        window.onclick = function(ev) { 
            if (ev.target == modal) cerrarModal(); 
            if (ev.target == modalPassword) cerrarModalPassword();
        }
    </script>


        <footer class="bottombar">© 2026 ITZAM</footer>

    </body>
</html>
