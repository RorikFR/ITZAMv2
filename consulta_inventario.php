<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';       
require 'autorizacion.php';   

//RBAC
requerir_roles(['Administrativo', 'Administrador']);

//Menú dinámico
require 'header.php';
?>

    <br>

        <div class="tabla-container">
            <table id="tablaInventario" class="display responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th class="all">ID</th>
                        <th class="all">Nombre</th>
                        <th class="all">Piezas en stock</th>
                        <th class="min-tablet">Proveedor</th>
                        <th class="min-tablet">Categoría</th>
                        <th class="all">Unidad Médica</th>
                        <th class="min-tablet">Acciones</th>
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
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        const cuerpoTabla = document.getElementById("cuerpoTabla");
        const modal = document.getElementById("modalEdicion");
        
        // Inputs en el modal
        const inputModalId = document.getElementById("inputModalId");
        const inputModalCategoria = document.getElementById("inputModalCategoria");
        const inputModalNombre = document.getElementById("inputModalNombre");
        const inputModalStock = document.getElementById("inputModalStock");
        const inputModalProveedor = document.getElementById("inputModalProveedor");

        let tablaInstancia = null; 

        // Cargar catálogo de proveedores
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

        // Carga inicial
        async function cargarDatosIniciales() {
            if (tablaInstancia !== null) {
                tablaInstancia.destroy();
                tablaInstancia = null;
            }

            cuerpoTabla.innerHTML = "<tr><td colspan='7' style='text-align:center'>Cargando base de datos...</td></tr>";

            try {
                const response = await fetch('backend_consulta_inventario.php?cat=Todos');
                const datos = await response.json();
                
                if(datos.error) { alert(datos.error); return; }
                renderizar(datos);

            } catch (error) {
                console.error(error);
                cuerpoTabla.innerHTML = "<tr><td colspan='7' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        // Renderizar e iniciar DataTables
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='7' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach(item => {
                const nombreProveedor = item.Proveedor ? item.Proveedor : '<span style="color:gray; font-style: italic;">Sin proveedor</span>';
                
                // Alerta de bajo stock
                const stockColor = item.Cantidad < 10 ? 'color: #dc3545; font-weight: bold;' : 'font-weight: bold; color: #198754;';

                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><b>${item.id}</b></td>
                        <td style="font-weight: 500;">${item.Nombre}</td>
                        <td style="${stockColor}">${item.Cantidad}</td>
                        <td>${nombreProveedor}</td>
                        <td><span style="background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-size: 0.9em; border: 1px solid #ced4da;">${item.Categoria}</span></td>
                        <td><b>${item.Unidad || 'S/D'}</b></td>
                        <td>
                            <button class="btn-edit" onclick="abrirModal(${item.id}, '${item.Categoria}', '${item.Nombre.replace(/'/g, "\\'")}', ${item.Cantidad}, ${item.idProveedor || 'null'})">Editar</button>
                            <button class="btn-del" onclick="eliminarRegistro(${item.id}, '${item.Categoria}')">Borrar</button>
                        </td>
                    </tr>
                `;
            });

            tablaInstancia = $('#tablaInventario').DataTable({
                responsive: true,
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
                dom: '<"top"Bf>rt<"bottom"lip><"clear">',
                buttons: [
                    { 
                        extend: 'pdfHtml5', 
                        text: 'Reporte general PDF', 
                        className: 'btn-exportar',
                        orientation: 'landscape', 
                        pageSize: 'LETTER',       
                        title: 'Reporte de inventario - ITZAM',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5] 
                        }
                    },
                    { 
                        extend: 'csvHtml5', 
                        text: 'Reporte general CSV', 
                        className: 'btn-exportar',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5] 
                        }
                    }
                ],
                pageLength: 10,
                ordering: true,
                order: [[5, "asc"], [1, "asc"]] 
            });
        }

        // Eliminar registro
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
                    cargarDatosIniciales(); 
                } else {
                    alert("⚠️ " + res.mensaje);
                }
            } catch (error) { alert("Error al eliminar"); }
        }

        //Editar registro
        function abrirModal(id, categoria, nombre, cantidad, idProveedor) {
            inputModalId.value = id;
            inputModalCategoria.value = categoria;
            inputModalNombre.value = nombre;
            inputModalStock.value = cantidad;
            
            inputModalProveedor.value = idProveedor ? idProveedor : "";

            inputModalNombre.readOnly = true;
            inputModalNombre.style.backgroundColor = '#e9ecef';
            inputModalNombre.style.cursor = 'not-allowed';
            inputModalNombre.title = "Este nombre pertenece al Catálogo Maestro y no puede modificarse desde aquí.";
            
            modal.classList.add("show");
        }

        function cerrarModal() { modal.classList.remove("show"); }

        async function guardarCambios() {
            const id = inputModalId.value;
            const categoria = inputModalCategoria.value;
            const cantidad = inputModalStock.value;
            const idProveedor = inputModalProveedor.value;

            // Validacion de campos vacios
            if (idProveedor === "" || cantidad === "") {
                alert("⚠️ Por favor, ingresa una cantidad y selecciona un proveedor.");
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
                    cargarDatosIniciales(); 
                }
            } catch (error) { alert("Error al guardar cambios"); }
        }

        // Cargas iniciales
        cargarProveedoresModal();
        cargarDatosIniciales();

        // Cerrar modal 
        window.onclick = function(ev) { if (ev.target == modal) cerrarModal(); }
    </script>

    <script src="Scripts/js/timeout.js"></script>

    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>