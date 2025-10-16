# Sistema de Administración de Edificios - SLH

## 📋 Descripción
Sistema completo de gestión residencial que incluye módulos de administración, finanzas, consumos, comunicaciones y reservas.

## 🏗️ Estructura del Proyecto

```
proyectoAdmEdificios/
├── 📁 api/                    # APIs REST
│   ├── invoices.php          # Gestión de facturas
│   ├── payments.php          # Gestión de pagos
│   ├── payroll.php           # Gestión de nóminas
│   └── reports.php           # Reportes financieros
├── 📁 assets/                 # Recursos estáticos
│   ├── css/
│   │   └── style.css         # Estilos principales
│   └── img/                  # Imágenes del proyecto
├── 📁 config/                 # Configuración
│   └── database.php          # Configuración de BD
├── 📁 db/                     # Esquemas de base de datos
│   └── financial_tables.sql  # Tablas financieras
├── 📁 docs/                   # Documentación
│   └── README_FINANZAS.md    # Documentación del módulo financiero
├── 📁 includes/               # Funciones y clases comunes
│   ├── anomaly_detector.php  # Detector de anomalías en consumos
│   ├── api_ingest_lectura.php # API para ingestión de lecturas
│   ├── consumos_actions.php  # Acciones de consumos
│   ├── db.php                # Conexión a base de datos
│   ├── financial.php         # Funciones financieras
│   ├── footer.php            # Pie de página común
│   ├── functions.php         # Funciones de autenticación y utilidades
│   ├── header.php            # Cabecera común
│   └── stream_lecturas.php   # Streaming de lecturas
├── 📁 logs/                   # Archivos de log
├── 📁 sql/                    # Scripts SQL
│   └── consumos_tables.sql   # Tablas de consumos
├── 📁 temp_files/             # Archivos temporales de desarrollo
├── 📁 tools/                  # Herramientas de desarrollo
│   ├── check_lecturas.php    # Verificación de lecturas
│   ├── run_financial_migration.php # Migración financiera
│   ├── run_migrations.php    # Migración de consumos
│   └── seed_lecturas.php     # Datos de prueba
├── 📁 views/                  # Vistas del sistema
│   ├── 📁 admin/             # Vistas de administrador
│   │   ├── areas_comunes.php # Gestión de áreas comunes
│   │   ├── comunicacion.php  # Centro de comunicaciones (admin)
│   │   ├── consumos.php      # Monitoreo de consumos
│   │   ├── consumos_data.php # Datos de consumos
│   │   ├── dashboard.php     # Dashboard principal admin
│   │   ├── empleados.php     # Gestión de empleados
│   │   ├── inquilinos.php    # Gestión de inquilinos
│   │   ├── pagos.php         # Gestión de pagos
│   │   └── reservas.php      # Gestión de reservas
│   ├── 📁 empleado/          # Vistas de empleado
│   │   └── dashboard.php     # Dashboard de empleado
│   ├── 📁 inquilino/         # Vistas de inquilino
│   │   ├── consumos.php      # Vista de consumos inquilino
│   │   ├── dashboard.php     # Dashboard de inquilino
│   │   ├── pagos.php         # Vista de pagos inquilino
│   │   └── reservas.php      # Vista de reservas inquilino
│   └── 📁 shared/            # Vistas compartidas
│       └── comunicaciones.php # Centro de comunicaciones general
├── edificio_admin.sql        # Script principal de BD
├── diagnostico.php           # Diagnóstico del sistema
├── estado_sesion.php         # Verificación de sesión
├── finanzas.php              # Módulo financiero
├── fix_passwords.php         # Utilidad para corregir contraseñas
├── index.php                 # Página principal/landing page
├── login.php                 # Lógica de login
├── login_form.php            # Formulario de login
├── logout.php                # Logout del sistema
├── test_connection.php       # Test de conexión a BD
└── test_sistema.php          # Test general del sistema
```

## 🎯 Módulos del Sistema

### 1. **Módulo de Consumos** (Belu)
- **Funcionalidad**: Monitoreo y gestión de consumos de servicios (agua, electricidad, gas)
- **Características**:
  - Lecturas en tiempo real
  - Detección de anomalías
  - Alertas automáticas
  - APIs para dispositivos IoT
  - Dashboard de visualización

