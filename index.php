<?php
session_start();

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
		<title>Login - ITZAM</title>
		<link rel="stylesheet" href="styles.css" />
		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	</head>
	<body>

	<header>
		<div class="topbar-container-login">
			<div class="topbar-header-login">Sistema web de consulta de información clínica</div>
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

								<button class="btn-login" type="submit">Iniciar sesión</button>
							</form>
							
							<span class="forgot" id="forgot" onclick="abrirModalRecuperacion()">
								Olvidé mi contraseña y/o usuario
							</span>
						</main>
					</div>
				</div>

			</div>
		</div>

		<div id="modalRecuperacion" class="modal-overlay" style="display:none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
			<div class="modal-box" style="background: white; border-radius: 8px; max-width: 420px; text-align: center; padding: 0; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
				<div class="modal-header">
					Recuperación de Acceso
				</div>
				
				<div style="padding: 30px 20px;">
					<h1 style="font-size: 3.5em; margin: 0 0 15px 0; color: #B58500;">🔒</h1>
					
					<h3 style="color: #231F20; margin-bottom: 15px;">¿Problemas para iniciar sesión?</h3>
					
					<p style="color: #97999B; font-size: 0.95em; line-height: 1.6; margin-bottom: 20px;">
						Por políticas de seguridad y protección de datos clínicos, el restablecimiento de contraseñas se realiza estrictamente de forma interna.
					</p>
					
					<p style="color: #231F20; font-weight: bold; font-size: 0.95em; margin-bottom: 25px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; border: 1px dashed #97999B;">
						Comunícate con Soporte para solicitar una nueva clave.
					</p>
					
					<button type="button" class="btn-save" onclick="cerrarModalRecuperacion()">Entendido</button>
				</div>
			</div>
		</div>

		<footer class="bottombar">© 2026 ITZAM</footer>

		<script>
			// Modal
			function abrirModalRecuperacion() {
				document.getElementById('modalRecuperacion').style.display = 'flex';
			}

			function cerrarModalRecuperacion() {
				document.getElementById('modalRecuperacion').style.display = 'none';
			}

			//Cerrar modal
			window.addEventListener('click', function(event) {
				var modal = document.getElementById('modalRecuperacion');
				if (event.target === modal) {
					cerrarModalRecuperacion();
				}
			});

			// Logica de inicio de sesion
			const form = document.getElementById('login-form');
			const errorEl = document.getElementById('error');
            const submitBtn = form.querySelector('button[type="submit"]');

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

                submitBtn.disabled = true;
                submitBtn.textContent = 'Verificando...';

				try {
					const res = await fetch('login.php', {
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
				} catch (err) {
					errorEl.textContent = err.message || 'Error en el inicio de sesión';
					errorEl.style.display = 'block';
					if (typeof grecaptcha !== 'undefined') grecaptcha.reset();
				} finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Iniciar sesión';
                }
			});

			//Alerta por inactividad
			const urlParams = new URLSearchParams(window.location.search);
			if (urlParams.has('timeout')) {
			    errorEl.textContent = "Tu sesión ha expirado por inactividad. Por favor, ingresa de nuevo.";
			    errorEl.style.display = 'block';
			    
			    // Limpiamos la URL para que no se quede el ?timeout=1 si el usuario recarga
			    window.history.replaceState({}, document.title, window.location.pathname);
			}
		</script>

	</body>
</html>