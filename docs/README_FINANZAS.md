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

Pasos recomendados:
1. Importar `db/financial_tables.sql` en tu base de datos.
2. Asegurarte de que `config/database.php` tiene las credenciales correctas.
3. Probar endpoints en /api/ (requieren sesión activa con rol admin o empleado).
4. Instalar dompdf si quieres generación de PDFs: `composer require dompdf/dompdf`.

Seguridad:
- Protege la carpeta `api/` y `finanzas.php` con HTTPS y roles.
- Valida y sanea siempre entradas externas.
