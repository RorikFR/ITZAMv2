<?php
// Escribe aquí la contraseña real que quieres usar para tu administrador
$contrasena_plana = "secret"; 

// Generamos el hash seguro usando bcrypt
$hash = password_hash($contrasena_plana, PASSWORD_BCRYPT);

echo "<h3>Tu contraseña encriptada está lista:</h3>";
echo "<p style='background:#eee; padding:10px; font-family:monospace; font-size:16px;'><b>" . $hash . "</b></p>";
echo "<p>Copia el texto de arriba y pégalo en la columna 'contrasena' de tu tabla usuarios_sistema en phpMyAdmin.</p>";
?>