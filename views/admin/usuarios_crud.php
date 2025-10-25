<?php
require_once '../../includes/functions.php';

// Verificar que está logueado y es admin
if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../../login.php');
    exit();
}

$page_title = 'Gestión de Usuarios - Administrador';
require_once '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// PROCESAR ACCIONES CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // CREAR USUARIO
    if ($action === 'create') {
        $name = clean_input($_POST['name']);
        $email = clean_input($_POST['email']);
        $password = $_POST['password'];
        $role = clean_input($_POST['role']);
        $dni = clean_input($_POST['dni'] ?? '');
        $telefono = clean_input($_POST['telefono'] ?? '');
        
        // Validaciones
        if (empty($name) || empty($email) || empty($password) || empty($role)) {
            $error = 'Todos los campos obligatorios deben estar completos.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email inválido.';
        } elseif (strlen($password) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } else {
            // Verificar si el email ya existe
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'El email ya está registrado en el sistema.';
            } else {
                // Hash de la contraseña
                $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
                
                try {
                    // Insertar usuario
                    $sql = "INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$name, $email, $hashed_password, $role]);
                    $user_id = $db->lastInsertId();
                    
                    // Si es inquilino, crear registro en tabla inquilinos
                    if ($role === 'inquilino') {
                        $sql_inquilino = "INSERT INTO inquilinos (user_id, dni, telefono, estado, fecha_ingreso) VALUES (?, ?, ?, 'activo', NOW())";
                        $stmt_inquilino = $db->prepare($sql_inquilino);
                        $stmt_inquilino->execute([$user_id, $dni, $telefono]);
                    }
                    
                    // Si es empleado, crear registro en tabla empleados
                    if ($role === 'empleado') {
                        $sql_empleado = "INSERT INTO empleados (user_id, dni, telefono, fecha_ingreso) VALUES (?, ?, ?, NOW())";
                        $stmt_empleado = $db->prepare($sql_empleado);
                        $stmt_empleado->execute([$user_id, $dni, $telefono]);
                    }
                    
                    $message = "✅ Usuario creado exitosamente: <strong>$name</strong> ($email)";
                    logSecurityEvent($user_id, 'user_created', "Usuario creado por admin: $name ($role)");
                } catch (PDOException $e) {
                    $error = "Error al crear usuario: " . $e->getMessage();
                }
            }
        }
    }
    
    // ACTUALIZAR USUARIO
    elseif ($action === 'update') {
        $user_id = intval($_POST['user_id']);
        $name = clean_input($_POST['name']);
        $email = clean_input($_POST['email']);
        $role = clean_input($_POST['role']);
        $dni = clean_input($_POST['dni'] ?? '');
        $telefono = clean_input($_POST['telefono'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        
        if (empty($name) || empty($email) || empty($role)) {
            $error = 'Nombre, email y rol son obligatorios.';
        } else {
            try {
                // Actualizar usuario
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_ARGON2ID);
                    $sql = "UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$name, $email, $hashed_password, $role, $user_id]);
                } else {
                    $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$name, $email, $role, $user_id]);
                }
                
                // Actualizar datos adicionales según rol
                if ($role === 'inquilino') {
                    $sql_check = "SELECT id FROM inquilinos WHERE user_id = ?";
                    $stmt_check = $db->prepare($sql_check);
                    $stmt_check->execute([$user_id]);
                    
                    if ($stmt_check->fetch()) {
                        $sql_update = "UPDATE inquilinos SET dni = ?, telefono = ? WHERE user_id = ?";
                        $stmt_update = $db->prepare($sql_update);
                        $stmt_update->execute([$dni, $telefono, $user_id]);
                    } else {
                        $sql_insert = "INSERT INTO inquilinos (user_id, dni, telefono, estado, fecha_ingreso) VALUES (?, ?, ?, 'activo', NOW())";
                        $stmt_insert = $db->prepare($sql_insert);
                        $stmt_insert->execute([$user_id, $dni, $telefono]);
                    }
                }
                
                if ($role === 'empleado') {
                    $sql_check = "SELECT id FROM empleados WHERE user_id = ?";
                    $stmt_check = $db->prepare($sql_check);
                    $stmt_check->execute([$user_id]);
                    
                    if ($stmt_check->fetch()) {
                        $sql_update = "UPDATE empleados SET dni = ?, telefono = ? WHERE user_id = ?";
                        $stmt_update = $db->prepare($sql_update);
                        $stmt_update->execute([$dni, $telefono, $user_id]);
                    } else {
                        $sql_insert = "INSERT INTO empleados (user_id, dni, telefono, fecha_ingreso) VALUES (?, ?, ?, NOW())";
                        $stmt_insert = $db->prepare($sql_insert);
                        $stmt_insert->execute([$user_id, $dni, $telefono]);
                    }
                }
                
                $message = "✅ Usuario actualizado exitosamente: <strong>$name</strong>";
                logSecurityEvent($user_id, 'user_updated', "Usuario actualizado por admin: $name");
            } catch (PDOException $e) {
                $error = "Error al actualizar usuario: " . $e->getMessage();
            }
        }
    }
    
    // ELIMINAR USUARIO
    elseif ($action === 'delete') {
        $user_id = intval($_POST['user_id']);
        
        try {
            // Verificar que no sea el usuario actual
            if ($user_id == $_SESSION['user_id']) {
                $error = 'No puedes eliminar tu propio usuario.';
            } else {
                // Obtener datos antes de eliminar
                $stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Eliminar registros relacionados
                $db->prepare("DELETE FROM inquilinos WHERE user_id = ?")->execute([$user_id]);
                $db->prepare("DELETE FROM empleados WHERE user_id = ?")->execute([$user_id]);
                
                // Eliminar usuario
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                
                $message = "✅ Usuario eliminado: <strong>{$user_data['name']}</strong> ({$user_data['email']})";
                logSecurityEvent($_SESSION['user_id'], 'user_deleted', "Usuario eliminado por admin: {$user_data['name']}");
            }
        } catch (PDOException $e) {
            $error = "Error al eliminar usuario: " . $e->getMessage();
        }
    }
}

