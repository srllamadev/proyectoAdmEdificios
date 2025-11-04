# Sistema de Seguridad y Recuperación de Contraseña

## Funcionalidades Implementadas

### 1. 📧 Email del Administrador Actualizado

**Email anterior:** ~~`admin@edificio.com`~~ / ~~`llamakachera@gmail.com`~~  
**Email actual:** `admin@admin.com`  
**Contraseña:** `ko87K#adm-0`

El email ha sido actualizado en la base de datos.

---

### 2. Sistema de Recuperación de Contraseña

El sistema ahora permite a los usuarios recuperar su contraseña si la olvidan.

#### Flujo de Recuperación:

1. **Usuario olvida su contraseña**
   - Va a la página de login
   - Hace clic en "¿Olvidaste tu contraseña?"
   - Es redirigido a: `forgot-password.php`

2. **Solicitud de recuperación**
   - Ingresa su email registrado
   - Sistema genera un token único y seguro
   - Token válido por 1 hora

3. **Envío de correo**
   - **Modo Desarrollo (actual):** El correo se guarda en `logs/emails/` y el enlace se muestra en pantalla
   - **Modo Producción:** El correo se envía al email registrado

4. **Restablecimiento**
   - Usuario hace clic en el enlace del correo
   - Ingresa su nueva contraseña
   - Sistema valida la fortaleza de la contraseña
   - Contraseña se actualiza con hash seguro Argon2ID

5. **Confirmación**
   - Usuario recibe correo de confirmación del cambio
   - Intentos fallidos se resetean
   - Bloqueos de cuenta se eliminan

#### Archivos Involucrados:
- `forgot-password.php` - Formulario de solicitud
- `reset-password.php` - Formulario de restablecimiento
- `includes/functions.php` - Funciones de recuperación y envío de correos

---

### 3. 🚨 Notificación por Intentos Fallidos

El sistema ahora envía notificaciones por correo después de 3 intentos fallidos de login.

#### Funcionamiento:

**Después de 3 intentos fallidos:**
- Se envía automáticamente un correo al usuario
- Se registra el evento en los logs de seguridad
- El correo incluye:
  - Número de intentos fallidos
  - Fecha y hora del último intento
  - Dirección IP del intento
  - Enlace para recuperar contraseña
  - Advertencia de bloqueo después de 5 intentos

**Después de 5 intentos fallidos:**
- La cuenta se bloquea automáticamente por 15 minutos
- Se envía notificación adicional informando el bloqueo
- El usuario debe esperar o contactar al administrador

#### Ejemplo de Notificación:

```
Asunto:  Alerta de Seguridad - Intentos de Acceso Fallidos

Se han detectado 3 intentos fallidos de acceso a tu cuenta.

Detalles:
- Cuenta: usuario@ejemplo.com
- Intentos fallidos: 3
- Fecha: 24/10/2025 20:30:15
- IP: 192.168.1.100

¿Fuiste tú?
- Si fuiste tú, recupera tu contraseña aquí
- Si NO fuiste tú, cambia tu contraseña inmediatamente

ADVERTENCIA: Después de 5 intentos, tu cuenta será bloqueada por 15 minutos.
```

---

## 📧 Sistema de Correos

### Modo de Desarrollo (Actual)

En modo desarrollo (`DEVELOPMENT_MODE = true`):
- Los correos NO se envían realmente
- Se guardan como archivos HTML en `logs/emails/`
- Los enlaces de recuperación se muestran en pantalla
- Perfecto para testing sin configurar servidor SMTP

### Ubicación de Correos Guardados:
```
logs/
  └── emails/
      ├── email_2025-10-24_20-30-15_abc123.html
      ├── email_2025-10-24_20-31-22_def456.html
      └── ...
```

### Modo de Producción

Para activar el envío real de correos:

1. Editar `config/database.php`:
   ```php
   define('DEVELOPMENT_MODE', false); // Cambiar a false
   ```

2. Configurar servidor SMTP (opcional):
   - Instalar PHPMailer
   - Configurar credenciales SMTP
   - Actualizar función `sendEmail()` en `includes/functions.php`

---

## 🎨 Tipos de Correos Implementados

