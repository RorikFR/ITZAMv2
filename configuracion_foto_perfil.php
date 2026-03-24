<?php
date_default_timezone_set('America/Mexico_City');

//Validaciones de seguridad e inactividad
require 'inactive.php';      
require 'autorizacion.php';   

//RBAC
requerir_roles(['Administrador', 'Médico', 'Administrativo', 'Enfermería']);

//Menu dinamico
require 'header.php';

//Usar imagen por defecto
$foto_actual = isset($_SESSION['foto_perfil']) && $_SESSION['foto_perfil'] !== '' 
               ? $_SESSION['foto_perfil'] 
               : 'Assets/img_placeholder.png';
?>
        <div class="title-box">
            <h3>Modificar foto de perfil</h3>
        </div>

        <div class="grid-wrapper">
            <div class="formulario-background-normal" style="text-align: center; padding: 30px;">
                
                <form id="form-foto-perfil" action="" method="post" enctype="multipart/form-data">
                    
                    <div style="margin-bottom: 20px;">
                        <img id="profile-picture" class="profile-picture" src="<?= htmlspecialchars($foto_actual) ?>">
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
            const btnSubmit = document.getElementById('submitBtn');

            // Pre-visualizar foto de perfil
            inputFoto.addEventListener('change', function(event) {
                const file = event.target.files[0];
                
                if (file) {
                    if (!validarArchivo(file)) {
                        inputFoto.value = ''; 
                        return;
                    }

                    // Previsualización local
                    const objectUrl = URL.createObjectURL(file);
                    profilePicture.src = objectUrl;
                }
            });

            //Validar tipo de archivo, tamaño y nombre
            function validarArchivo(file) {
                const maxSizeMB = 2; 
                const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                const maxNameLength = 50;

                if (!validTypes.includes(file.type)) {
                    alert("Tipo de archivo no permitido. Solo se aceptan imágenes JPG, PNG o WEBP.");
                    return false;
                }

                if (file.size > (maxSizeMB * 1024 * 1024)) {
                    alert(`El archivo es muy pesado. El límite máximo es de ${maxSizeMB}MB.`);
                    return false;
                }

                if (file.name.length > maxNameLength) {
                    alert(`El nombre del archivo es demasiado largo (máximo ${maxNameLength} caracteres). Renómbralo e intenta de nuevo.`);
                    return false;
                }

                return true; 
            }

            //Cargar archivo al servidor
            async function subirFoto() {
                const file = inputFoto.files[0];
                
                if (!file) {
                    alert("Por favor, selecciona una imagen primero.");
                    return;
                }

                if (!validarArchivo(file)) return;

                const formData = new FormData();
                formData.append('foto_perfil', file);
                formData.append('accion', 'subir_foto');

                btnSubmit.innerText = "Subiendo...";
                btnSubmit.disabled = true;

                try {
                    const response = await fetch('backend_foto_perfil.php', {
                        method: 'POST',
                        body: formData 
                    });
                    
                    const datos = await response.json();
                    
                    if (datos.estatus === 'exito') {
                        alert(datos.mensaje);
                        
                        const rutaFresca = datos.nueva_ruta + '?t=' + new Date().getTime();
                        
                        document.getElementById('profile-picture').src = rutaFresca;
                        
                        const headerPhoto = document.getElementById('header-user-photo');
                        if (headerPhoto) {
                            headerPhoto.src = rutaFresca;
                        }
                        
                        //Limpiar input
                        inputFoto.value = '';
                        
                    } else {
                        alert("Atención: " + datos.mensaje);
                    }
                } catch (error) {
                    alert("Error de conexión al intentar subir la foto.");
                } finally {
                    btnSubmit.innerText = "Subir foto";
                    btnSubmit.disabled = false;
                }
            }
        </script>

        <script src="Scripts/js/timeout.js"></script>
    </body>
</html>