// OBTENER TODOS LOS USUARIOS
try {
    $query = "SELECT u.*, 
              i.dni as inquilino_dni, i.telefono as inquilino_telefono, i.estado as inquilino_estado,
              e.dni as empleado_dni, e.telefono as empleado_telefono
              FROM users u 
              LEFT JOIN inquilinos i ON u.id = i.user_id 
              LEFT JOIN empleados e ON u.id = e.user_id
              ORDER BY u.id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener usuarios: " . $e->getMessage();
    $usuarios = [];
}

// Contar por rol
$count_admin = count(array_filter($usuarios, fn($u) => $u['role'] === 'admin'));
$count_empleado = count(array_filter($usuarios, fn($u) => $u['role'] === 'empleado'));
$count_inquilino = count(array_filter($usuarios, fn($u) => $u['role'] === 'inquilino'));
?>

<div class="bento-page-header">
    <h1 class="bento-page-title"><i class="fas fa-users-cog"></i> Gestión de Usuarios (CRUD)</h1>
    <p class="bento-page-subtitle">Administración completa de usuarios del sistema</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Estadísticas -->
<div class="bento-stats-grid">
    <div class="bento-stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="bento-stat-number" style="color: white;"><?php echo count($usuarios); ?></div>
        <div class="bento-stat-label" style="color: rgba(255,255,255,0.9);">Total Usuarios</div>
    </div>
    <div class="bento-stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
        <div class="bento-stat-number" style="color: white;"><?php echo $count_admin; ?></div>
        <div class="bento-stat-label" style="color: rgba(255,255,255,0.9);">Administradores</div>
    </div>
    <div class="bento-stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
        <div class="bento-stat-number" style="color: white;"><?php echo $count_empleado; ?></div>
        <div class="bento-stat-label" style="color: rgba(255,255,255,0.9);">Empleados</div>
    </div>
    <div class="bento-stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
        <div class="bento-stat-number" style="color: white;"><?php echo $count_inquilino; ?></div>
        <div class="bento-stat-label" style="color: rgba(255,255,255,0.9);">Inquilinos</div>
    </div>
</div>

<!-- Botón Crear Usuario -->
<div style="margin: 20px 0;">
    <button onclick="openCreateModal()" class="bento-btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; font-size: 1.1em; border: none; border-radius: 10px; cursor: pointer; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
        <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
    </button>
</div>

