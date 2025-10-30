# Script para habilitar extensión GD en XAMPP
# Ejecutar como Administrador en PowerShell

Write-Host "=== Habilitando extensión GD en XAMPP ===" -ForegroundColor Green

$phpIniPath = "C:\xampp\php\php.ini"

if (Test-Path $phpIniPath) {
    Write-Host "Archivo php.ini encontrado: $phpIniPath" -ForegroundColor Yellow
    
    # Leer contenido
    $content = Get-Content $phpIniPath -Raw
    
    # Verificar si ya está habilitada
    if ($content -match "^extension=gd" -or $content -match "^extension=php_gd2.dll") {
        Write-Host "La extensión GD ya está habilitada!" -ForegroundColor Green
    } else {
        # Hacer backup
        $backupPath = "$phpIniPath.backup_" + (Get-Date -Format "yyyyMMdd_HHmmss")
        Copy-Item $phpIniPath $backupPath
        Write-Host "Backup creado: $backupPath" -ForegroundColor Cyan
        
        # Habilitar GD
        $content = $content -replace ";extension=gd", "extension=gd"
        $content = $content -replace ";extension=php_gd2.dll", "extension=php_gd2.dll"
        
        # Guardar cambios
        Set-Content -Path $phpIniPath -Value $content
        
        Write-Host "Extensión GD habilitada exitosamente!" -ForegroundColor Green
        Write-Host ""
        Write-Host "IMPORTANTE: Debes reiniciar Apache desde el Panel de Control de XAMPP" -ForegroundColor Yellow
        Write-Host ""
        
        # Preguntar si desea reiniciar Apache
        $restart = Read-Host "¿Deseas reiniciar Apache ahora? (S/N)"
        if ($restart -eq "S" -or $restart -eq "s") {
            Write-Host "Deteniendo Apache..." -ForegroundColor Yellow
            & "C:\xampp\apache\bin\httpd.exe" -k stop
            Start-Sleep -Seconds 3
            
            Write-Host "Iniciando Apache..." -ForegroundColor Yellow
            & "C:\xampp\apache\bin\httpd.exe" -k start
            Start-Sleep -Seconds 2
            
            Write-Host "Apache reiniciado!" -ForegroundColor Green
        } else {
            Write-Host "Por favor, reinicia Apache manualmente desde el panel de XAMPP" -ForegroundColor Yellow
        }
    }
} else {
    Write-Host "ERROR: No se encontró el archivo php.ini en $phpIniPath" -ForegroundColor Red
    Write-Host "Por favor, verifica la ruta de instalación de XAMPP" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Para verificar que GD está habilitada, abre: http://localhost/proyectoAdmEdificios/check_gd.php" -ForegroundColor Cyan
Read-Host "Presiona Enter para cerrar"
