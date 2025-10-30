<?php
/**
 * Script para verificar y habilitar extensión GD
 */

echo "<h2>Verificación de Extensión GD para Códigos QR</h2>";

// Verificar si GD está instalada
if (extension_loaded('gd')) {
    echo "<p style='color: green;'>✅ La extensión GD está HABILITADA</p>";
    
    // Mostrar información de GD
    $gdInfo = gd_info();
    echo "<h3>Información de GD:</h3>";
    echo "<ul>";
    foreach ($gdInfo as $key => $value) {
        echo "<li><strong>$key:</strong> " . ($value === true ? 'Sí' : ($value === false ? 'No' : $value)) . "</li>";
    }
    echo "</ul>";
    
} else {
    echo "<p style='color: red;'>❌ La extensión GD NO está habilitada</p>";
    echo "<h3>Cómo habilitar GD en XAMPP:</h3>";
    echo "<ol>";
    echo "<li>Abrir el archivo <code>C:\\xampp\\php\\php.ini</code></li>";
    echo "<li>Buscar la línea: <code>;extension=gd</code></li>";
    echo "<li>Quitar el punto y coma (;) al inicio: <code>extension=gd</code></li>";
    echo "<li>Guardar el archivo</li>";
    echo "<li>Reiniciar Apache desde el panel de XAMPP</li>";
    echo "</ol>";
    
    echo "<h3>O usar este comando:</h3>";
    echo "<pre>En PowerShell como Administrador:\n";
    echo "cd C:\\xampp\\php\n";
    echo "(Get-Content php.ini) -replace ';extension=gd', 'extension=gd' | Set-Content php.ini\n";
    echo "Luego reiniciar Apache</pre>";
}

echo "<hr>";
echo "<p><a href='test_qr_pdf.php'>← Volver al test</a></p>";