<!-- Lista de Usuarios -->
<div class="bento-card">
    <h3 class="bento-card-title"><i class="fas fa-list"></i> Lista de Usuarios</h3>
    
    <?php if (!empty($usuarios)): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Nombre</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-user-tag"></i> Rol</th>
                        <th><i class="fas fa-id-card"></i> DNI</th>
                        <th><i class="fas fa-phone"></i> Teléfono</th>
                        <th><i class="fas fa-calendar"></i> Registro</th>
                        <th><i class="fas fa-cogs"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <?php
                        $dni = $usuario['inquilino_dni'] ?? $usuario['empleado_dni'] ?? 'N/A';
                        $telefono = $usuario['inquilino_telefono'] ?? $usuario['empleado_telefono'] ?? 'N/A';
                        $badge_color = [
                            'admin' => 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);',
                            'empleado' => 'background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);',
                            'inquilino' => 'background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);'
                        ][$usuario['role']] ?? '';
                        ?>
                        <tr>
                            <td><strong><?php echo $usuario['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($usuario['name']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td>
                                <span style="padding: 5px 15px; border-radius: 20px; color: white; font-size: 0.85em; <?php echo $badge_color; ?>">
                                    <i class="fas fa-<?php echo ['admin' => 'crown', 'empleado' => 'briefcase', 'inquilino' => 'home'][$usuario['role']]; ?>"></i>
                                    <?php echo ucfirst($usuario['role']); ?>
                                </span>
                            </td>
                            <td><code><?php echo htmlspecialchars($dni); ?></code></td>
                            <td><?php echo htmlspecialchars($telefono); ?></td>
                            <td><small><?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?></small></td>
                            <td>
                                <button onclick='editUser(<?php echo json_encode($usuario); ?>)' 
                                        class="bento-btn-icon" 
                                        style="background: #3b82f6; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; margin-right: 5px;"
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                <button onclick='deleteUser(<?php echo $usuario["id"]; ?>, "<?php echo htmlspecialchars($usuario["name"]); ?>")' 
                                        class="bento-btn-icon" 
                                        style="background: #ef4444; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer;"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bento-empty-state">
            <i class="fas fa-users"></i>
            <h3>No hay usuarios registrados</h3>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Crear/Editar Usuario -->
<div id="userModal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2 id="modalTitle"><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h2>
            <button onclick="closeModal()" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST" action="" class="modal-form">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="user_id" id="userId" value="">
            
            <div class="form-grid">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nombre Completo *</label>
                    <input type="text" name="name" id="userName" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" name="email" id="userEmail" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-user-tag"></i> Rol *</label>
                    <select name="role" id="userRole" required class="form-control" onchange="toggleRoleFields()">
                        <option value="">Seleccionar...</option>
                        <option value="admin">Administrador</option>
                        <option value="empleado">Empleado</option>
                        <option value="inquilino">Inquilino</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> <span id="passwordLabel">Contraseña *</span></label>
                    <input type="password" name="password" id="userPassword" class="form-control">
                    <input type="password" name="new_password" id="userNewPassword" class="form-control" style="display: none;">
                    <small id="passwordHint">Mínimo 8 caracteres</small>
                </div>
                
                <div class="form-group" id="dniField" style="display: none;">
                    <label><i class="fas fa-id-card"></i> DNI</label>
                    <input type="text" name="dni" id="userDni" class="form-control">
                </div>
                
                <div class="form-group" id="telefonoField" style="display: none;">
                    <label><i class="fas fa-phone"></i> Teléfono</label>
                    <input type="text" name="telefono" id="userTelefono" class="form-control">
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeModal()" class="bento-btn" style="background: #6b7280;">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="bento-btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="fas fa-save"></i> <span id="submitButtonText">Crear Usuario</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="deleteForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="user_id" id="deleteUserId">
</form>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s;
}

.modal-container {
    background: white;
    border-radius: 20px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    animation: slideUp 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 30px;
    border-radius: 20px 20px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5em;
}

.modal-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2em;
    transition: all 0.3s;
}

.modal-close:hover {
    background: #ef4444;
    transform: rotate(90deg);
}

.modal-form {
    padding: 30px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #1f2937;
}

.form-control {
    padding: 12px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1em;
    transition: all 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.modal-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 2px solid #f3f4f6;
}

.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border-left: 4px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function openCreateModal() {
    document.getElementById('userModal').style.display = 'flex';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Crear Nuevo Usuario';
    document.getElementById('formAction').value = 'create';
    document.getElementById('submitButtonText').textContent = 'Crear Usuario';
    document.getElementById('passwordLabel').textContent = 'Contraseña *';
    document.getElementById('userPassword').style.display = 'block';
    document.getElementById('userPassword').required = true;
    document.getElementById('userNewPassword').style.display = 'none';
    document.getElementById('passwordHint').textContent = 'Mínimo 8 caracteres';
    
    // Limpiar formulario
    document.getElementById('userId').value = '';
    document.getElementById('userName').value = '';
    document.getElementById('userEmail').value = '';
    document.getElementById('userRole').value = '';
    document.getElementById('userPassword').value = '';
    document.getElementById('userDni').value = '';
    document.getElementById('userTelefono').value = '';
    
    toggleRoleFields();
}

function editUser(user) {
    document.getElementById('userModal').style.display = 'flex';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Usuario';
    document.getElementById('formAction').value = 'update';
    document.getElementById('submitButtonText').textContent = 'Actualizar Usuario';
    document.getElementById('passwordLabel').textContent = 'Nueva Contraseña (opcional)';
    document.getElementById('userPassword').style.display = 'none';
    document.getElementById('userNewPassword').style.display = 'block';
    document.getElementById('userPassword').required = false;
    document.getElementById('passwordHint').textContent = 'Dejar en blanco para mantener la actual';
    
    // Llenar formulario
    document.getElementById('userId').value = user.id;
    document.getElementById('userName').value = user.name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userRole').value = user.role;
    document.getElementById('userDni').value = user.inquilino_dni || user.empleado_dni || '';
    document.getElementById('userTelefono').value = user.inquilino_telefono || user.empleado_telefono || '';
    
    toggleRoleFields();
}

function deleteUser(userId, userName) {
    if (confirm(`¿Estás seguro de que deseas eliminar al usuario "${userName}"?\n\nEsta acción no se puede deshacer.`)) {
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteForm').submit();
    }
}

function closeModal() {
    document.getElementById('userModal').style.display = 'none';
}

function toggleRoleFields() {
    const role = document.getElementById('userRole').value;
    const dniField = document.getElementById('dniField');
    const telefonoField = document.getElementById('telefonoField');
    
    if (role === 'empleado' || role === 'inquilino') {
        dniField.style.display = 'block';
        telefonoField.style.display = 'block';
    } else {
        dniField.style.display = 'none';
        telefonoField.style.display = 'none';
    }
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('userModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>
