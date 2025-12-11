<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Crear Empleado - Administrador';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = clean_input($_POST['name']);
        $email = clean_input($_POST['email']);
        $password = $_POST['password'];
        $dni = clean_input($_POST['dni']);
        $cargo = clean_input($_POST['cargo']);
        $telefono = clean_input($_POST['telefono']);
        $salario = clean_input($_POST['salario']);
        $fecha_contratacion = clean_input($_POST['fecha_contratacion']);
        
        // Validaciones
        if (empty($name) || empty($email) || empty($password) || empty($cargo)) {
            throw new Exception("Por favor complete todos los campos obligatorios.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email inválido.");
        }
        
        if (strlen($password) < 6) {
            throw new Exception("La contraseña debe tener al menos 6 caracteres.");
        }
        
        // Verificar que el email no exista
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception("Ya existe un usuario con ese email.");
        }
        
        // Iniciar transacción
        $db->beginTransaction();
        
        // Crear usuario con rol empleado
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, created_at, updated_at) 
                             VALUES (?, ?, ?, 'empleado', NOW(), NOW())");
        $stmt->execute([$name, $email, $hashed_password]);
        $user_id = $db->lastInsertId();
        
        // Crear registro en tabla empleados
        $stmt = $db->prepare("INSERT INTO empleados (user_id, dni, cargo, telefono, salario, fecha_contratacion, estado, created_at, updated_at) 
                             VALUES (?, ?, ?, ?, ?, ?, 'activo', NOW(), NOW())");
        $stmt->execute([
            $user_id, 
            $dni, 
            $cargo, 
            $telefono, 
            $salario ?: null, 
            $fecha_contratacion ?: date('Y-m-d')
        ]);
        
        $db->commit();
        $success = "Empleado creado exitosamente.";
        
        // Limpiar formulario
        $_POST = [];
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $error = $e->getMessage();
    }
}
?>

<div class="bento-page-header">
    <h1 class="bento-page-title"><i class="fas fa-user-plus"></i> Crear Nuevo Empleado</h1>
    <p class="bento-page-subtitle">Registrar un nuevo empleado en el sistema</p>
    <a href="dashboard.php" class="bento-btn bento-btn-ghost">
        <i class="fas fa-arrow-left"></i> Volver al Dashboard
    </a>
</div>

<?php if ($success): ?>
    <div class="bento-alert bento-alert-success">
        <i class="fas fa-check-circle"></i>
        <strong>¡Éxito!</strong> <?php echo $success; ?>
        <a href="empleados.php" class="bento-link-primary">Ver lista de empleados</a>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bento-alert bento-alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="bento-card">
    <h3 class="bento-card-title"><i class="fas fa-user-tie"></i> Información del Empleado</h3>
    
    <form method="POST" action="" class="bento-form">
        <h4 class="bento-section-title"><i class="fas fa-user-circle"></i> Datos de Acceso</h4>
        
        <div class="bento-form-grid">
            <div class="bento-form-group">
                <label for="name" class="bento-form-label">
                    <i class="fas fa-user"></i> Nombre Completo *
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="bento-form-input" 
                    placeholder="Ej: Juan Pérez"
                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                    required
                >
            </div>
            
            <div class="bento-form-group">
                <label for="email" class="bento-form-label">
                    <i class="fas fa-envelope"></i> Email *
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="bento-form-input" 
                    placeholder="empleado@ejemplo.com"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    required
                >
            </div>
            
            <div class="bento-form-group">
                <label for="password" class="bento-form-label">
                    <i class="fas fa-lock"></i> Contraseña *
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="bento-form-input" 
                    placeholder="Mínimo 6 caracteres"
                    required
                >
                <small class="bento-form-hint">La contraseña debe tener al menos 6 caracteres</small>
            </div>
        </div>
        
        <h4 class="bento-section-title"><i class="fas fa-briefcase"></i> Información Laboral</h4>
        
        <div class="bento-form-grid">
            <div class="bento-form-group">
                <label for="dni" class="bento-form-label">
                    <i class="fas fa-id-card"></i> DNI
                </label>
                <input 
                    type="text" 
                    id="dni" 
                    name="dni" 
                    class="bento-form-input" 
                    placeholder="12345678"
                    value="<?php echo isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : ''; ?>"
                >
            </div>
            
            <div class="bento-form-group">
                <label for="cargo" class="bento-form-label">
                    <i class="fas fa-user-tag"></i> Cargo *
                </label>
                <select id="cargo" name="cargo" class="bento-form-select" required>
                    <option value="">Seleccione un cargo</option>
                    <option value="mantenimiento" <?php echo (isset($_POST['cargo']) && $_POST['cargo'] == 'mantenimiento') ? 'selected' : ''; ?>>Mantenimiento</option>
                    <option value="seguridad" <?php echo (isset($_POST['cargo']) && $_POST['cargo'] == 'seguridad') ? 'selected' : ''; ?>>Seguridad</option>
                    <option value="limpieza" <?php echo (isset($_POST['cargo']) && $_POST['cargo'] == 'limpieza') ? 'selected' : ''; ?>>Limpieza</option>
                    <option value="administracion" <?php echo (isset($_POST['cargo']) && $_POST['cargo'] == 'administracion') ? 'selected' : ''; ?>>Administración</option>
                    <option value="jardineria" <?php echo (isset($_POST['cargo']) && $_POST['cargo'] == 'jardineria') ? 'selected' : ''; ?>>Jardinería</option>
                    <option value="recepcion" <?php echo (isset($_POST['cargo']) && $_POST['cargo'] == 'recepcion') ? 'selected' : ''; ?>>Recepción</option>
                    <option value="otro" <?php echo (isset($_POST['cargo']) && $_POST['cargo'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
                </select>
            </div>
            
            <div class="bento-form-group">
                <label for="telefono" class="bento-form-label">
                    <i class="fas fa-phone"></i> Teléfono
                </label>
                <input 
                    type="text" 
                    id="telefono" 
                    name="telefono" 
                    class="bento-form-input" 
                    placeholder="+1234567890"
                    value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                >
            </div>
            
            <div class="bento-form-group">
                <label for="salario" class="bento-form-label">
                    <i class="fas fa-dollar-sign"></i> Salario Mensual
                </label>
                <input 
                    type="number" 
                    id="salario" 
                    name="salario" 
                    class="bento-form-input" 
                    placeholder="0.00"
                    step="0.01"
                    min="0"
                    value="<?php echo isset($_POST['salario']) ? htmlspecialchars($_POST['salario']) : ''; ?>"
                >
            </div>
            
            <div class="bento-form-group">
                <label for="fecha_contratacion" class="bento-form-label">
                    <i class="fas fa-calendar"></i> Fecha de Contratación
                </label>
                <input 
                    type="date" 
                    id="fecha_contratacion" 
                    name="fecha_contratacion" 
                    class="bento-form-input"
                    value="<?php echo isset($_POST['fecha_contratacion']) ? htmlspecialchars($_POST['fecha_contratacion']) : date('Y-m-d'); ?>"
                >
            </div>
        </div>
        
        <div class="bento-form-actions">
            <button type="submit" class="bento-btn bento-btn-success">
                <i class="fas fa-save"></i> Crear Empleado
            </button>
            <a href="dashboard.php" class="bento-btn bento-btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
