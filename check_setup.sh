#!/bin/bash
# Script para verificar configuración completa del proyecto

echo "========================================"
echo "  Verificación del Proyecto Edificio Admin"
echo "========================================"
echo

# Verificar PHP
echo "🔍 Verificando PHP..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    echo "✅ PHP $PHP_VERSION detectado"
else
    echo "❌ PHP no encontrado"
    exit 1
fi

# Verificar MySQL
echo
echo "🔍 Verificando MySQL..."
if command -v mysql &> /dev/null; then
    echo "✅ MySQL detectado"
else
    echo "⚠️  MySQL no encontrado en PATH (puede estar en XAMPP)"
fi

# Verificar archivos importantes
echo
echo "🔍 Verificando archivos del proyecto..."
files=(
    "api/test.php"
    "config/environment.php"
    "includes/cors.php"
    "includes/db.php"
    ".env"
    "start_ngrok.bat"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file existe"
    else
        echo "❌ $file no encontrado"
    fi
done

# Verificar Ngrok
echo
echo "🔍 Verificando Ngrok..."
if command -v ngrok &> /dev/null; then
    NGROK_VERSION=$(ngrok version 2>&1 | head -n 1)
    echo "✅ Ngrok $NGROK_VERSION detectado"
else
    echo "❌ Ngrok no encontrado"
    echo "   Instala desde: https://ngrok.com/download"
fi

# Verificar XAMPP corriendo
echo
echo "🔍 Verificando XAMPP..."
if lsof -i :8080 &> /dev/null; then
    echo "✅ Apache detectado en puerto 8080"
else
    echo "❌ Apache no detectado en puerto 8080"
    echo "   Asegúrate de que XAMPP esté corriendo"
fi

echo
echo "🎯 Próximos pasos:"
echo "1. Ejecuta: ./start_ngrok.bat"
echo "2. Copia la URL HTTPS que aparezca"
echo "3. Prueba accediendo a la URL desde tu navegador"
echo "4. API Test: https://TU_URL.ngrok.io/proyectoAdmEdificios/api/test.php"
echo
echo "📖 Documentación completa en NGROK_SETUP.md"