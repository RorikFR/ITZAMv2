<?php
session_start();

// Opcional: Si el usuario YA tiene sesión, lo mandamos directo al sistema para que no vea el login de nuevo.
if (isset($_SESSION['idUsuario'])) {
    header("Location: home.php"); 
    exit;
}
?>
<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width,initial-scale=1" />
		<title>Login</title>
		<link rel="stylesheet" href="styles.css" />
		<script src="https://www.google.com/recaptcha/api.js" async defer></script>

	</head>
	<body>

	<header>
		<div class="topbar-container-login">
			
			<div class="topbar-header-login">Sistema web de consulta de información clínica ITZAM</div>
			
		</div>
	</header>

		<div class="split-container">
			
			<div class="left-side">
				<img src="Assets/itzam_logoV2.png" alt="ITZAM Logo Central" />
			</div>

			<div class="right-side">
				
				<div class="page-center">
					<div class="page">
						<main class="card" role="main">
							<h2 class="form-title">Iniciar sesión</h2>

							<form id="login-form" novalidate>
								<label class="login" for="username">Usuario</label>
								<input class="login" id="username" name="username" type="text" autocomplete="username" required>

								<label class="login" for="password">Contraseña</label>
								<input class="login" id="password" name="password" type="password" autocomplete="current-password" required>

								<div class="recaptcha-wrap">
									<div class="g-recaptcha" data-sitekey="6LcjXlcsAAAAAGtjbpH31zpJXCUnxY5I8PxnW_Gi"></div>
								</div>
								<div id="error" class="err" aria-live="polite"></div>

								<button class="btn" type="submit">Iniciar sesión</button>
							</form>
							<span class="forgot" id="forgot">Olvide mi contraseña y/o usuario</span>
						</main>
					</div>
				</div>

			</div>
		</div>

<div id="myModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">&times;</span>
      <h2>¿Olvidaste tus credenciales de acceso?</h2>
    </div>
    <div class="modal-body">
      <p class="modal-body" id="recover-instructions">
        Ingresa tu correo electrónico registrado para solicitar al administrador el restablecimiento de tu acceso.
      </p>
      
      <input type="email" id="recover-email" class="login" placeholder="ejemplo@correo.com" style="width: 90%; margin-bottom: 15px;" required>
      
      <button class="btn" id="btn-recover" onclick="enviarSolicitudRecuperacion()">Enviar solicitud</button>
      
      <p id="recover-message" class="modal-body" style="display:none; font-weight:bold; margin-top:15px;"></p>
    </div>
  </div>
</div>

<script>
	// --- LÓGICA DE RECUPERACIÓN DE CONTRASEÑA (POR CORREO) ---
    async function enviarSolicitudRecuperacion() {
        const email = document.getElementById('recover-email').value.trim();
        const msgEl = document.getElementById('recover-message');
        const btn = document.getElementById('btn-recover');

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email)) {
            msgEl.style.display = 'block';
            msgEl.style.color = '#d9534f'; 
            msgEl.textContent = 'Por favor, ingresa un correo electrónico válido.';
            return;
        }

        btn.textContent = 'Enviando...';
        btn.disabled = true;
        msgEl.style.display = 'none';

        try {
            const res = await fetch('backend_recuperar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email }) 
            });
            const data = await res.json();

            msgEl.style.display = 'block';
            
            if (data.estatus === 'exito') {
                msgEl.style.color = '#5cb85c'; 
                msgEl.textContent = '✅ ' + data.mensaje;
                
                document.getElementById('recover-email').style.display = 'none';
                btn.style.display = 'none';
                document.getElementById('recover-instructions').style.display = 'none';
                
            } else {
                msgEl.style.color = '#d9534f'; 
                msgEl.textContent = '⚠️ ' + data.mensaje;
                btn.textContent = 'Enviar solicitud';
                btn.disabled = false;
            }
            
        } catch (err) {
            msgEl.style.display = 'block';
            msgEl.style.color = '#d9534f';
            msgEl.textContent = 'Error de conexión con el servidor.';
            btn.textContent = 'Enviar solicitud';
            btn.disabled = false;
        }
    }
</script>

	<footer class="bottombar">© 2026 ITZAM</footer>

<script>
	var modal = document.getElementById("myModal");
	var btn = document.getElementById("forgot");
	var span = document.getElementsByClassName("close")[0];

	btn.onclick = function() {
	  modal.style.display = "block";
	}

	span.onclick = function() {
	  modal.style.display = "none";
	}

	window.onclick = function(event) {
	  if (event.target == modal) {
	    modal.style.display = "none";
	  }
	}
</script>
		
		<script>
			const form = document.getElementById('login-form');
			const errorEl = document.getElementById('error');

			form.addEventListener('submit', async (ev) => {
				ev.preventDefault();
				errorEl.style.display = 'none';
				if (typeof grecaptcha === 'undefined') {
					errorEl.textContent = 'reCAPTCHA no está cargado.';
					errorEl.style.display = 'block';
					return;
				}

				const token = grecaptcha.getResponse();
				if (!token) {
					errorEl.textContent = 'Por favor completa el reCAPTCHA.';
					errorEl.style.display = 'block';
					return;
				}

				const payload = {
					username: document.getElementById('username').value,
					password: document.getElementById('password').value,
					'g-recaptcha-response': token
				};

				try {
					const res = await fetch('/login.php', {
						method: 'POST',
						headers: {'Content-Type':'application/json'},
						body: JSON.stringify(payload)
					});

					const data = await res.json();
					if (!res.ok) throw new Error(data.error || 'Ocurrió un error, intenta de nuevo.');
					if (data && data.redirect) {
						window.location.href = data.redirect;
						return;
					}
					alert('Inicio de sesión correcto');
				} catch (err) {
					errorEl.textContent = err.message || 'Error en el inicio de sesión';
					errorEl.style.display = 'block';
					if (typeof grecaptcha !== 'undefined') grecaptcha.reset();
				}
			});
		</script>

	</body>
</html>