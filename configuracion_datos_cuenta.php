<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';       
require 'autorizacion.php';   

//RBAC
requerir_roles(['Administrador', 'Médico', 'Administrativo', 'Enfermería']);

//Menu dinamico
require 'header.php';
?>
        <div class="title-box">
            <h1>Datos de mi cuenta</h1>
        </div>

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
            
            <form id="formCorreo" novalidate autocomplete="off">
                <input type="hidden" id="inputModalIdUsuario"> 
                
                <div class="form-group">
                    <label>Nombre de Usuario:</label>
                    <input type="text" id="inputModalUsuario" disabled style="background-color: #e9ecef; cursor: not-allowed;">
                </div>
                
                <div class="form-group">
                    <label for="inputModalEmail">Nuevo Correo Electrónico:</label>
                    <input type="email" id="inputModalEmail" required maxlength="100" placeholder="ejemplo@correo.com">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-save" id="btnSaveCorreo" onclick="guardarCambios()">Guardar Cambios</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalPassword" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">Cambiar Contraseña</div>
            
            <form id="formPassword" novalidate autocomplete="off">
                <input type="hidden" id="inputModalIdPass"> 
                
                <div class="form-group">
                    <label for="inputPassActual">Contraseña Actual:</label>
                    <input type="password" id="inputPassActual" required placeholder="Ingresa tu contraseña actual">
                </div>
                
                <div class="form-group">
                    <label for="inputPassNuevo">Nueva Contraseña:</label>
                    <input type="password" id="inputPassNuevo" required minlength="8" placeholder="Mínimo 8 caracteres">
                </div>

                <div class="form-group">
                    <label for="inputPassConfirmar">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="inputPassConfirmar" required placeholder="Repite la nueva contraseña">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-save" id="btnSavePass" onclick="guardarPassword()">Actualizar Contraseña</button>
                    <button type="button" class="btn-cancel" onclick="cerrarModalPassword()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const cuerpoTabla = document.getElementById("cuerpoTabla");
        
        // Modal email
        const modal = document.getElementById("modalEdicion");
        const formCorreo = document.getElementById("formCorreo");
        const inputModalIdUsuario = document.getElementById("inputModalIdUsuario");
        const inputModalUsuario = document.getElementById("inputModalUsuario");
        const inputModalEmail = document.getElementById("inputModalEmail");
        const btnSaveCorreo = document.getElementById("btnSaveCorreo");

        // Modal Contraseña
        const modalPassword = document.getElementById("modalPassword");
        const formPassword = document.getElementById("formPassword");
        const inputModalIdPass = document.getElementById("inputModalIdPass");
        const inputPassActual = document.getElementById("inputPassActual");
        const inputPassNuevo = document.getElementById("inputPassNuevo");
        const inputPassConfirmar = document.getElementById("inputPassConfirmar");
        const btnSavePass = document.getElementById("btnSavePass");

        // Validar que contraseñas coincidan
        function validarPasswordsModal() {
            if (inputPassConfirmar.value === '') {
                inputPassConfirmar.style.borderColor = '';
                inputPassConfirmar.setCustomValidity('');
            } else if (inputPassNuevo.value !== inputPassConfirmar.value) {
                inputPassConfirmar.style.borderColor = '#dc3545'; 
                inputPassConfirmar.setCustomValidity('Las contraseñas no coinciden');
            } else {
                inputPassConfirmar.style.borderColor = '#198754'; 
                inputPassConfirmar.setCustomValidity('');
            }
        }
        inputPassNuevo.addEventListener('input', validarPasswordsModal);
        inputPassConfirmar.addEventListener('input', validarPasswordsModal);

        //Carga inicial de datos
        async function buscarDatos() {
            cuerpoTabla.innerHTML = "<tr><td colspan='4' style='text-align:center'>Cargando...</td></tr>";

            try {
                const response = await fetch('backend_config_cuenta.php');
                const datos = await response.json();
                
                if(datos.error) { alert(datos.error); return; }
                renderizar(datos);

            } catch (error) {
                cuerpoTabla.innerHTML = "<tr><td colspan='4' style='text-align:center; color:red'>Error de conexión</td></tr>";
            }
        }

        // Renderizar tabla (DataTables)
        function renderizar(datos) {
            cuerpoTabla.innerHTML = "";
            if(datos.length === 0){
                cuerpoTabla.innerHTML = "<tr><td colspan='4' style='text-align:center; padding: 20px;'>No se encontraron resultados</td></tr>";
                return;
            }

            datos.forEach(item => {
                cuerpoTabla.innerHTML += `
                    <tr>
                        <td><strong>@${item['Nombre de usuario']}</strong></td>
                        <td>${item.Email}</td>
                        <td>${item['Fecha de registro']}</td>
                        <td>
                            <button class="btn-edit" onclick="abrirModal(${item.idUsuario}, '${item['Nombre de usuario']}', '${item.Email}')">Actualizar Email</button>
                            <button class="btn-pass" onclick="abrirModalPassword(${item.idUsuario})">Cambiar Contraseña</button>
                        </td>
                    </tr>
                `;
            });
        }

        //Funciones modal correo
        function abrirModal(id, nombre, email) {
            inputModalIdUsuario.value = id;
            inputModalUsuario.value = nombre;
            inputModalEmail.value = email;
            modal.style.display = "flex"; 
        }

        function cerrarModal() { 
            modal.style.display = "none"; 
        }

        async function guardarCambios() {
            if (!formCorreo.checkValidity()) {
                formCorreo.reportValidity();
                return;
            }

            const id = inputModalIdUsuario.value;
            const email = inputModalEmail.value.trim();

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email)) {
                alert("Por favor, ingresa un correo electrónico válido.");
                return;
            }

            btnSaveCorreo.disabled = true;
            btnSaveCorreo.textContent = "Guardando...";

            try {
                const response = await fetch('backend_config_cuenta.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ accion: 'editar', idUsuario: id, email: email })
                });
                
                const res = await response.json();
                
                if(res.estatus === 'exito') {
                    alert("Cambios guardados correctamente.");
                    cerrarModal();
                    buscarDatos(); 
                } else {
                    alert("Atención: " + res.mensaje);
                }
            } catch (error) { 
                alert("Error de red al intentar guardar los cambios."); 
            } finally {
                btnSaveCorreo.disabled = false;
                btnSaveCorreo.textContent = "Guardar Cambios";
            }
        }

        // Funciones modal contraseña
        function abrirModalPassword(id) {
            inputModalIdPass.value = id;
            formPassword.reset();
            inputPassConfirmar.style.borderColor = "";
            modalPassword.style.display = "flex"; 
        }

        function cerrarModalPassword() { 
            modalPassword.style.display = "none"; 
        }

        async function guardarPassword() {
            if (!formPassword.checkValidity()) {
                formPassword.reportValidity();
                return;
            }

            const id = inputModalIdPass.value;
            const actual = inputPassActual.value;
            const nuevo = inputPassNuevo.value;
            const confirmar = inputPassConfirmar.value;

            if (nuevo !== confirmar) {
                alert("Las nuevas contraseñas no coinciden. Intenta de nuevo.");
                inputPassConfirmar.focus();
                return;
            }

            btnSavePass.disabled = true;
            btnSavePass.textContent = "Actualizando...";

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
                    alert("Contraseña actualizada correctamente.");
                    cerrarModalPassword();
                } else {
                    alert("Atención: " + res.mensaje);
                }
            } catch (error) { 
                alert("Error de red al intentar cambiar la contraseña."); 
            } finally {
                btnSavePass.disabled = false;
                btnSavePass.textContent = "Actualizar Contraseña";
            }
        }

        // Recargar datos
        buscarDatos();

        window.onclick = function(ev) { 
            if (ev.target == modal) cerrarModal(); 
            if (ev.target == modalPassword) cerrarModalPassword();
        }
    </script>

    <script src="Scripts/js/timeout.js"></script>

    <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>