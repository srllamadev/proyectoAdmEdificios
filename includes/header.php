<?php
// Función para obtener la ruta correcta al CSS desde cualquier subdirectorio
function getCSSPath() {
    // Obtener la ruta actual del script
    $currentPath = $_SERVER['SCRIPT_NAME'];
    
    // Contar cuántos niveles de subdirectorio hay
    $levels = substr_count($currentPath, '/') - 2; // -2 porque quitamos la parte base
    
    // Construir la ruta relativa al CSS
    $basePath = str_repeat('../', max(0, $levels));
    return $basePath . 'assets/css/style.css';
}

// Función para obtener la clase de usuario para el body
function getUserClass() {
    if (isset($_SESSION['role'])) {
        return 'user-' . $_SESSION['role'];
    }
    return 'user-guest';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Sistema de Administración de Edificios'; ?></title>
    <link rel="stylesheet" href="<?php echo getCSSPath(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="<?php echo getUserClass(); ?>"><?php if (isset($_SESSION['user_name'])): ?>
    <div class="top-nav">
        <div class="user-info">
            <i class="fas fa-user-circle" style="font-size: 1.5rem; color: var(--primary-blue);"></i>
            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <span class="status-badge status-active"><?php echo ucfirst($_SESSION['role']); ?></span>
        </div>
        
        <div class="nav-links">
            <?php
            // Base path relativo al root del proyecto desde el script actual
            $basePath = str_repeat('../', max(0, substr_count($_SERVER['SCRIPT_NAME'], '/') - 2));

            $dashboard_link = '';
            switch($_SESSION['role']) {
                case 'admin':
                    $dashboard_link = $basePath . 'views/admin/dashboard.php';
                    break;
                case 'empleado':
                    $dashboard_link = $basePath . 'views/empleado/dashboard.php';
                    break;
                case 'inquilino':
                    $dashboard_link = $basePath . 'views/inquilino/dashboard.php';
                    break;
            }

            // Contar morosidad para mostrar badge (solo para admin)
            $overdue_link = '';
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                // Intentar cargar conexión DB (silencioso en fallo)
                try {
                    require_once __DIR__ . '/db.php';
                    $stmt = $pdo->query("SELECT COUNT(*) FROM invoices WHERE status <> 'paid' AND due_date IS NOT NULL AND due_date < CURDATE()");
                    $overdue_count = (int)$stmt->fetchColumn();
                } catch (Exception $e) {
                    $overdue_count = 0;
                }
                $overdue_link = $basePath . 'finanzas.php';
            }
            ?>
            <a href="<?php echo $dashboard_link; ?>"><i class="fas fa-home"></i> Inicio</a>
            <?php if (!empty($overdue_link)): ?>
                <a href="<?php echo $overdue_link; ?>"><i class="fas fa-wallet"></i> Finanzas<?php if (!empty($overdue_count) && $overdue_count > 0) echo ' <span class="status-badge status-expired">'.htmlspecialchars($overdue_count).'</span>'; ?></a>
            <?php endif; ?>
            <a href="<?php echo $basePath . 'logout.php'; ?>" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
<?php endif; ?>

<div class="container">