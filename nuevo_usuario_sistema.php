<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';      
require 'autorizacion.php';   

// RBAC
requerir_roles(['Administrador']);

//Menú dinamico
require 'header.php';
?>

        <div class="title-box">
            <h3>Nuevo usuario del sistema</h3>
        </div>

<div class="grid-wrapper">
    <div class="formulario-background-normal">
        <span class="message">* Campos obligatorios</span>
        
        <form id="formUsuario" novalidate autocomplete="off">
            <div class="tab active" data-step="0" aria-hidden="false">
                <fieldset id="admin-usuario">
                    <legend>Datos de la Cuenta</legend>

                    <label for="idPersonal">*Asignar a Personal (Empleado):</label>
                    <select class="form" id="idPersonal" name="idPersonal" required>
                        <option value="" disabled selected>Cargando personal disponible...</option>
                    </select>

                    <label for="rol">*Rol en el sistema:</label>
                    <select class="form" id="rol" name="rol" required>
                        <option value="" disabled selected>Seleccione un rol...</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Médico">Médico</option>
                        <option value="Enfermería">Enfermería</option>
                        <option value="Administrativo">Administrativo</option>
                    </select>

                    <label for="nom-usuario">*Nombre de usuario:</label>
                    <input class="form" type="text" id="nom-usuario" name="nombre_usuario" required placeholder="Ej. dr_ramos" maxlength="50" pattern="^[a-zA-Z0-9_]+$" title="Solo letras, números y guiones bajos. Sin espacios." />

                    <label for="email-usuario">*Email:</label>
                    <input class="form" type="email" id="email-usuario" name="email" required placeholder="correo@clinica.com" maxlength="100" />

                    <label for="pass-usuario">*Contraseña:</label>
                    <input class="form" type="password" id="pass-usuario" name="contrasena" required placeholder="Mínimo 8 caracteres" minlength="8" />

                    <label for="pass-usuario-verify">*Verificar contraseña:</label>
                    <input class="form" type="password" id="pass-usuario-verify" required placeholder="Repita la contraseña" />
                </fieldset>
            </div>
        </form>
        
        <div class="step-controls">
            <button class="multi-btn-clear" type="button" id="clearBtn" style="background-color: #6c757d;">Limpiar campos</button>
            <button class="multi-btn-submit" type="submit" id="submitBtn" form="formUsuario">Crear usuario</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const form = document.getElementById('formUsuario');
    const pass1 = document.getElementById('pass-usuario');
    const pass2 = document.getElementById('pass-usuario-verify');
    const selectPersonal = document.getElementById('idPersonal');
    const selectRol = document.getElementById('rol');
    const inputEmail = document.getElementById('email-usuario'); 
    const submitBtn = document.getElementById('submitBtn');
    const clearBtn = document.getElementById('clearBtn');

    let listaPersonal = []; 

    //Validar si usuario administrador ya existe
    try {
        const resAdmin = await fetch('backend_verificar_admin.php');
        const dataAdmin = await resAdmin.json();
        
        // Si ya existe un administrador, bloqueamos la opción.
        if (dataAdmin.existe_admin) {
            const opAdmin = selectRol.querySelector('option[value="Administrador"]');
            if (opAdmin) {
                opAdmin.disabled = true;
                opAdmin.textContent = "Administrador (Límite de 1 cuenta alcanzado)";
            }
        }
    } catch (e) {
        console.error("Error al verificar disponibilidad de roles.");
    }

    //Cargar personal sin usuario en sistema
    try {
        const res = await fetch('backend_get_personal_sin_usuario.php');
        listaPersonal = await res.json();
        
        if (listaPersonal.length === 0) {
            selectPersonal.innerHTML = '<option value="" disabled selected>No hay personal sin usuario asignado</option>';
        } else {
            selectPersonal.innerHTML = '<option value="" disabled selected>Seleccione un empleado...</option>';
            listaPersonal.forEach(p => {
                selectPersonal.innerHTML += `<option value="${p.idPersonal}">${p.nombre_completo} (${p.puesto})</option>`;
            });
        }
    } catch (e) {
        selectPersonal.innerHTML = '<option value="" disabled selected>Error de conexión al cargar personal</option>';
    }

    // Obtener email de DB
    selectPersonal.addEventListener('change', (e) => {
        const idSeleccionado = e.target.value;
        const empleado = listaPersonal.find(p => p.idPersonal == idSeleccionado);
        
        if (empleado && empleado.email_heredado) {
            inputEmail.value = empleado.email_heredado;
            inputEmail.readOnly = true;
            inputEmail.style.backgroundColor = '#e9ecef';
            inputEmail.style.cursor = 'not-allowed';
            inputEmail.title = "Correo heredado del perfil del empleado.";
        } else {
            inputEmail.value = '';
            inputEmail.readOnly = false;
            inputEmail.style.backgroundColor = '#ffffff';
            inputEmail.style.cursor = 'text';
            inputEmail.placeholder = "Este empleado no tiene correo. Escriba uno.";
            inputEmail.title = "";
        }
    });

    //Validar que contraseñas coincidan
    function validarPasswords() {
        if (pass2.value === '') {
            pass2.style.borderColor = '';
            pass2.setCustomValidity('');
        } else if (pass1.value !== pass2.value) {
            pass2.style.borderColor = '#dc3545'; 
            pass2.setCustomValidity('Las contraseñas no coinciden');
        } else {
            pass2.style.borderColor = '#198754'; 
            pass2.setCustomValidity('');
        }
    }

    pass1.addEventListener('input', validarPasswords);
    pass2.addEventListener('input', validarPasswords);

    //Limpiar formulario
    clearBtn.addEventListener('click', () => {
        if(confirm("¿Deseas borrar todos los datos del formulario?")) {
            form.reset();
            pass2.style.borderColor = '';
            
            // Devolvemos el campo de correo a su estado normal
            inputEmail.readOnly = false;
            inputEmail.style.backgroundColor = '#ffffff';
            inputEmail.style.cursor = 'text';
            inputEmail.placeholder = "correo@clinica.com";
            inputEmail.title = "";
        }
    });

    //Enviar formulario (JSON)
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (pass1.value !== pass2.value) {
            alert("Las contraseñas no coinciden.");
            pass2.focus();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = "Creando usuario...";

        const formData = new FormData(form);
        const dataObj = Object.fromEntries(formData.entries());

        try {
            const res = await fetch('backend_nuevo_usuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataObj)
            });
            const data = await res.json();

            if (data.estatus === 'exito') {
                alert(data.mensaje);
                form.reset();
                pass2.style.borderColor = '';
                location.reload(); 
            } else {
                alert("Atención:\n\n" + data.mensaje);
            }
        } catch (error) {
            alert("Error de conexión al servidor.");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Crear usuario";
        }
    });
});
</script>

<script src="Scripts/js/timeout.js"></script>
        
        <footer class="bottombar">© 2026 ITZAM</footer>
    </body>
</html>