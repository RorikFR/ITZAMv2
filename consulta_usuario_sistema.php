<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';      
require 'autorizacion.php';   

//RBAC
requerir_roles(['Administrador']);

//Menu dinamico
require 'header.php';
?>

<br>

   <div class="tabla-container">
    <table id="tablaUsuarios" class="display responsive nowrap" style="width:100%">
        <thead>
            <tr>
                <th class="all">ID</th>
                <th class="all">Nombre de usuario</th>
                <th>Email</th>
                <th class="all">Estatus</th>
                <th>Rol</th>
                <th>Fecha de creación</th>
                <th>Fecha de suspensión</th>
                <th class="all">Acciones</th>
            </tr>
        </thead>
        <tbody id="cuerpoTabla"></tbody>
    </table>
</div>

<div id="modalEdicion" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">Administrar Usuario</div>
        
        <form id="formEdicionUsuario" novalidate autocomplete="off">
            <input type="hidden" id="inputModalIdUsuario"> 
            
            <div class="form-group">
                <label>Nombre de Usuario:</label>
                <input type="text" id="inputModalUsuario" disabled style="background-color: #e9ecef; cursor: not-allowed;">
            </div>
            
            <div class="form-group">
                <label for="inputModalEmail">Correo Electrónico:</label>
                <input type="email" id="inputModalEmail" required maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="inputModalRol">Rol en el Sistema:</label>
                <select id="inputModalRol" required>
                    <option value="Administrador">Administrador</option>
                    <option value="Médico">Médico</option>
                    <option value="Enfermería">Enfermería</option>
                    <option value="Administrativo">Administrativo</option>
                </select>
            </div>

            <div class="form-group">
                <label for="inputModalEstatus">Estatus de la Cuenta:</label>
                <select id="inputModalEstatus" required>
                    <option value="Activo">Activo</option>
                    <option value="Suspendido">Suspendido</option>
                </select>
            </div>
            
            <div class="modal-actions" style="margin-top: 20px;">
                <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button type="button" class="btn-save" id="btnSavePerfil" onclick="guardarCambiosUsuario()">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<div id="modalPassword" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width: 400px;">
        <div class="modal-header" style="background-color: #ffc107; color: #000;">Restablecer Contraseña</div>
        
        <form id="formPasswordModal" novalidate autocomplete="off">
            <input type="hidden" id="passModalIdUsuario"> 
            <p style="margin-bottom: 15px;">Cambiando clave para: <strong id="passModalNombreUsuario" style="font-family: monospace; font-size: 1.1em;"></strong></p>
            
            <div class="form-group">
                <label for="passModalNueva">Nueva Contraseña:</label>
                <input type="password" id="passModalNueva" required minlength="8" placeholder="Mínimo 8 caracteres" autofocus>
            </div>
            
            <div class="form-group">
                <label for="passModalConfirmar">Confirmar Contraseña:</label>
                <input type="password" id="passModalConfirmar" required placeholder="Repita la nueva contraseña">
            </div>
            
            <div class="modal-actions" style="margin-top: 20px;">
                <button type="button" class="btn-cancel" onclick="cerrarModalPass()">Cancelar</button>
                <button type="button" class="btn-save" id="btnSavePass" style="background-color: #ffc107; color: #000; border: none;" onclick="guardarNuevaContrasena()">Actualizar Clave</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
    const cuerpoTabla = document.getElementById("cuerpoTabla");
    
    // Modal 1
    const modal = document.getElementById("modalEdicion");
    const formPerfil = document.getElementById("formEdicionUsuario");
    const inputModalIdUsuario = document.getElementById("inputModalIdUsuario"); 
    const inputModalUsuario = document.getElementById("inputModalUsuario");
    const inputModalEmail = document.getElementById("inputModalEmail"); 
    const inputModalRol = document.getElementById("inputModalRol");
    const inputModalEstatus = document.getElementById("inputModalEstatus");
    const btnSavePerfil = document.getElementById("btnSavePerfil");
    
    // Modal 2
    const modalPass = document.getElementById("modalPassword");
    const formPass = document.getElementById("formPasswordModal");
    const passModalIdUsuario = document.getElementById("passModalIdUsuario");
    const passModalNombreUsuario = document.getElementById("passModalNombreUsuario");
    const passModalNueva = document.getElementById("passModalNueva");
    const passModalConfirmar = document.getElementById("passModalConfirmar");
    const btnSavePass = document.getElementById("btnSavePass");

    let tablaInstancia = null; 
    let adminYaExiste = false;

    // Validar si administrador ya existe
    async function verificarAdminExistente() {
        try {
            const res = await fetch('backend_verificar_admin.php');
            const data = await res.json();
            adminYaExiste = data.existe_admin;
        } catch (e) {
            console.error("Error al verificar roles");
        }
    }

    // Validar que contraseñas coincidan
    function validarPasswordsModal() {
        if (passModalConfirmar.value === '') {
            passModalConfirmar.style.borderColor = '';
            passModalConfirmar.setCustomValidity('');
        } else if (passModalNueva.value !== passModalConfirmar.value) {
            passModalConfirmar.style.borderColor = '#dc3545';
            passModalConfirmar.setCustomValidity('Las contraseñas no coinciden');
        } else {
            passModalConfirmar.style.borderColor = '#198754';
            passModalConfirmar.setCustomValidity('');
        }
    }
    passModalNueva.addEventListener('input', validarPasswordsModal);
    passModalConfirmar.addEventListener('input', validarPasswordsModal);

    //Carga de datos inicial
    async function cargarDatosIniciales() {
        if (tablaInstancia !== null) {
            tablaInstancia.destroy();
            tablaInstancia = null;
        }
        cuerpoTabla.innerHTML = "<tr><td colspan='8' style='text-align:center'>Cargando base de datos de usuarios...</td></tr>";

        try {
            const response = await fetch('backend_consulta_usuarios_admin.php');
            const datos = await response.json();
            
            if(datos.error) { alert("Error: " + datos.error); return; }
            renderizar(datos);
        } catch (error) {
            console.error(error);
            cuerpoTabla.innerHTML = "<tr><td colspan='8' style='text-align:center; color:red'>Error de conexión</td></tr>";
        }
    }

    // Renderizar tabla (DataTables)
    function renderizar(datos) {
        cuerpoTabla.innerHTML = "";
        
        if(datos.length === 0){
            cuerpoTabla.innerHTML = "<tr><td colspan='8' style='text-align:center; padding: 20px;'>No se encontraron usuarios registrados</td></tr>";
            return;
        }

        datos.forEach(item => {
            let colorEstatus = item['Estatus'] === 'Activo' ? '#198754' : '#dc3545';
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
                        <button class="btn-edit" style="margin-right: 5px;" onclick="abrirModal(${item.idUsuario}, '${item['Nombre de usuario']}', '${item.Email}', '${item['Rol']}', '${item['Estatus']}')">Editar</button>
                        
                        <button class="btn-pass" style="background-color: #ffc107; color: #000; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; margin-right: 5px;" onclick="abrirModalPass(${item.idUsuario}, '${item['Nombre de usuario']}')">Cambiar clave</button>
                        
                        <button class="btn-del" onclick="suspenderUsuario(${item.idUsuario}, '${item['Estatus']}')">Suspender</button>
                    </td>
                </tr>
            `;
        });

        tablaInstancia = $('#tablaUsuarios').DataTable({
            responsive: {
                details: {
                    renderer: function (api, rowIdx, columns) {
                        let data = $.map(columns, function (col, i) {
                            return col.hidden ?
                                '<div class="dtr-detalle-celda" data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                                    '<div class="dtr-detalle-titulo">' + col.title + '</div> ' +
                                    '<div class="dtr-detalle-dato">' + col.data + '</div>' +
                                '</div>' : '';
                        }).join('');
                        return data ? $('<div class="dtr-detalle-fila"/>').append(data) : false;
                    }
                }
            },
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
                        "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                },
            dom: '<"top"Bf>rt<"bottom"lip><"clear">',
            buttons: [
                { 
                    extend: 'pdfHtml5', 
                    text: 'Reporte General PDF', 
                    className: 'btn-exportar',
                    orientation: 'landscape', 
                    pageSize: 'LETTER',       
                    title: 'Auditoría de Usuarios - ITZAM',
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] },
                    customize: function (doc) {
                        doc.defaultStyle.fontSize = 8;
                        doc.styles.tableHeader.fontSize = 9;
                        doc.defaultStyle.alignment = 'center';
                        doc.styles.tableHeader.alignment = 'center';
                        doc.content[1].table.widths = ['auto', '*', '*', 'auto', 'auto', 'auto', 'auto'];
                    }
                },
                { extend: 'csvHtml5', text: 'Reporte General CSV', className: 'btn-exportar', exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] } }
            ],
            pageLength: 10,
            ordering: true,
            order: [[0, "desc"]] 
        });
    }

    // Suspender usuario
    async function suspenderUsuario(idUsuario, estatus) {
        if(estatus === 'Suspendido') {
            alert("Este usuario ya se encuentra suspendido.");
            return;
        }
        if(!confirm("¿Confirma que desea suspender el acceso a este usuario? Ya no podrá iniciar sesión en el sistema.")) return;

        try {
            const response = await fetch('backend_consulta_usuarios_admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: 'suspender', idUsuario: idUsuario })
            });
            const res = await response.json();
            
            if(res.estatus === 'exito') {
                alert("Usuario suspendido correctamente.");
                cargarDatosIniciales(); 
            } else {
                alert("Atención: " + res.mensaje);
            }
        } catch (error) { alert("Error de conexión al suspender usuario"); }
    }

    // Editar usuario
    function abrirModal(idUsuario, nombre, email, rolActual, estatus) {
        inputModalIdUsuario.value = idUsuario;
        inputModalUsuario.value = nombre;
        inputModalEmail.value = email;
        inputModalEstatus.value = estatus;

        //Bloquear rol de administrador si ya existe
        const opAdmin = inputModalRol.querySelector('option[value="Administrador"]');
        if (adminYaExiste && rolActual !== 'Administrador') {
            opAdmin.disabled = true;
            opAdmin.textContent = "Administrador (Límite alcanzado)";
        } else {
            opAdmin.disabled = false;
            opAdmin.textContent = "Administrador";
        }
        
        inputModalRol.value = rolActual;
        modal.classList.add("show");
    }

    function cerrarModal() { modal.classList.remove("show"); }

    async function guardarCambiosUsuario() {
        if (!formPerfil.checkValidity()) {
            formPerfil.reportValidity();
            return;
        }

        const idUsuario = inputModalIdUsuario.value;
        const email = inputModalEmail.value.trim();
        const rol = inputModalRol.value;
        const estatus = inputModalEstatus.value;

        btnSavePerfil.disabled = true;
        btnSavePerfil.textContent = "Guardando...";

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
                alert("Perfil guardado correctamente.");
                cerrarModal();
                cargarDatosIniciales(); 
            } else {
                alert("Atención: " + res.mensaje);
            }
        } catch (error) { 
            alert("Error al guardar cambios de red."); 
        } finally {
            btnSavePerfil.disabled = false;
            btnSavePerfil.textContent = "Guardar Cambios";
        }
    }

    // Modificar contraseña
    function abrirModalPass(idUsuario, nombreUsuario) {
        passModalIdUsuario.value = idUsuario;
        passModalNombreUsuario.innerText = "@" + nombreUsuario;
        formPass.reset();
        passModalConfirmar.style.borderColor = "";
        modalPass.classList.add("show");
    }

    function cerrarModalPass() { modalPass.classList.remove("show"); }

    async function guardarNuevaContrasena() {
        if (!formPass.checkValidity()) {
            formPass.reportValidity();
            return;
        }

        const idUsuario = passModalIdUsuario.value;
        const nuevaPass = passModalNueva.value;
        const confirmaPass = passModalConfirmar.value;

        if (nuevaPass !== confirmaPass) {
            alert("Las contraseñas no coinciden. Por favor, verifícalas.");
            passModalConfirmar.focus();
            return;
        }

        btnSavePass.disabled = true;
        btnSavePass.textContent = "Actualizando...";

        try {
            const response = await fetch('backend_consulta_usuarios_admin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    accion: 'cambiar_password_unico', 
                    idUsuario: idUsuario, 
                    nueva_pass: nuevaPass 
                })
            });
            
            const res = await response.json();
            
            if(res.estatus === 'exito') {
                alert("Contraseña actualizada correctamente.");
                cerrarModalPass();
            } else {
                alert("Atención: " + res.mensaje);
            }
        } catch (error) { 
            alert("Error al actualizar la contraseña de red."); 
        } finally {
            btnSavePass.disabled = false;
            btnSavePass.textContent = "Actualizar Clave";
        }
    }

    //Recarga de datos
    verificarAdminExistente();
    cargarDatosIniciales();

    // Cerrar modal
    window.onclick = function(ev) { 
        if (ev.target == modal) cerrarModal(); 
        if (ev.target == modalPass) cerrarModalPass();
    }
</script>

<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="Scripts/js/timeout.js"></script>
        
    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>