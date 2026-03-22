<?php
date_default_timezone_set('America/Mexico_City');

require 'inactive.php';       // Control de inactividad y cache
require 'autorizacion.php';   // Roles de usuario

//RBAC
requerir_roles(['Médico', 'Enfermería']);

//Menu de navegación dinámico
require 'header.php';
?>

    <div class="title-box">
	   <h3>Formulario de registro de asesoría</h3>
	</div>

    <div class="background">
		<div class="formulario-background-normal">
			<span class="message">* Campos obligatorios</span>
            
            <form class="formulario" id="nueva_asesoria" novalidate>
                <input type="hidden" id="idPaciente" name="idPaciente">

                <fieldset id="datos-paciente">
					<legend>Datos del usuario/paciente</legend>
					
                    <label class="form" for="curp">*CURP:</label>
                    <div style="display: flex; gap: 10px;">
					    <input class="form" type="text" id="curp" name="curp" maxlength="18" style="text-transform: uppercase; width: 100%;" required />
                        <button type="button" id="btn-buscar-curp" class="btn" style="padding: 10px 20px;">Buscar</button>
                    </div>
                    <small id="curp-mensaje" style="color: #d9534f; display: none; margin-bottom: 10px;">Paciente no encontrado.</small>
			
					<label class="form" for="nombre">*Nombre:</label>
                    <input class="form" type="text" id="nombre" name="nombre" readonly style="background-color: #e9ecef;" required />

					<label class="form" for="apellido_paterno">*Apellido paterno:</label>
                	<input class="form" type="text" id="apellido_paterno" name="apellido_paterno" readonly style="background-color: #e9ecef;" required />

					<label class="form" for="apellido_materno">Apellido materno:</label>
                    <input class="form" type="text" id="apellido_materno" name="apellido_materno" readonly style="background-color: #e9ecef;" />
				</fieldset>
                        
				<fieldset id="datos-asesoria">
					<legend>Datos de la asesoría</legend>
					<label class="form" for="motivo">*Motivo de la asesoría:</label>
					<select class="form" id="motivo" name="motivo" required>
						<option value="" disabled selected>Cargando motivos...</option>
					</select>

					<label class="form" for="comentarios">*Comentarios:</label>
					<textarea class="form" id="comentario-asesoria" name="comentarios" rows="8" required></textarea>
				</fieldset>

                <button type="submit" id="submit" class="long-btn">Registrar Asesoría</button>
				<button type="button" id="clear" class="long-btn" style="background-color: #6c757d;">Limpiar campos</button>
            </form>
        </div>
	</div>

<script>
    const form = document.getElementById('nueva_asesoria');
    const inputCurp = document.getElementById('curp');
    const btnBuscarCurp = document.getElementById('btn-buscar-curp');
    const curpMensaje = document.getElementById('curp-mensaje');
    const selectMotivo = document.getElementById('motivo');

    // Cargar catálogo
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const res = await fetch('backend_catalogos.php?tabla=cat_motivos_asesoria');
            const datos = await res.json();
            selectMotivo.innerHTML = '<option value="" disabled selected>Selecciona un motivo</option>';
            if(!datos.error) {
                datos.forEach(item => {
                    const op = document.createElement('option');
                    op.value = item.id;
                    op.textContent = item.valor;
                    selectMotivo.appendChild(op);
                });
            }
        } catch (error) {
            console.error("Error cargando motivos");
            selectMotivo.innerHTML = '<option value="">Error al cargar</option>';
        }
    });

    // Buscar paciente por CURP
    btnBuscarCurp.addEventListener('click', async () => {
        const curp = inputCurp.value.trim().toUpperCase();
        
        //Validar formato CURP
        const curpRegex = /^[A-Z]{4}\d{6}[HM][A-Z]{2}[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/;
        
        if (!curpRegex.test(curp)) {
            alert("⚠️ El formato del CURP es incorrecto. Verifica que tenga 18 caracteres válidos (ej. ABCD123456HDFXYZ09).");
            inputCurp.focus();
            return; 
        }

        btnBuscarCurp.textContent = "...";
        curpMensaje.style.display = 'none';

        try {
            const res = await fetch(`backend_nueva_asesoria.php?accion=buscar_paciente&curp=${curp}`);
            const data = await res.json();

            if(data.estatus === 'exito') {
                //Llenar campos
                document.getElementById('idPaciente').value = data.datos.idPaciente;
                document.getElementById('nombre').value = data.datos.nombre;
                document.getElementById('apellido_paterno').value = data.datos.apellido_p;
                document.getElementById('apellido_materno').value = data.datos.apellido_m || '';
                curpMensaje.style.display = 'none';
            } else {
                //Limpiar campos
                document.getElementById('idPaciente').value = '';
                document.getElementById('nombre').value = '';
                document.getElementById('apellido_paterno').value = '';
                document.getElementById('apellido_materno').value = '';
                curpMensaje.textContent = data.mensaje;
                curpMensaje.style.display = 'block';
            }
        } catch (error) {
            alert("Error de conexión al buscar paciente.");
        } finally {
            btnBuscarCurp.textContent = "Buscar";
        }
    });

    //Enviar formulario
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const idPaciente = document.getElementById('idPaciente').value;
        const idMotivo = selectMotivo.value;
        const comentarios = document.getElementById('comentario-asesoria').value.trim();

        if(!idPaciente) {
            alert("Debes buscar y seleccionar un paciente válido usando su CURP.");
            return;
        }

        if(!idMotivo || !comentarios) {
            alert("El motivo y los comentarios son obligatorios.");
            return;
        }

        const btnSubmit = document.getElementById('submit');
        btnSubmit.disabled = true;
        btnSubmit.textContent = "Guardando...";

        try {
            const res = await fetch('backend_nueva_asesoria.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    idPaciente: idPaciente,
                    idMotivo: idMotivo,
                    comentarios: comentarios
                })
            });
            const data = await res.json();

            if(data.estatus === 'exito') {
                alert("✅ " + data.mensaje);
                form.reset();
                document.getElementById('idPaciente').value = ''; 
            } else {
                alert("⚠️ " + data.mensaje);
            }
        } catch (error) {
            alert("Error de conexión al guardar.");
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = "Registrar Asesoría";
        }
    });

    //Botón limpiar
    document.getElementById('clear').addEventListener('click', () => {
        form.reset();
        document.getElementById('idPaciente').value = '';
        curpMensaje.style.display = 'none';
    });
</script>

<script src="Scripts/js/timeout.js"></script>

        <footer class="bottombar">© 2026 ITZAM</footer>

    </body>
</html>
