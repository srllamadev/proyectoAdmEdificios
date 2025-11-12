<?php
/**
 * Script para generar el hash de la contraseña del administrador
 * Ejecutar este archivo una vez para obtener el hash y luego insertarlo en la base de datos
 */

$password = 'ko87K#adm-0';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n\n";

echo "Ejecuta este SQL en phpMyAdmin:\n\n";
echo "INSERT INTO usuarios (email, password, nombre, rol) VALUES \n";
echo "('admin@admin.com', '$hash', 'Administrador', 'admin');\n";
?>
