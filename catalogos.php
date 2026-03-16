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
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
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
        <p style="text-align: center; color: #666;">Selecciona un diccionario de datos para gestionar sus registros.</p>
    </div>

    <div id="vista-tarjetas" style="display: flex; gap: 20px; flex-wrap: wrap; padding: 20px; justify-content: center; max-width: 1200px; margin: auto;">
        
       <div onclick="abrirCatalogo('cat_especialidades', 'Especialidades Médicas')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
                <h1 style="font-size: 3em; margin: 0; color: #007bff;">🩺</h1>
                <h3 style="margin: 10px 0;">Especialidades</h3>
                <p style="color: #666; font-size: 0.9em;">Pediatría, Cardiología, etc.</p>
            </div>

            <div onclick="abrirCatalogo('cat_puestos', 'Puestos de Personal')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
                <h1 style="font-size: 3em; margin: 0; color: #17a2b8;">👨‍⚕️</h1>
                <h3 style="margin: 10px 0;">Puestos</h3>
                <p style="color: #666; font-size: 0.9em;">Médico General, Enfermería, etc.</p>
            </div>

            <div onclick="abrirCatalogo('cat_tipo_consulta', 'Tipos de Consulta')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
                <h1 style="font-size: 3em; margin: 0; color: #6f42c1;">📋</h1>
                <h3 style="margin: 10px 0;">Tipos de Consulta</h3>
                <p style="color: #666; font-size: 0.9em;">General, Urgencias, Especialidad.</p>
            </div>

            <div onclick="abrirCatalogo('cat_motivos_asesoria', 'Motivos de Asesoría')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
                <h1 style="font-size: 3em; margin: 0; color: #e83e8c;">🗣️</h1>
                <h3 style="margin: 10px 0;">Motivos de Asesoría</h3>
                <p style="color: #666; font-size: 0.9em;">Vacunación, Tratamiento, etc.</p>
            </div>

            <div onclick="abrirCatalogo('cat_estudios_laboratorio', 'Estudios de Laboratorio')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
                <h1 style="font-size: 3em; margin: 0; color: #20c997;">🧪</h1>
                <h3 style="margin: 10px 0;">Estudios Médicos</h3>
                <p style="color: #666; font-size: 0.9em;">Biometría, Química Sanguínea, etc.</p>
            </div>

            <div onclick="abrirCatalogo('cat_prioridad_lab', 'Prioridades de Laboratorio')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
                <h1 style="font-size: 3em; margin: 0; color: #dc3545;">🚨</h1>
                <h3 style="margin: 10px 0;">Prioridades Lab.</h3>
                <p style="color: #666; font-size: 0.9em;">Urgente, Rutina, Programado.</p>
            </div>

            <div onclick="abrirCatalogo('proveedores', 'Proveedores Autorizados')" style="cursor: pointer; padding: 30px 20px; border: 1px solid #ddd; border-radius: 10px; width: 250px; text-align: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;">
                <h1 style="font-size: 3em; margin: 0; color: #fd7e14;">📦</h1>
                <h3 style="margin: 10px 0;">Proveedores</h3>
                <p style="color: #666; font-size: 0.9em;">Farmacéuticas, Material médico, etc.</p>
            </div>

        </div>

    <div id="vista-tabla" style="display: none; max-width: 1200px; margin: auto; padding: 20px;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <button onclick="volverTarjetas()" class="btn-cancel" style="margin: 0;">⬅ Volver al menú</button>
            <h2 id="titulo-catalogo-actual" style="margin: 0; color: #0056b3;">Nombre del Catálogo</h2>
            <button onclick="abrirModalNuevo()" class="btn-save" style="margin: 0;">+ Nuevo Registro</button>
        </div>

        <div class="tabla-container">
            <table id="tablaDetalleCatalogo" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 15%;">ID</th>
                        <th style="width: 60%;">Valor / Nombre</th>
                        <th style="width: 25%;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaDetalle"></tbody>
            </table>
        </div>
    </div>

    <div id="modalEdicion" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header" id="modal-titulo">Gestionar Registro</div>
            
            <input type="hidden" id="inputModalId"> 
            
            <div class="form-group">
                <label>Valor / Nombre del registro:</label>
                <input type="text" id="inputModalValor" placeholder="Ej. Cardiología, A+, Oral..." autofocus>
            </div>
            
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button class="btn-save" onclick="guardarCambios()">Guardar</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        // Variables de Vistas
        const vistaTarjetas = document.getElementById("vista-tarjetas");
        const vistaTabla = document.getElementById("vista-tabla");
        const tituloCatalogo = document.getElementById("titulo-catalogo-actual");
        
        // Variables de Tabla y Modal
        const cuerpoTabla = document.getElementById("cuerpoTablaDetalle");
        const modal = document.getElementById("modalEdicion");
        const inputModalId = document.getElementById("inputModalId");
        const inputModalValor = document.getElementById("inputModalValor");

        let tablaInstancia = null; 
        
        // Estado actual: ¿Qué catálogo estamos viendo?
        let catalogoActualBD = ""; 
        let catalogoActualNombre = "";

        // --- NAVEGACIÓN ENTRE VISTAS ---
        function abrirCatalogo(tabla_bd, nombre_amigable) {
            catalogoActualBD = tabla_bd;
            catalogoActualNombre = nombre_amigable;
            
            tituloCatalogo.innerText = nombre_amigable;
            
            vistaTarjetas.style.display = "none";
            vistaTabla.style.display = "block";

            cargarDatosCatalogo();
        }

        function volverTarjetas() {
            vistaTabla.style.display = "none";
            vistaTarjetas.style.display = "flex";
            catalogoActualBD = "";
        }

        // --- 1. CARGAR DATOS DEL CATÁLOGO SELECCIONADO (GET) ---
        async function cargarDatosCatalogo() {
            if (tablaInstancia !== null) {
                tablaInstancia.destroy();
                tablaInstancia = null;
            }

            cuerpoTabla.innerHTML = "<tr><td colspan='3' style='text-align:center'>Cargando registros...</td></tr>";

            try {
                // Le decimos al backend QUÉ tabla queremos leer
                const response = await fetch(`backend_catalogos.php?tabla=${catalogoActualBD}`);
                const datos = await response.json();
                
                if(datos.error) { alert(datos.error); volverTarjetas(); return; }
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='3' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        // --- RENDERIZAR E INICIALIZAR DATATABLES ---
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='3' style='text-align:center; padding: 20px;'>El catálogo está vacío. ¡Agrega el primer registro!</td></tr>";
                // Aún vacío, inicializamos DataTables para que la interfaz no se rompa
            } else {
                datos.forEach(item => {
                    // Limpiamos comillas para evitar errores JS
                    const valorSafe = item.valor.replace(/'/g, "\\'"); 

                    cuerpoTabla.innerHTML += `
                        <tr>
                            <td><b>${item.id}</b></td>
                            <td style="font-size: 1.1em;">${item.valor}</td>
                            <td>
                                <button class="btn-edit" onclick="abrirModalEditar(${item.id}, '${valorSafe}')">Editar</button>
                                <button class="btn-del" onclick="eliminarRegistro(${item.id})">Borrar</button>
                            </td>
                        </tr>
                    `;
                });
            }

            tablaInstancia = $('#tablaDetalleCatalogo').DataTable({
                language: {
                    "decimal": "",
                    "emptyTable": "No hay registros en este catálogo",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(Filtrado de _MAX_ registros totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "🔍 Buscar registro:",
                    "zeroRecords": "No se encontraron coincidencias",
                    "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" }
                },
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '📊 Exportar', className: 'btn-exportar' }
                ],
                pageLength: 10,
                ordering: true,
                order: [[1, "asc"]], // Ordenar alfabéticamente por el valor
                destroy: true // Vital para poder cambiar de catálogo sin recargar la página
            });
        }

        // --- 2. ELIMINAR (POST) ---
        async function eliminarRegistro(id) {
            if(!confirm(`¿Confirma que desea eliminar este registro del catálogo de ${catalogoActualNombre}?`)) return;

            try {
                const response = await fetch('backend_catalogos.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'eliminar', 
                        tabla: catalogoActualBD, 
                        id: id 
                    })
                });
                const res = await response.json();
                
                if(res.estatus === 'exito') {
                    cargarDatosCatalogo(); 
                } else {
                    alert("⚠️ " + res.mensaje);
                }
            } catch (error) { alert("Error al eliminar"); }
        }

        // --- 3. GESTIÓN DEL MODAL (NUEVO / EDITAR) ---
        function abrirModalNuevo() {
            document.getElementById("modal-titulo").innerText = `Nuevo Registro - ${catalogoActualNombre}`;
            inputModalId.value = ""; // ID vacío significa "Crear Nuevo"
            inputModalValor.value = "";
            modal.classList.add("show");
        }

        function abrirModalEditar(id, valor) {
            document.getElementById("modal-titulo").innerText = `Editar Registro - ${catalogoActualNombre}`;
            inputModalId.value = id;
            inputModalValor.value = valor;
            modal.classList.add("show");
        }

        function cerrarModal() { 
            modal.classList.remove("show"); 
        }

        async function guardarCambios() {
            const id = inputModalId.value;
            const valor = inputModalValor.value.trim();
            
            // Si hay un ID es una edición, si está vacío es un INSERT
            const accion = id === "" ? 'crear' : 'editar'; 

            if (valor === "") {
                alert("El valor no puede estar vacío.");
                return;
            }

            try {
                const response = await fetch('backend_catalogos.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: accion, 
                        tabla: catalogoActualBD,
                        id: id, 
                        valor: valor 
                    })
                });
                const res = await response.json();
                
                if(res.estatus === 'exito') {
                    cerrarModal();
                    cargarDatosCatalogo(); 
                } else {
                    alert("⚠️ " + res.mensaje);
                }
            } catch (error) { alert("Error al guardar cambios"); }
        }

        // Cerrar modal click fuera
        window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
    </script>

    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>