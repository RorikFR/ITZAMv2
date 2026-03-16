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
        <title>Sistema ITZAM — Consultar inventario</title>
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
                
                <div class="topbar-header">Consultar inventario</div>
                
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
            <table id="tablaInventario" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Piezas en stock</th>
                        <th>Proveedor</th>
                        <th>Categoría</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTabla"></tbody>
            </table>
        </div>

    <div id="modalEdicion" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">Editar Artículo de Inventario</div>
            
            <input type="hidden" id="inputModalId"> 
            <input type="hidden" id="inputModalCategoria"> 
            
            <div class="form-group">
                <label>Nombre del artículo:</label>
                <input type="text" id="inputModalNombre">
            </div>
            
            <div class="form-group">
                <label>Stock (Cantidad):</label>
                <input type="number" id="inputModalStock" min="0">
            </div>
            
            <div class="form-group">
                <label>Proveedor:</label>
                <select id="inputModalProveedor">
                    <option value="" disabled selected>Cargando proveedores...</option>
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
        
        // Mapeo de los inputs del modal
        const inputModalId = document.getElementById("inputModalId");
        const inputModalCategoria = document.getElementById("inputModalCategoria");
        const inputModalNombre = document.getElementById("inputModalNombre");
        const inputModalStock = document.getElementById("inputModalStock");
        const inputModalProveedor = document.getElementById("inputModalProveedor");

        // Variable global para la instancia de DataTables
        let tablaInstancia = null; 

        // --- 0. CARGAR CATÁLOGOS (PROVEEDORES) ---
        async function cargarProveedoresModal() {
            try {
                const response = await fetch('backend_consulta_inventario.php?accion=cargar_proveedores');
                const datos = await response.json();
                
                inputModalProveedor.innerHTML = '<option value="" disabled selected>Seleccione un proveedor...</option>';
                
                datos.proveedores.forEach(prov => {
                    const opcion = document.createElement('option');
                    opcion.value = prov.idProveedor; 
                    opcion.textContent = prov.nombre;  
                    inputModalProveedor.appendChild(opcion);
                });
            } catch (error) {
                console.error("Error al cargar proveedores:", error);
            }
        }

        // --- 1. CARGAR DATOS INICIALES (GET TODO) ---
        async function cargarDatosIniciales() {
            if (tablaInstancia !== null) {
                tablaInstancia.destroy();
                tablaInstancia = null;
            }

            cuerpoTabla.innerHTML = "<tr><td colspan='6' style='text-align:center'>Cargando base de datos...</td></tr>";

            try {
                // Pasamos 'Todos' por defecto para que el backend traiga el inventario completo
                const response = await fetch('backend_consulta_inventario.php?cat=Todos');
                const datos = await response.json();
                
                if(datos.error) { alert(datos.error); return; }
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='6' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        // --- RENDERIZAR E INICIALIZAR DATATABLES ---
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='6' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach(item => {
                // Validación visual de Proveedor
                const nombreProveedor = item.Proveedor ? item.Proveedor : '<span style="color:gray; font-style: italic;">Sin proveedor</span>';
                
                // 🔥 ALERTA VISUAL: Si el stock es menor a 10, lo pintamos de rojo
                const stockColor = item.Cantidad < 10 ? 'color: #dc3545; font-weight: bold;' : 'font-weight: bold; color: #198754;';

                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><b>${item.id}</b></td>
                        <td style="font-weight: 500;">${item.Nombre}</td>
                        <td style="${stockColor}">${item.Cantidad}</td>
                        <td>${nombreProveedor}</td>
                        <td><span style="background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-size: 0.9em; border: 1px solid #ced4da;">${item.Categoria}</span></td>
                        <td>
                            <button class="btn-edit" onclick="abrirModal(${item.id}, '${item.Categoria}', '${item.Nombre}', ${item.Cantidad}, ${item.idProveedor || 'null'})">Editar</button>
                            <button class="btn-del" onclick="eliminarRegistro(${item.id}, '${item.Categoria}')">Borrar</button>
                        </td>
                    </tr>
                `;
            });

            // Inicializamos DataTables con diccionario en español
            tablaInstancia = $('#tablaInventario').DataTable({
                language: {
                    "decimal": "",
                    "emptyTable": "No hay información en el inventario",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ artículos",
                    "infoEmpty": "Mostrando 0 a 0 de 0 artículos",
                    "infoFiltered": "(Filtrado de _MAX_ artículos totales)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ artículos por página",
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
                    { extend: 'excelHtml5', text: '📊 Exportar Inventario', className: 'btn-exportar' },
                    { extend: 'csvHtml5', text: '📄 Exportar CSV', className: 'btn-exportar' }
                ],
                pageLength: 15, // Aumenté un poco la paginación porque los inventarios suelen verse mejor con más filas
                ordering: true,
                order: [[2, "asc"]] // 🔥 Opcional: Ordena por cantidad ascendente por defecto (los que tienen menos stock salen primero)
            });
        }

        // --- 2. ELIMINAR (POST) ---
        async function eliminarRegistro(id, categoria) {
            if(!confirm("¿Confirma que desea eliminar este artículo permanentemente del inventario?")) return;

            try {
                const response = await fetch('backend_consulta_inventario.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'eliminar', 
                        id: id, 
                        categoria: categoria 
                    })
                });
                const res = await response.json();
                
                if(res.estatus === 'exito') {
                    alert("✅ " + res.mensaje);
                    cargarDatosIniciales(); // Recargar tabla usando la nueva función
                } else {
                    alert("⚠️ " + res.mensaje);
                }
            } catch (error) { alert("Error al eliminar"); }
        }

        // --- 3. EDITAR (POST) ---
        function abrirModal(id, categoria, nombre, cantidad, idProveedor) {
            inputModalId.value = id;
            inputModalCategoria.value = categoria;
            inputModalNombre.value = nombre;
            inputModalStock.value = cantidad;
            
            inputModalProveedor.value = idProveedor ? idProveedor : "";
            
            modal.classList.add("show");
        }

        function cerrarModal() { modal.classList.remove("show"); }

        async function guardarCambios() {
            const id = inputModalId.value;
            const categoria = inputModalCategoria.value;
            const nombre = inputModalNombre.value;
            const cantidad = inputModalStock.value;
            const idProveedor = inputModalProveedor.value;

            // Escudo de validación Frontend
            if (nombre.trim() === "" || idProveedor === "") {
                alert("⚠️ Por favor, ingresa el nombre del artículo y selecciona un proveedor.");
                return; 
            }

            try {
                const response = await fetch('backend_consulta_inventario.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        accion: 'editar', 
                        id: id, 
                        categoria: categoria,
                        nombre: nombre,
                        cantidad: cantidad,
                        idProveedor: idProveedor
                    })
                });
                const res = await response.json();
                
                if(res.estatus === 'error') {
                    alert("⚠️ Atención:\n\n" + res.mensaje);
                } 
                else if (res.estatus === 'exito') {
                    alert("✅ " + res.mensaje);
                    cerrarModal();
                    cargarDatosIniciales(); // Recargar tabla usando la nueva función
                }
            } catch (error) { alert("Error al guardar cambios"); }
        }

        // Cargas iniciales
        cargarProveedoresModal();
        cargarDatosIniciales();

        // Cerrar modal click fuera
        window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
    </script>

    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>