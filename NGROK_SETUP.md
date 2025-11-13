# Configuración Ngrok para Proyecto Edificio Admin

## 🚀 Despliegue con Ngrok

Este proyecto está configurado para usar Ngrok durante desarrollo y testing.

### 📋 Requisitos Previos

1. **Instalar Ngrok**:
   ```bash
   # Windows con Chocolatey
   choco install ngrok

   # O descargar manualmente desde https://ngrok.com/download
   ```

2. **Configurar Token de Ngrok** (Recomendado):
   ```bash
   # Obtén tu token gratis en https://dashboard.ngrok.com/get-started/your-authtoken
   ngrok config add-authtoken TU_TOKEN_AQUI
   ```

### 🔧 Configuración

1. **Verificar puertos**:
   - ✅ Apache (XAMPP): Puerto **80**
   - ⚠️ Oracle XE: Puerto **8080** (conflicto)

2. **Ejecutar Ngrok apuntando al puerto correcto**:
   ```bash
   # ✅ CORRECTO: Apunta a Apache (puerto 80)
   ngrok http 80

   # ❌ INCORRECTO: Apunta a Oracle XE (puerto 8080)
   # ngrok http 8080
   ```

3. **Obtener URL**:
   Ngrok te dará una URL como:
   ```
   https://abc123.ngrok.io -> http://localhost:80
   ```

4. **Probar configuración**:
   - Accede a: `https://abc123.ngrok.io/proyectoAdmEdificios/`
   - API Test: `https://abc123.ngrok.io/proyectoAdmEdificios/api/test.php`

### 🧪 Probar

1. **Verificar que Apache esté corriendo** en puerto 80
2. **Ejecutar**: `ngrok http 80`
3. **Probar web**: Abre `https://abc123.ngrok.io/proyectoAdmEdificios/`
4. **Probar API**: Abre `https://abc123.ngrok.io/proyectoAdmEdificios/api/test.php`

### ⚠️ Consideraciones

- **URLs temporales**: Cambian cada reinicio (8 horas máximo gratuito)
- **Seguridad**: Configura autenticación si es necesario
- **Rendimiento**: Un poco más lento que localhost directo
- **Producción**: Usa un VPS real para producción

### 🔒 Seguridad

Para mayor seguridad, puedes agregar autenticación básica:
```bash
ngrok http 80 --basic-auth="admin:password123"
```