### 2. **Módulo Financiero** (Main)
- **Funcionalidad**: Gestión completa de finanzas y facturación
- **Características**:
  - Facturación automática
  - Control de pagos y morosidad
  - Reportes financieros
  - APIs REST para integración
  - Gestión de nóminas

### 3. **Módulo de Administración**
- **Funcionalidad**: Gestión general del edificio
- **Características**:
  - Dashboard administrativo
  - Gestión de usuarios (admin, empleados, inquilinos)
  - Sistema de comunicaciones
  - Gestión de áreas comunes
  - Control de reservas

## 🚀 Instalación y Configuración

### Prerrequisitos
- XAMPP con PHP 8.1+
- MySQL 8.0+
- Navegador web moderno

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/srllamadev/proyectoAdmEdificios.git
   cd proyectoAdmEdificios
   ```

2. **Configurar la base de datos**
   - Importar `edificio_admin.sql` en phpMyAdmin
   - Ejecutar migraciones:
     ```bash
     php tools/run_migrations.php
     php tools/run_financial_migration.php
     ```

3. **Cargar datos de prueba**
   ```bash
   php tools/seed_lecturas.php
   ```

4. **Verificar instalación**
   - Acceder a `http://localhost/proyectoAdmEdificios/test_sistema.php`

## 👥 Usuarios de Prueba

### Administrador
- **Email**: admin@edificio.com
- **Contraseña**: password

### Empleados
- empleado1@edificio.com / password
- empleado2@edificio.com / password
- empleado3@edificio.com / password

### Inquilinos
- inquilino1@edificio.com / password
- inquilino2@edificio.com / password
- inquilino3@edificio.com / password
- inquilino4@edificio.com / password
- inquilino5@edificio.com / password

## 🔧 APIs Disponibles

### Endpoints Financieros
- `GET/POST /api/invoices.php` - Gestión de facturas
- `GET/POST /api/payments.php` - Gestión de pagos
- `GET/POST /api/payroll.php` - Gestión de nóminas
- `GET /api/reports.php` - Reportes financieros

### Endpoints de Consumos
- `POST /includes/api_ingest_lectura.php` - Ingestión de lecturas IoT

## 📊 Base de Datos

### Tablas Principales
- `users` - Usuarios del sistema
- `departamentos` - Unidades residenciales
- `empleados` - Empleados del edificio
- `inquilinos` - Residentes
- `comunicacion` - Sistema de mensajes
- `reservas` - Reservas de áreas comunes
- `pagos` - Registro de pagos
- `lecturas` - Lecturas de consumos
- `invoices` - Facturas
- `invoice_items` - Items de facturas

## 🛠️ Desarrollo

### Estructura de Ramas
- `main` - Rama principal con código estable
- `yamil-rama1` - Rama de desarrollo de Yamil
- `belu-rama1` - Rama de desarrollo de Belu
- `hernan_rama1` - Rama de desarrollo de Hernan
- `vane-rama1` - Rama de desarrollo de Vane

### Comandos Útiles
```bash
# Verificar estado del sistema
php diagnostico.php

# Ejecutar tests
php test_sistema.php

# Verificar lecturas
php tools/check_lecturas.php

# Ejecutar detector de anomalías
php includes/anomaly_detector.php
```

## 📝 Notas de Desarrollo

- El sistema utiliza sesiones PHP para autenticación
- Todas las contraseñas están hasheadas con `password_hash()`
- Las APIs requieren autenticación previa
- El detector de anomalías debe ejecutarse por cron cada 5-15 minutos
- Los estilos utilizan CSS moderno con variables CSS

## 🤝 Contribuidores

- **Yamil**: Landing page y UI/UX general
- **Belu**: Módulo de consumos y IoT
- **Hernan**: [Pendiente de documentación]
- **Vane**: [Pendiente de documentación]

---

**SLH - El Futuro de la Vida Urbana** 🏙️</content>
<parameter name="filePath">c:\xampp\htdocs\proyectoAdmEdificios\README.md