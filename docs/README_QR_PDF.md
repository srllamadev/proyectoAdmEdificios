# Sistema de Facturación y Nómina con Códigos QR

## Nuevas Funcionalidades Implementadas

### 1. **Librería de Códigos QR (phpqrcode)**
- **Ubicación**: `lib/phpqrcode/qrlib.php`
- **Descripción**: Librería personalizada para generar códigos QR en formato PNG y SVG
- **Uso**: Se utiliza automáticamente en todas las facturas y comprobantes de nómina

### 2. **Generador de PDFs con QR**
- **Ubicación**: `includes/pdf_generator.php`
- **Clase**: `InvoicePDF`
- **Funciones principales**:
  - `generateInvoicePDF($invoiceData)`: Genera PDF de factura para inquilino con código QR
  - `generatePayrollPDF($payrollData)`: Genera comprobante de pago de nómina con código QR

### 3. **Helper de Códigos QR**
- **Ubicación**: `includes/qr_helper.php`
- **Funciones disponibles**:
  - `generateQRImage($data, $outputPath)`: Genera imagen QR y retorna ruta del archivo
  - `generateQRBase64($data)`: Genera QR en formato base64 para uso en HTML
  - `generateInvoiceQR($ref, $amount, $dueDate)`: QR específico para facturas
  - `generatePayrollQR($period, $name, $net)`: QR específico para nómina
  - `generateContactQR($name, $phone, $email)`: QR en formato vCard
  - `generateURLQR($url)`: QR para URLs

### 4. **Endpoints de PDF**

#### a) Facturas de Inquilinos
- **URL**: `api/invoice_pdf.php?id={invoice_id}`
- **Acceso**: Admin y el inquilino propietario de la factura
- **Características**:
  - Muestra detalle completo de la factura
  - Incluye código QR con datos de pago
  - Botón para imprimir/guardar como PDF desde navegador
  - Desglose de servicios (luz, agua, gas, etc.)

#### b) Comprobantes de Nómina
- **URL**: `api/payroll_pdf.php?id={payroll_id}`
- **Acceso**: Admin y el empleado propietario del comprobante
- **Características**:
  - Detalle de salario bruto, deducciones y neto
  - Código QR de verificación
  - Información del período y empleado
  - Botón para imprimir/guardar como PDF

#### c) Generador de QR Dinámico
- **URL**: `api/qr_code.php?data={texto}`
- **Parámetros adicionales**:
  - `type=invoice&ref=...&amount=...&due=...`: QR de factura
  - `type=payroll&period=...&name=...&net=...`: QR de nómina
  - `type=url&data=...`: QR de URL

### 5. **Vista de Pagos para Inquilinos**
- **Ubicación**: `views/inquilino/pagos.php`
- **Mejoras implementadas**:
  - ✅ Lista de facturas generadas por el administrador
  - ✅ Formulario de pago por factura con métodos de pago
  - ✅ Botón "Ver/Descargar Factura PDF con QR" en cada factura
  - ✅ Indicadores visuales de estado (pagado, pendiente, vencido)
  - ✅ Campo para número de comprobante/transacción
  - ✅ Los pagos registrados por inquilinos se reflejan automáticamente en admin

### 6. **Vista de Empleados en Administración**
- **Ubicación**: `finanzas.php` - Sección "Empleados / Planilla"
- **Mejoras implementadas**:
  - ✅ Carga de empleados desde tabla `empleados` del proyecto
  - ✅ Cálculo de bono anual por antigüedad (5% × años de servicio)
  - ✅ Historial completo de pagos de nómina
  - ✅ Botón "Ver Comprobante" con PDF y código QR para cada pago
  - ✅ Botón "Marcar Pagado" para pagos pendientes
  - ✅ Estadísticas: total pagado, pendiente, cantidad de pagos

## Estructura de Datos en Códigos QR

### Facturas de Inquilinos
```
FACTURA:{referencia}|MONTO:${monto}|VENCE:{fecha_vencimiento}
```
Ejemplo: `FACTURA:INV-2024-001|MONTO:$150.50|VENCE:2024-11-30`

### Comprobantes de Nómina
```
NOMINA:{periodo}|EMPLEADO:{nombre}|NETO:${monto_neto}
```
Ejemplo: `NOMINA:2024-10|EMPLEADO:Juan Pérez|NETO:$2500.00`

## Flujo de Trabajo

### Para Inquilinos:
1. Admin genera factura con consumos o montos fijos
2. Inquilino ve factura en `views/inquilino/pagos.php`
3. Inquilino puede:
   - Ver detalle completo
   - Descargar PDF con QR code
   - Registrar pago con método y comprobante
4. El pago queda registrado y es visible para admin

### Para Empleados:
1. Admin crea registro en `payroll` para el empleado
2. El registro aparece en historial del empleado
3. Admin puede:
   - Ver comprobante PDF con QR
   - Marcar como pagado
4. Empleado (si tiene acceso) puede descargar su comprobante

## Archivos Creados/Modificados

### Nuevos Archivos:
- ✅ `lib/phpqrcode/qrlib.php` - Librería QR
- ✅ `includes/pdf_generator.php` - Generador de PDFs
- ✅ `includes/qr_helper.php` - Helper de códigos QR
- ✅ `api/qr_code.php` - Endpoint QR dinámico
- ✅ `temp/qr/` - Directorio temporal para QR codes

### Archivos Modificados:
- ✅ `api/invoice_pdf.php` - Actualizado con nueva funcionalidad
- ✅ `api/payroll_pdf.php` - Actualizado con nueva funcionalidad
- ✅ `views/inquilino/pagos.php` - Agregado formulario de pago y botón PDF
- ✅ `finanzas.php` - Agregados botones PDF en historial y empleados
- ✅ `.gitignore` - Excluir directorio temp/

## Próximas Mejoras Sugeridas

1. **Integración con pasarelas de pago**:
   - MercadoPago, PayPal, Stripe
   - El QR podría incluir link de pago directo

2. **Notificaciones automáticas**:
   - Email al inquilino cuando se genera factura
   - Email al empleado cuando se genera comprobante
   - Incluir PDF y QR en el email

3. **Aplicación móvil**:
   - Escanear QR para ver detalle de factura
   - Pagar desde la app escaneando el QR

4. **Panel de reportes**:
   - Dashboard con estadísticas de pagos
   - Gráficos de morosidad
   - Exportar reportes con QR codes

5. **Firma digital**:
   - QR firmado criptográficamente
   - Verificación de autenticidad del comprobante

## Soporte y Mantenimiento

- Los códigos QR son estándar y pueden ser escaneados con cualquier app
- Los PDFs se generan como HTML imprimible (compatible con todos los navegadores)
- Los archivos temporales en `temp/qr/` se autoeliminan después de generar el base64
- El sistema es compatible con PHP 7.4+

---

**Fecha de implementación**: Octubre 2024  
**Versión del sistema**: 2.0  
**Desarrollado para**: Sistema de Administración de Edificios
