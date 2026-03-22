<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';       
require 'autorizacion.php';   

//RBAC
requerir_roles(['Administrador', 'Médico', 'Administrativo', 'Enfermería']);

require 'header.php';
?>
        <div class="title-box">
            <h1>Bienvenido(a), <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?></h1>
        </div>

        <div class="dashboard-container">
            <div>
                <a href="configuracion_datos_cuenta.php">
                    <img class="dashboard-icon" src="Assets/stethoscope-tool.png" alt="Datos de la cuenta">
                    <p class="card-subtitle">Datos de la cuenta</p>
                </a>
            </div>
            <div>
                <a href="configuracion_foto_perfil.php">
                    <img class="dashboard-icon" src="Assets/noun-black-cat-707608.png" alt="Foto de perfil">
                    <p class="card-subtitle">Foto del perfil</p>
                </a>
            </div>
        </div>
        
        <script src="Scripts/js/timeout.js"></script>
    
        <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>