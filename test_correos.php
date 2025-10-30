<?php
// Incluir functions.php ANTES de cualquier HTML para evitar errores de sesión
require_once 'includes/functions.php';

// Verificar si el usuario está logueado en el sistema de correos
if (!isset($_SESSION['test_correos_logged_in']) || !$_SESSION['test_correos_logged_in']) {
    header('Location: login_correos.php');
    exit;
}

// Obtener datos del usuario logueado
$current_user_email = $_SESSION['test_correos_email'] ?? 'Usuario';
$current_user_name = $_SESSION['test_correos_name'] ?? 'Usuario';

// Procesar logout
if (isset($_GET['logout'])) {
    unset($_SESSION['test_correos_logged_in']);
    unset($_SESSION['test_correos_email']);
    unset($_SESSION['test_correos_name']);
    header('Location: login_correos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Entrada - Sistema de Edificios</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f6f8fc;
            color: #1f2937;
            overflow-x: hidden;
        }

        /* Gmail-style Header */
        .gmail-header {
            background: #1e293b;
            color: white;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 600;
        }

        .logo i {
            font-size: 28px;
            color: #10b981;
        }

        .search-bar {
            background: #334155;
            border-radius: 8px;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 400px;
        }

        .search-bar input {
            background: transparent;
            border: none;
            color: white;
            outline: none;
            width: 100%;
            font-size: 14px;
        }

        .search-bar input::placeholder {
            color: #94a3b8;
        }

        .search-bar i {
            color: #94a3b8;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
        }

        .header-icon:hover {
            background: #334155;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
        }

        /* Gmail Layout */
        .gmail-container {
            display: flex;
            height: calc(100vh - 64px);
        }

        /* Sidebar */
        .gmail-sidebar {
            width: 260px;
            background: white;
            border-right: 1px solid #e2e8f0;
            padding: 20px 10px;
            overflow-y: auto;
        }

        .compose-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 24px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
            transition: all 0.2s;
        }

        .compose-btn:hover {
            background: #059669;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
            transform: translateY(-1px);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px 16px;
            border-radius: 0 24px 24px 0;
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 4px;
            color: #475569;
            font-size: 14px;
        }

        .nav-item:hover {
            background: #f1f5f9;
        }

        .nav-item.active {
            background: #e0f2fe;
            color: #0284c7;
            font-weight: 600;
        }

        .nav-item i {
            font-size: 18px;
            width: 20px;
        }

        .nav-item .badge {
            margin-left: auto;
            background: #0284c7;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Main Content */
        .gmail-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .toolbar {
            background: white;
            padding: 12px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toolbar-btn {
            background: transparent;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }

        .toolbar-btn:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .toolbar-divider {
            width: 1px;
            height: 24px;
            background: #e2e8f0;
            margin: 0 8px;
        }

        /* Email List */
        .email-list-container {
            flex: 1;
            overflow-y: auto;
            background: white;
        }

        .email-item {
            display: grid;
            grid-template-columns: 50px 200px 1fr 180px;
            align-items: center;
            padding: 12px 24px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: all 0.2s;
        }

        .email-item:hover {
            background: #f8fafc;
            box-shadow: inset 2px 0 0 #10b981;
        }

        .email-item.unread {
            background: #fafbfc;
            font-weight: 600;
        }

        .email-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .email-star {
            color: #cbd5e1;
            cursor: pointer;
            transition: color 0.2s;
        }

        .email-star:hover {
            color: #fbbf24;
        }

        .email-star.starred {
            color: #fbbf24;
        }

        .email-sender {
            font-weight: 600;
            color: #1e293b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .email-subject {
            color: #475569;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: 0 20px;
        }

        .email-subject .snippet {
            color: #94a3b8;
            font-weight: normal;
        }

        .email-date {
            color: #64748b;
            font-size: 13px;
            text-align: right;
        }

        .email-label {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 8px;
        }

        .label-security {
            background: #fee2e2;
            color: #dc2626;
        }

        .label-recovery {
            background: #dbeafe;
            color: #2563eb;
        }

        .label-success {
            background: #d1fae5;
            color: #059669;
        }

        /* Right Panel - Email Detail */
        .email-detail-panel {
            width: 0;
            background: white;
            border-left: 1px solid #e2e8f0;
            overflow: hidden;
            transition: width 0.3s;
        }

        .email-detail-panel.open {
            width: 600px;
        }

        /* Status Cards */
        .status-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 24px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
        }

        .status-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .status-card.success {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        }

        .status-card.warning {
            background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
        }

        .status-card.info {
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        }

        .status-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .status-card-icon {
            font-size: 32px;
            opacity: 0.9;
        }

        .status-card-value {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .status-card-label {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 8px;
            color: #64748b;
        }

        /* Action Buttons */
        .action-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .action-btn:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .action-btn.secondary {
            background: #0284c7;
        }

        .action-btn.secondary:hover {
            background: #0369a1;
        }

        /* Quick Actions */
        .quick-actions {
            padding: 20px 24px;
            background: white;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <!-- Gmail-style Header -->
    <div class="gmail-header">
        <div class="header-left">
            <div class="logo">
                <i class="fas fa-envelope"></i>
                <span>Sistema de Correos</span>
            </div>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar en correos...">
            </div>
        </div>
        <div class="header-right">
            <div class="header-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="header-icon">
                <i class="fas fa-cog"></i>
            </div>
            <div class="user-avatar" title="<?php echo htmlspecialchars($current_user_name); ?> (<?php echo htmlspecialchars($current_user_email); ?>)">
                <i class="fas fa-user"></i>
            </div>
            <a href="?logout=1" class="header-icon" title="Cerrar Sesión" style="color: #ef4444; margin-left: 10px;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

    <!-- Gmail Container -->
    <div class="gmail-container">
        <!-- Sidebar -->
        <div class="gmail-sidebar">
            <button class="compose-btn">
                <i class="fas fa-pen"></i>
                Redactar
            </button>

            <div class="nav-item active">
                <i class="fas fa-inbox"></i>
                <span>Recibidos</span>
                <span class="badge" id="inbox-count">0</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-star"></i>
                <span>Destacados</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-paper-plane"></i>
                <span>Enviados</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-file-alt"></i>
                <span>Borradores</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-exclamation-circle"></i>
                <span>Spam</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-trash"></i>
                <span>Papelera</span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="gmail-main">
            <!-- Welcome Message -->
            <div style="padding: 20px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 15px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-user-circle" style="font-size: 3em;"></i>
                <div>
                    <h3 style="margin: 0; font-size: 1.5em;">¡Bienvenido, <?php echo htmlspecialchars($current_user_name); ?>!</h3>
                    <p style="margin: 5px 0 0 0; opacity: 0.9;"><?php echo htmlspecialchars($current_user_email); ?></p>
                </div>
            </div>

            <!-- Status Cards -->
            <div class="status-cards">
                <?php
                // Verificar directorio de logs
                $log_dir = 'logs/emails';
                $email_count = 0;
                $today_count = 0;
                
                if (is_dir($log_dir)) {
                    $emails = glob($log_dir . '/email_*.html');
                    $email_count = count($emails);
                    
                    // Contar correos de hoy
                    foreach ($emails as $email) {
                        if (date('Y-m-d', filemtime($email)) == date('Y-m-d')) {
                            $today_count++;
                        }
                    }
                }
                ?>
                
                <div class="status-card success">
                    <div class="status-card-header">
                        <div class="status-card-icon"><i class="fas fa-envelope"></i></div>
                    </div>
                    <div class="status-card-value"><?php echo $email_count; ?></div>
                    <div class="status-card-label">Total de Correos</div>
                </div>

                <div class="status-card info">
                    <div class="status-card-header">
                        <div class="status-card-icon"><i class="fas fa-calendar-day"></i></div>
                    </div>
                    <div class="status-card-value"><?php echo $today_count; ?></div>
                    <div class="status-card-label">Correos Hoy</div>
                </div>

                <div class="status-card">
                    <div class="status-card-header">
                        <div class="status-card-icon"><i class="fas fa-shield-alt"></i></div>
                    </div>
                    <div class="status-card-value">
                        <?php echo DEVELOPMENT_MODE ? 'DEV' : 'PROD'; ?>
                    </div>
                    <div class="status-card-label">Modo del Sistema</div>
                </div>

                <?php
                try {
                    $database = new Database();
                    $pdo = $database->getConnection();
                    
                    // Contar eventos de seguridad del día
                    $sql = "SELECT COUNT(*) as count FROM security_logs WHERE DATE(created_at) = CURDATE()";
                    $stmt = $pdo->query($sql);
                    $security_count = $stmt->fetch()['count'];
                ?>
                
                <div class="status-card warning">
                    <div class="status-card-header">
                        <div class="status-card-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                    <div class="status-card-value"><?php echo $security_count; ?></div>
                    <div class="status-card-label">Eventos de Seguridad Hoy</div>
                </div>
                
                <?php
                } catch (Exception $e) {
                    echo "<div class='status-card warning'>";
                    echo "<div class='status-card-header'><div class='status-card-icon'><i class='fas fa-exclamation-triangle'></i></div></div>";
                    echo "<div class='status-card-value'>!</div>";
                    echo "<div class='status-card-label'>Error al cargar</div>";
                    echo "</div>";
                }
                ?>
            </div>

            <!-- Toolbar -->
            <div class="toolbar">
                <input type="checkbox" class="email-checkbox">
                <button class="toolbar-btn" title="Recargar">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button class="toolbar-btn" title="Más acciones">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="toolbar-divider"></div>
                <button class="toolbar-btn" title="Anterior">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="toolbar-btn" title="Siguiente">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- Email List -->
            <div class="email-list-container">
                <?php
                if ($email_count > 0) {
                    // Ordenar por fecha (más reciente primero)
                    usort($emails, function($a, $b) {
                        return filemtime($b) - filemtime($a);
                    });
                    
                    foreach ($emails as $index => $email) {
                        $filename = basename($email);
                        $date = date('H:i', filemtime($email));
                        $today = date('Y-m-d', filemtime($email)) == date('Y-m-d');
                        
                        if (!$today) {
                            $date = date('d M', filemtime($email));
                        }
                        
                        // Determinar tipo de correo por nombre de archivo
                        $subject = "Notificación del Sistema";
                        $label_class = "label-security";
                        $label_text = "Seguridad";
                        
                        if (strpos($filename, 'password') !== false || strpos($filename, 'reset') !== false) {
                            $subject = "Recuperación de Contraseña";
                            $label_class = "label-recovery";
                            $label_text = "Recuperación";
                        } elseif (strpos($filename, 'failed') !== false || strpos($filename, 'attempt') !== false) {
                            $subject = "Alerta de Seguridad - Intentos Fallidos";
                            $label_class = "label-security";
                            $label_text = "Alerta";
                        } elseif (strpos($filename, 'success') !== false || strpos($filename, 'change') !== false) {
                            $subject = "Contraseña Actualizada";
                            $label_class = "label-success";
                            $label_text = "Éxito";
                        }
                        
                        $unread_class = $index < 2 ? 'unread' : '';
                        $email_path = str_replace('\\', '/', $email);
                        
                        echo "<div class='email-item $unread_class' onclick='openEmail(\"$email_path\")' style='cursor: pointer;'>";
                        echo "  <div><input type='checkbox' class='email-checkbox' onclick='event.stopPropagation();'></div>";
                        echo "  <div class='email-sender'>";
                        echo "    <i class='fas fa-star email-star'></i> Sistema Edificio";
                        echo "  </div>";
                        echo "  <div class='email-subject'>";
                        echo "    <span class='email-label $label_class'>$label_text</span>";
                        echo "    <strong>$subject</strong> ";
                        echo "    <span class='snippet'>- Ver detalles del correo...</span>";
                        echo "  </div>";
                        echo "  <div class='email-date'>$date</div>";
                        echo "</div>";
                    }
                    
                    // Actualizar contador
                    echo "<script>document.getElementById('inbox-count').textContent = '$email_count';</script>";
                } else {
                    echo "<div class='empty-state'>";
                    echo "  <i class='fas fa-inbox'></i>";
                    echo "  <h3>No hay correos</h3>";
                    echo "  <p>Tu bandeja de entrada está vacía. Los correos aparecerán aquí cuando se generen.</p>";
                    echo "</div>";
                }
                ?>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="login.php" class="action-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Ir al Login
                </a>
                <a href="forgot-password.php" class="action-btn secondary">
                    <i class="fas fa-key"></i>
                    Recuperar Contraseña
                </a>
                <a href="logs/emails" class="action-btn" style="background: #64748b;" target="_blank">
                    <i class="fas fa-folder-open"></i>
                    Ver Carpeta de Correos
                </a>
            </div>
        </div>
    </div>

    <!-- Modal para ver correo completo -->
    <div id="emailModal" class="email-modal">
        <div class="email-modal-content">
            <div class="email-modal-header">
                <h3><i class="fas fa-envelope-open"></i> Detalles del Correo</h3>
                <button class="email-modal-close" onclick="closeEmailModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="email-modal-body">
                <iframe id="emailFrame" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>

    <style>
        .email-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .email-modal-content {
            position: relative;
            background-color: white;
            margin: 2% auto;
            width: 90%;
            max-width: 1200px;
            height: 90vh;
            border-radius: 15px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            animation: slideDown 0.3s;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .email-modal-header {
            padding: 20px 30px;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .email-modal-header h3 {
            margin: 0;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .email-modal-close {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2em;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .email-modal-close:hover {
            background: #ef4444;
            transform: rotate(90deg);
        }

        .email-modal-body {
            flex: 1;
            overflow: hidden;
            padding: 0;
        }

        .email-item:hover {
            background: #f1f5f9;
            transform: translateX(5px);
        }
    </style>

    <script>
        function openEmail(emailPath) {
            const modal = document.getElementById('emailModal');
            const iframe = document.getElementById('emailFrame');
            
            // Cargar el correo en el iframe
            iframe.src = emailPath;
            
            // Mostrar el modal
            modal.style.display = 'block';
            
            // Cerrar con ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeEmailModal();
                }
            });
        }

        function closeEmailModal() {
            const modal = document.getElementById('emailModal');
            modal.style.display = 'none';
            document.getElementById('emailFrame').src = '';
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('emailModal');
            if (event.target === modal) {
                closeEmailModal();
            }
        }

        // Recargar página
        document.querySelector('.toolbar-btn[title="Recargar"]')?.addEventListener('click', function() {
            location.reload();
        });
    </script>
</body>
</html>
