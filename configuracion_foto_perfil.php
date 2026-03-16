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
        <title>Sistema ITZAM — Foto de perfil</title>
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
                <img id="header-user-photo" class="user-photo user-icon" src="<?php echo isset($_SESSION['foto_perfil']) && $_SESSION['foto_perfil'] ? $_SESSION['foto_perfil'] : 'Assets/img_placeholder.png'; ?>" onclick="toggleMenu()">
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
            <h3>Modificar foto de perfil</h3>
        </div>

        <div class="grid-wrapper">
            <div class="formulario-background-normal" style="text-align: center; padding: 30px;">
                
                <form id="form-foto-perfil" action="" method="post" enctype="multipart/form-data">
                    
                    <div style="margin-bottom: 20px;">
                        <img id="profile-picture" src="Assets/img_placeholder.png" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 3px solid #0056b3; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display:block; margin-bottom:10px; font-weight:bold;">Selecciona una nueva imagen:</label>
                        <input type="file" id="input-foto" name="foto" accept=".jpg, .jpeg, .png, .webp" style="display: inline-block;">
                    </div>
                    
                </form>
                
                <button class="multi-btn-submit" type="button" id="submitBtn" onclick="subirFoto()">Subir foto</button>
            </div>
        </div>

        <footer class="bottombar">© 2026 ITZAM</footer>

        <script>
            const inputFoto = document.getElementById('input-foto');
            const profilePicture = document.getElementById('profile-picture');

            // --- 1. LÓGICA DE PREVISUALIZACIÓN Y VALIDACIÓN RÁPIDA ---
            // Este evento se dispara apenas el usuario selecciona un archivo de su computadora
            inputFoto.addEventListener('change', function(event) {
                const file = event.target.files[0];
                
                if (file) {
                    // Si el archivo no pasa las reglas, borramos el input y detenemos todo
                    if (!validarArchivo(file)) {
                        inputFoto.value = ''; 
                        return;
                    }

                    // Magia de JS: Creamos una URL temporal en memoria para previsualizar la imagen
                    const objectUrl = URL.createObjectURL(file);
                    profilePicture.src = objectUrl;
                }
            });

            // --- 2. REGLAS DE NEGOCIO Y SEGURIDAD ---
            function validarArchivo(file) {
                const maxSizeMB = 2; // Límite de 2MB para no saturar el servidor
                const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                const maxNameLength = 50;

                // Regla 1: Validar tipo de archivo real (MIME type)
                if (!validTypes.includes(file.type)) {
                    alert("⚠️ Tipo de archivo no permitido. Solo se aceptan imágenes JPG, PNG o WEBP.");
                    return false;
                }

                // Regla 2: Validar tamaño máximo
                if (file.size > (maxSizeMB * 1024 * 1024)) {
                    alert(`⚠️ El archivo es muy pesado. El límite máximo es de ${maxSizeMB}MB.`);
                    return false;
                }

                // Regla 3: Validar longitud del nombre para evitar ataques o desbordes en la Base de Datos
                if (file.name.length > maxNameLength) {
                    alert(`⚠️ El nombre del archivo es demasiado largo (máximo ${maxNameLength} caracteres). Renómbralo e intenta de nuevo.`);
                    return false;
                }

                return true; // Si sobrevive a todo, es seguro
            }

            // --- 3. ENVIAR ARCHIVO AL SERVIDOR ---
            async function subirFoto() {
                const file = inputFoto.files[0];
                
                if (!file) {
                    alert("Por favor, selecciona una imagen primero.");
                    return;
                }

                // Doble validación por seguridad antes de enviar
                if (!validarArchivo(file)) return;

                // A DIFERENCIA DE LOS JSON, PARA ENVIAR ARCHIVOS USAMOS FORMDATA
                const formData = new FormData();
                formData.append('foto_perfil', file);
                formData.append('accion', 'subir_foto');

                // Cambiamos el texto del botón para que el usuario sepa que está cargando
                const btn = document.getElementById('submitBtn');
                btn.innerText = "Subiendo...";
                btn.disabled = true;

                try {
                    const response = await fetch('backend_foto_perfil.php', {
                        method: 'POST',
                        body: formData 
                    });
                    
                    const datos = await response.json();
                    
                    if (datos.estatus === 'exito') {
                        alert("✅ " + datos.mensaje);
                        
                        // --- MAGIA DE ACTUALIZACIÓN EN TIEMPO REAL ---
                        // Agregamos un timestamp (?t=...) para engañar a la caché del navegador 
                        // y obligarlo a pintar la nueva foto inmediatamente.
                        const rutaFresca = datos.nueva_ruta + '?t=' + new Date().getTime();
                        
                        // 1. Actualizamos la foto grande del formulario
                        document.getElementById('profile-picture').src = rutaFresca;
                        
                        // 2. Actualizamos la foto pequeña del menú superior
                        const headerPhoto = document.getElementById('header-user-photo');
                        if (headerPhoto) {
                            headerPhoto.src = rutaFresca;
                        }
                        // ---------------------------------------------
                        
                    } else {
                        alert("⚠️ Error: " + datos.mensaje);
                    }
                } catch (error) {
                    console.error("Error al subir:", error);
                    alert("Error de conexión al intentar subir la foto.");
                } finally {
                    // Restauramos el botón
                    btn.innerText = "Subir foto";
                    btn.disabled = false;
                }
            }
        </script>
    </body>
</html>