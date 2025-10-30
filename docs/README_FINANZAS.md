Gestión Financiera - Módulo
==========================

Archivos añadidos:
- includes/db.php        -> wrapper PDO usando config/database.php
- includes/financial.php -> lógica financiera (facturas, pagos, nómina, reportes)
- api/invoices.php       -> endpoint CRUD facturas
- api/payments.php       -> endpoint para registrar pagos
- api/payroll.php        -> endpoint para nómina
- api/reports.php        -> endpoint para reportes
- finanzas.php           -> UI protegida para gestión financiera
- db/financial_tables.sql-> esquema SQL para importar
 - api/invoice_pdf.php   -> descarga/visualización de factura (PDF/HTML)
 - api/gateway_tigo.php  -> pasarela mock tipo Tigo Money (desarrollo)
 - pay_invoice.php       -> página pública de pago/QR por referencia
 - api/payroll_pdf.php   -> descarga de planilla (PDF/HTML) por periodo
 - views/admin/planilla.php -> interfaz admin para descargar planillas mensuales

Pasos recomendados:
1. Importar `db/financial_tables.sql` en tu base de datos.
2. Asegurarte de que `config/database.php` tiene las credenciales correctas.
3. Probar endpoints en /api/ (requieren sesión activa con rol admin o empleado).
4. Instalar dompdf si quieres generación de PDFs: `composer require dompdf/dompdf`.

Notas rápidas:
- Tipos de factura soportados: alquiler, electricidad, agua, gas, mantenimiento. El tipo se guarda en `invoices.meta -> {"type":"electricidad"}`.
- Para ver deudas por inquilino usar la vista `views/inquilino/pagos.php` (muestra historial, resumen mensual y deuda total).
- Para generar planilla en PDF: ir a `views/admin/planilla.php` y descargar el periodo deseado. En desarrollo la planilla se renderiza en HTML si Dompdf no está instalado.

Integración Tigo Money:
- `api/gateway_tigo.php` es un mock para pruebas locales. Para una integración real necesitarás las credenciales de Tigo y endpoints de callback; contacta al proveedor y adapta `includes/financial.php::integratePaymentGateway`.

QR local (recomendado):
- Para generar QR locales sin depender de servicios externos, instala la librería PHP `chillerlan/php-qrcode` vía Composer:

```powershell
composer require chillerlan/php-qrcode
```

La implementación detecta automáticamente la librería y generará PNGs locales incrustados en los PDFs. Si no instalas la librería, el sistema hace un fallback a Google Charts (temporal) para generar QR.

Seguridad:
- Protege la carpeta `api/` y `finanzas.php` con HTTPS y roles.
- Valida y sanea siempre entradas externas.
