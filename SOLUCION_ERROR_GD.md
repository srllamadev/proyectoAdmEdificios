# 🔧 SOLUCIÓN RÁPIDA - Error de Extensión GD

## ❌ Problema Encontrado:
```
Fatal error: Call to undefined function imagecreate()
```

**Causa**: La extensión GD de PHP no está habilitada en XAMPP.

---

## ✅ SOLUCIONES (Elige una):

### **Opción 1: Automática (RECOMENDADO)**

1. **Abrir PowerShell como Administrador**
   - Click derecho en el menú inicio → "Windows PowerShell (Administrador)"

2. **Ejecutar el script automático**:
   ```powershell
   cd C:\xampp\htdocs\proyectoAdmEdificios
   .\habilitar_gd.ps1
   ```

3. **Reiniciar Apache** desde el panel de XAMPP

4. **Verificar**: Abrir http://localhost/proyectoAdmEdificios/check_gd.php

---

### **Opción 2: Manual**

1. **Abrir archivo de configuración**:
   ```
   C:\xampp\php\php.ini
   ```

2. **Buscar la línea** (Ctrl+F):
   ```ini
   ;extension=gd
   ```

3. **Quitar el punto y coma** (;) al inicio:
   ```ini
   extension=gd
   ```

4. **Guardar** el archivo (Ctrl+S)

5. **Reiniciar Apache** desde el panel de XAMPP

6. **Verificar**: Abrir http://localhost/proyectoAdmEdificios/check_gd.php

---

### **Opción 3: Usar SVG (Ya implementado como fallback)**

Si no puedes habilitar GD, el sistema **ya está configurado** para usar SVG automáticamente.

Los códigos QR se generarán en formato SVG en lugar de PNG. Funciona igual de bien.

**No necesitas hacer nada**, simplemente:
1. Intenta crear una factura nuevamente
2. El sistema detectará que GD no está disponible
3. Generará QR codes en formato SVG automáticamente

---

## 🎯 Otras Correcciones Aplicadas:

### **1. Campos Faltantes en Facturas** ✅
- Mapeado correcto de campos de base de datos
- Manejo de valores por defecto para evitar "Undefined array key"
- Soporte para diferentes nombres de columnas

### **2. Items de Factura** ✅
- Manejo flexible de campos `quantity` y `qty`
- Cálculo automático de `subtotal` si no está presente
- Valores por defecto para evitar errores

---

## 📋 Verificación Post-Solución:

### **Verificar GD está habilitada**:
```
http://localhost/proyectoAdmEdificios/check_gd.php
```

### **Probar generación de PDF**:
1. Ir a `finanzas.php`
2. Crear una factura para cualquier inquilino
3. Click en botón "PDF" en el historial
4. ✅ Debe mostrar la factura con código QR (PNG o SVG)

---

## 🆘 Si Sigue Fallando:

### **Verificar versión de PHP**:
```
http://localhost/dashboard/phpinfo.php
```
Buscar "GD Support" → debe decir "enabled"

### **Revisar logs de error**:
```
C:\xampp\apache\logs\error.log
```

### **Contacto**:
Si el problema persiste, revisar el archivo de log y compartir el mensaje de error específico.

---

## ✨ Ventajas de la Solución Implementada:

- ✅ **Fallback automático**: Si GD no está disponible, usa SVG
- ✅ **Sin pérdida de funcionalidad**: Los QR funcionan igual
- ✅ **Manejo robusto de errores**: Valores por defecto para todos los campos
- ✅ **Compatible con diferentes estructuras de BD**: Mapeo flexible de campos

---

**El sistema ahora debería funcionar perfectamente** tanto con GD habilitada como sin ella! 🎉
