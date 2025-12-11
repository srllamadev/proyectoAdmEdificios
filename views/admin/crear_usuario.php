<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Crear Usuario - Administrador';
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
        $role = clean_input($_POST['role']);
        $dni = clean_input($_POST['dni']);
        $telefono = clean_input($_POST['telefono']);
        $direccion = clean_input($_POST['direccion']);
        
        // Validaciones
        if (empty($name) || empty($email) || empty($password) || empty($role)) {
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
        
        // Crear usuario
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, created_at, updated_at) 
                             VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$name, $email, $hashed_password, $role]);
        $user_id = $db->lastInsertId();
        
        // Si es inquilino, crear registro en tabla inquilinos
        if ($role === 'inquilino') {
            $stmt = $db->prepare("INSERT INTO inquilinos (user_id, dni, telefono, direccion, fecha_ingreso, estado, created_at, updated_at) 
                                 VALUES (?, ?, ?, ?, NOW(), 'activo', NOW(), NOW())");
            $stmt->execute([$user_id, $dni, $telefono, $direccion]);
        }
        
        $db->commit();
        $success = "Usuario creado exitosamente.";
        
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
    <h1 class="bento-page-title"><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h1>
    <p class="bento-page-subtitle">Registrar un nuevo usuario en el sistema</p>
    <a href="dashboard.php" class="bento-btn bento-btn-ghost">
        <i class="fas fa-arrow-left"></i> Volver al Dashboard
    </a>
</div>

<?php if ($success): ?>
    <div class="bento-alert bento-alert-success">
        <i class="fas fa-check-circle"></i>
        <strong>¡Éxito!</strong> <?php echo $success; ?>
        <a href="inquilinos.php" class="bento-link-primary">Ver lista de inquilinos</a>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bento-alert bento-alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="bento-card">
    <h3 class="bento-card-title"><i class="fas fa-user-circle"></i> Información del Usuario</h3>
    
    <form method="POST" action="" class="bento-form">
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
                    placeholder="usuario@ejemplo.com"
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
            
            <div class="bento-form-group">
                <label for="role" class="bento-form-label">
                    <i class="fas fa-user-tag"></i> Rol *
                </label>
                <select id="role" name="role" class="bento-form-select" required>
                    <option value="">Seleccione un rol</option>
                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                    <option value="inquilino" <?php echo (isset($_POST['role']) && $_POST['role'] == 'inquilino') ? 'selected' : ''; ?>>Inquilino</option>
                    <option value="empleado" <?php echo (isset($_POST['role']) && $_POST['role'] == 'empleado') ? 'selected' : ''; ?>>Empleado</option>
                </select>
            </div>
        </div>
        
        <div id="inquilino-fields" style="display: none;">
            <h4 class="bento-section-title"><i class="fas fa-id-card"></i> Información Adicional del Inquilino</h4>
            
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
                
                <div class="bento-form-group" style="grid-column: 1 / -1;">
                    <label for="direccion" class="bento-form-label">
                        <i class="fas fa-map-marker-alt"></i> Dirección
                    </label>
                    <textarea 
                        id="direccion" 
                        name="direccion" 
                        class="bento-form-textarea" 
                        rows="3"
                        placeholder="Dirección completa"
                    ><?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="bento-form-actions">
            <button type="submit" class="bento-btn bento-btn-success">
                <i class="fas fa-save"></i> Crear Usuario
            </button>
            <a href="dashboard.php" class="bento-btn bento-btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<script>
// Mostrar/ocultar campos adicionales según el rol seleccionado
document.getElementById('role').addEventListener('change', function() {
    const inquilinoFields = document.getElementById('inquilino-fields');
    if (this.value === 'inquilino') {
        inquilinoFields.style.display = 'block';
    } else {
        inquilinoFields.style.display = 'none';
    }
});

// Ejecutar al cargar la página
window.addEventListener('DOMContentLoaded', function() {
    const role = document.getElementById('role').value;
    if (role === 'inquilino') {
        document.getElementById('inquilino-fields').style.display = 'block';
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>
