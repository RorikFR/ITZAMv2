<?php
function puede_ver($roles_permitidos) {
    $rol = $_SESSION['rol'] ?? '';
    
    // Acceso total para administrador
    if ($rol === 'Administrador') return true;
    return in_array($rol, $roles_permitidos);
}
?>
<!doctype html>
<html lang="es-MX">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>Sistema ITZAM</title>
        <link rel="stylesheet" href="styles.css" />
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    </head>
    <body>
        <header>
            <div class="topbar-container">
                <div>
                    <img class="logo" src="Assets/itzam_logoV2.png" alt="LOGO" />
                </div>
                
                <div class="topbar-header">Sistema web de consulta de información clínica - ITZAM</div>
                
                <div class="user-menu">
                    <div style="display: flex; align-items: center; gap: 12px; cursor: pointer;" onclick="toggleMenu()">
                        <span style="color: white; font-weight: bold; font-size: 0.95em; text-align: right;">
                            <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?>
                        </span>
                        <img id="header-user-photo" class="user-photo user-icon" style="margin: 0;" src="<?php echo isset($_SESSION['foto_perfil']) && $_SESSION['foto_perfil'] ? $_SESSION['foto_perfil'] : 'Assets/think.jpg'; ?>">
                    </div>
                    
                    <div class="dropdown-menu" id="userDropdown">
                        <p class="user-menu-title" style="font-weight: bold;">Menú</p>
                        <hr>
                        
                        <?php if (puede_ver([])): // Vacío porque la llave maestra del Admin le da acceso automático ?>
                            <a class="dropdown-item" href="administracion.php">Administración</a>
                            <a class="dropdown-item" href="catalogos.php">Catálogos</a>
                        <?php endif; ?>
                        
                        <?php if (puede_ver(['Médico', 'Enfermería', 'Administrativo'])): ?>
                            <a class="dropdown-item" href="configuracion_cuenta.php">Configuración</a>
                        <?php endif; ?>
                        
                        <a class="dropdown-item" href="logout.php">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </header>
    
    <nav>   
        <ul>
            <li><a href="home.php" class="active">Inicio</a></li>

            <?php if (puede_ver(['Médico', 'Enfermería'])): ?>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Asesorías</a>
                <div class="dropdown-content">
                    <a href="mis_asesorias.php">Mis asesorías</a>
                    <a href="nueva_asesoria.php">Registrar asesoría</a>
                </div>  
            </li>
            <?php endif; ?>

            <?php if (puede_ver(['Médico', 'Enfermería', 'Administrativo'])): ?>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Consultas médicas</a>
                <div class="dropdown-content">
                    <a href="buscar_consulta.php">Buscar consulta</a>
                    <?php if (puede_ver(['Médico'])): ?>
                        <a href="nueva_consulta.php">Registrar consulta</a>
                    <?php endif; ?>
                </div>
            </li>
            <?php endif; ?>

            <?php if (puede_ver(['Médico', 'Administrativo'])): ?>
            <li><a href="estadisticas.php">Estadísticas</a></li>
            <?php endif; ?>

            <?php if (puede_ver(['Médico', 'Enfermería'])): ?>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Laboratorios</a>
                <div class="dropdown-content">
                    <a href="consulta_orden_laboratorio.php">Buscar orden de laboratorio</a>
                    <?php if (puede_ver(['Médico'])): ?>
                        <a href="nueva_orden_laboratorio.php">Crear orden de laboratorio</a>
                    <?php endif; ?>
                </div>
            </li>
            <?php endif; ?>

            <?php if (puede_ver(['Administrativo'])): ?>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Inventario</a>
                <div class="dropdown-content">
                    <a href="consulta_inventario.php">Buscar en inventario</a>
                    <a href="nueva_compra_med.php">Registrar compra de medicamentos</a>
                    <a href="nueva_compra_insumo.php">Registrar compra de insumos</a>
                    <a href="nueva_compra_equipo.php">Registrar compra de equipo médico</a>
                </div>
            </li>
            <?php endif; ?>

            <?php if (puede_ver(['Médico', 'Enfermería', 'Administrativo'])): ?>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Pacientes</a>
                <div class="dropdown-content">
                    <a href="consulta_expediente.php">Consultar historia clínica</a>
                    <a href="consulta_paciente.php">Consultar paciente</a>
                    <a href="nuevo_paciente.php">Registrar paciente</a>
                </div>
            </li>
            <?php endif; ?>

            <?php if (puede_ver(['Administrativo'])): ?>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Personal de salud</a>
                <div class="dropdown-content">
                    <a href="consulta_personal.php">Consultar personal</a>
                    <a href="nuevo_personal.php">Registrar personal</a>
                </div>
            </li>
            <?php endif; ?>

            <?php if (puede_ver(['Médico', 'Enfermería'])): ?>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Recetas</a>
                <div class="dropdown-content">
                    <a href="consulta_receta.php">Consultar receta</a>
                    <?php if (puede_ver(['Médico'])): ?>
                        <a href="nueva_receta.php">Registrar receta</a>
                    <?php endif; ?>
                </div>
            </li>
            <?php endif; ?>

            <?php if (puede_ver(['Médico', 'Enfermería', 'Administrativo'])): ?>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Unidades médicas</a>
                <div class="dropdown-content">
                    <a href="consulta_unidad.php">Consultar unidad médica</a>
                    <?php if (puede_ver(['Administrativo'])): ?>
                        <a href="nueva_unidad.php">Registrar unidad médica</a>
                    <?php endif; ?>
                </div>
            </li>
            <?php endif; ?>

        </ul>
    </nav>