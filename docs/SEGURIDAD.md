# 🔐 SISTEMA DE SEGURIDAD - EXPLICACIÓN COMPLETA

## 📋 Resumen Ejecutivo

El sistema utiliza **múltiples capas de seguridad** para proteger datos sensibles. Actualmente está en **modo de desarrollo simplificado**, pero incluye toda la infraestructura para **seguridad de producción**.

---

## 🔑 **1. CONTRASEÑAS HASHEADAS (Bcrypt)**

### **¿Qué vemos en el SQL?**
```sql
password = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
```

### **¿Qué significa esto?**
- **`$2y$`** = Identificador de bcrypt
- **`12`** = Cost factor (complejidad del hash)
- **`92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`** = Salt + hash

### **¿Cómo se genera?**
```php
$hash = password_hash('password', PASSWORD_BCRYPT, ['cost' => 12]);
```

### **¿Cómo se verifica?**
```php
$valid = password_verify('password', $hash_stored_in_db);
```

---

## 🏷️ **2. TOKENS DE ACCESO**

### **Tabla: `personal_access_tokens`**
```sql
token = 'abc123def456...'  -- 64 caracteres hexadecimales
abilities = '["read", "write"]'  -- JSON con permisos
expires_at = '2025-12-31 23:59:59'  -- Fecha de expiración
```

### **¿Cómo se generan?**
```php
$token = bin2hex(random_bytes(32)); // 64 caracteres hex
```

---

## 💳 **3. DATOS FINANCIEROS ENCRIPTADOS**

### **Campos sensibles en `invoices`:**
```sql
meta = '{"payment_info": "encrypted_data_here"}'  -- JSON encriptado
```

### **Campos sensibles en `payments`:**
```sql
metadata = '{"card_last4": "****", "gateway_response": "..."}'  -- JSON encriptado
```

### **¿Cómo se encripta?**
```php
// Encriptar
$encrypted = encryptData($sensitive_data, ENCRYPTION_KEY);

// Desencriptar
$decrypted = decryptData($encrypted_data, ENCRYPTION_KEY);
```

---

## 🔐 **4. FUNCIONES DE SEGURIDAD DISPONIBLES**

### **Archivo: `includes/security_functions.php`**
```php
// Hash de contraseñas
hashPassword($password) → string

// Verificación de contraseñas
verifyPassword($password, $hash) → bool

// Generación de tokens
generateToken($length) → string

// Sanitización para logs
sanitizeForLog($data) → array

// Encriptación AES-256
encryptData($data, $key) → string
decryptData($encrypted, $key) → string

// Hash de archivos (integrity)
generateFileHash($filePath) → string

// Validación de emails segura
validateEmail($email) → bool

// Códigos de recuperación
generateRecoveryCode() → string
```

---

## 🚨 **5. MEDIDAS DE SEGURIDAD IMPLEMENTADAS**

### **A. Protección contra SQL Injection:**
- ✅ Uso de Prepared Statements en TODAS las consultas
- ✅ Sanitización de inputs con `clean_input()`
- ✅ Validación de tipos de datos

### **B. Protección XSS:**
- ✅ `htmlspecialchars()` en todas las salidas
- ✅ Content Security Policy headers
- ✅ Sanitización de HTML

### **C. Control de Sesiones:**
- ✅ `session_start()` seguro
- ✅ Regeneración de session IDs
- ✅ Timeout automático de sesiones

### **D. Validación de Datos:**
- ✅ Regex para emails
- ✅ Validación de formatos
- ✅ Límites de longitud

---

## 🔧 **6. MODO DESARROLLO vs PRODUCCIÓN**

### **Modo Desarrollo (Actual):**
```php
// Login simplificado para testing
if ($password === 'password') {
    // Login exitoso
}
```

### **Modo Producción (Recomendado):**
```php
// Login seguro real
if (password_verify($password, $user['password'])) {
    // Login exitoso
}
```

---

## 📊 **7. CLAVES DE ENCRIPTACIÓN**

### **Variables de Entorno Requeridas:**
```bash
# .env file
ENCRYPTION_KEY=your_256_bit_encryption_key_here
JWT_SECRET=your_jwt_secret_key_here
RECAPTCHA_SECRET=your_recaptcha_secret_here
```

### **Generación de Claves Seguras:**
```php
// Clave de encriptación (32 bytes = 256 bits)
$encryptionKey = bin2hex(random_bytes(32));

// JWT Secret (64 caracteres)
$jwtSecret = bin2hex(random_bytes(32));

// API Keys (32 caracteres)
$apiKey = bin2hex(random_bytes(16));
```

---

## 🛡️ **8. AUDITORÍA Y LOGGING**

### **Sistema de Logs:**
- ✅ Logs de autenticación
- ✅ Logs de acceso a datos sensibles
- ✅ Logs de cambios en configuración
- ✅ Logs de errores de seguridad

### **Auditoría de Archivos:**
```php
// Verificar integridad de archivos críticos
$hash = generateFileHash('config/database.php');
if ($hash !== EXPECTED_HASH) {
    // Archivo comprometido - ALERTA!
}
```

---

## 🚀 **9. IMPLEMENTACIÓN EN PRODUCCIÓN**

### **Pasos para activar seguridad completa:**

1. **Cambiar login.php:**
   ```php
   // Reemplazar verificación simplificada por:
   if (password_verify($password, $user['password'])) {
       // Login exitoso
   }
   ```

2. **Configurar variables de entorno:**
   ```bash
   cp .env.example .env
   # Editar con claves reales
   ```

3. **Generar nuevos hashes:**
   ```php
   // Para usuarios existentes
   $newHash = password_hash('nueva_contraseña', PASSWORD_BCRYPT);
   ```

4. **Activar encriptación:**
   ```php
   define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY'));
   ```

---

## ⚠️ **10. NOTAS DE SEGURIDAD**

### **Advertencias:**
- 🔴 **NO usar el modo desarrollo en producción**
- 🔴 **Cambiar todas las contraseñas por defecto**
- 🔴 **Configurar HTTPS obligatorio**
- 🔴 **Implementar rate limiting**
- 🔴 **Configurar backups automáticos**

### **Mejores Prácticas:**
- ✅ Usar HTTPS en todo momento
- ✅ Implementar 2FA cuando sea posible
- ✅ Monitorear logs de seguridad
- ✅ Actualizar dependencias regularmente
- ✅ Realizar auditorías de seguridad periódicas

---

## 📞 **SOPORTE Y CONSULTAS**

Para implementar la seguridad completa o resolver dudas específicas, contactar al equipo de desarrollo.

**Estado Actual:** 🔧 Sistema preparado para seguridad de producción
**Modo Actual:** 🧪 Desarrollo (simplificado para testing)</content>
<parameter name="filePath">c:\xampp\htdocs\proyectoAdmEdificios\docs\SEGURIDAD.md