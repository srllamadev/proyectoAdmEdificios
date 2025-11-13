@echo off
echo ========================================
echo   Configuracion Ngrok - Edificio Admin
echo ========================================
echo.

REM Verificar si ngrok esta instalado
ngrok version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Ngrok no esta instalado.
    echo.
    echo Instala Ngrok desde: https://ngrok.com/download
    echo O usa: choco install ngrok
    pause
    exit /b 1
)

echo ✅ Ngrok detectado
echo.

REM Verificar si XAMPP esta corriendo
netstat -ano | findstr :80 >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ XAMPP no parece estar corriendo en puerto 80
    echo.
    echo Asegúrate de que Apache esté iniciado en XAMPP Control Panel
    pause
    exit /b 1
)

echo ✅ XAMPP detectado en puerto 80
echo.

echo 🔗 Iniciando túnel Ngrok...
echo.
echo IMPORTANTE:
echo 1. Copia la URL HTTPS que aparezca abajo
echo 2. Prueba la conexión accediendo a la URL desde tu navegador
echo 3. API Test: https://TU_URL.ngrok.io/proyectoAdmEdificios/api/test.php
echo.
echo Presiona Ctrl+C para detener el túnel
echo.

ngrok http 80