### 1. Recuperación de Contraseña
- **Trigger:** Usuario solicita recuperar contraseña
- **Contenido:** Enlace seguro con token temporal
- **Validez:** 1 hora
- **Template:** HTML con diseño bento-style

### 2. Alerta de Intentos Fallidos
- **Trigger:** 3 o más intentos fallidos de login
- **Contenido:** Detalles del intento + enlace de recuperación
- **Incluye:** IP, fecha/hora, número de intentos

### 3. Confirmación de Cambio de Contraseña
- **Trigger:** Contraseña actualizada exitosamente
- **Contenido:** Confirmación del cambio + detalles
- **Seguridad:** Alerta si no fue el usuario quien cambió

---

## Medidas de Seguridad

### Contraseñas
- Hashing con Argon2ID (máxima seguridad)
- Validación de fortaleza (mínimo 8 caracteres, mayúsculas, minúsculas, números, símbolos)
- No se almacenan en texto plano nunca

### Tokens de Recuperación
- Generados con `random_bytes()` (criptográficamente seguros)
- Longitud de 64 caracteres
- Válidos por 1 hora solamente
- Se invalidan después de usarse

### Intentos de Login
- Contador de intentos fallidos por usuario
- Bloqueo temporal después de 5 intentos
- Notificación después de 3 intentos
- Logs de seguridad de todos los eventos

### Logs de Seguridad
Todos los eventos se registran en la tabla `security_logs`:
- Login exitoso
- Login fallido
- Solicitud de recuperación
- Cambio de contraseña
- Bloqueo de cuenta
- Envío de notificaciones

---

## 🧪 Cómo Probar

### Probar Recuperación de Contraseña:

1. Ir a: http://localhost/proyectoAdmEdificios/login.php
2. Hacer clic en "¿Olvidaste tu contraseña?"
3. Ingresar email: `admin@admin.com`
4. Copiar el enlace que se muestra en pantalla
5. Abrir el enlace en el navegador
6. Ingresar nueva contraseña (debe cumplir requisitos)
7. Confirmar cambio

### Probar Notificación de Intentos Fallidos:

1. Ir a: http://localhost/proyectoAdmEdificios/login.php
2. Ingresar email: `admin@admin.com`
3. Ingresar contraseña INCORRECTA 3 veces
4. Revisar la carpeta `logs/emails/`
5. Abrir el archivo HTML más reciente
6. Verificar el contenido del correo de alerta

### Verificar Correos Guardados:

```bash
# Ver lista de correos guardados
dir logs\emails

# Abrir último correo en navegador
start logs\emails\email_[nombre_archivo].html
```

---

## Estadísticas del Sistema

- **3 tipos de correos** implementados
- **3 intentos** antes de notificar
- **5 intentos** antes de bloquear
- **15 minutos** de bloqueo temporal
- **1 hora** de validez de token
- **100% funcional** en modo desarrollo

---

## 🔧 Configuración Adicional

### Para Producción con Correos Reales:

1. **Opción 1: PHP mail() nativo**
   ```php
   // Ya está configurado
   // Solo cambiar DEVELOPMENT_MODE a false
   ```

2. **Opción 2: PHPMailer (recomendado)**
   ```bash
   composer require phpmailer/phpmailer
   ```
   
   Luego actualizar `sendEmail()` para usar PHPMailer con SMTP.

3. **Opción 3: API de Email**
   - SendGrid
   - Mailgun
   - Amazon SES
   - Otros servicios de email

---

## Credenciales Actualizadas

### Administrador
- **Email:** admin@admin.com
- **Contraseña:** ko87K#adm-0

### Empleados
- empleado1@edificio.com / ko87K#emp-1
- empleado2@edificio.com / ko87K#emp-2
- empleado3@edificio.com / ko87K#emp-3

### Inquilinos
- inquilino1@edificio.com / ko87K#fo-inq-1
- inquilino2@edificio.com / ko87K#fo-inq-2
- ... hasta inquilino5

---

## Estado Final

- Email del admin actualizado
- Sistema de recuperación funcional
- Notificaciones por intentos fallidos activas
- Correos se guardan en logs/ (modo desarrollo)
- Todas las funciones de seguridad operativas

¡Sistema completamente funcional y seguro! 🎉
