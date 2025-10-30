<?php
require_once 'includes/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = clean_input($_POST['email']);

    if (empty($email)) {
        $error = 'Por favor ingrese su email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor ingrese un email válido.';
    } else {
        // Verificar si el email existe
        $database = new Database();
        $db = $database->getConnection();

        if ($db) {
            $sql = "SELECT id, name FROM users WHERE email = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Crear token de recuperación
                $token = createPasswordResetToken($email);

                // Enviar email de recuperación
                $emailSent = sendPasswordResetEmail($email, $token, $user['name']);
                
                if ($emailSent || DEVELOPMENT_MODE) {
                    // En modo desarrollo, mostrar el enlace directamente
                    if (DEVELOPMENT_MODE) {
                        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/proyectoAdmEdificios/reset-password.php?token=" . $token;
                        $message .= "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 15px; margin: 20px 0; text-align: center; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);'>";
                        $message .= "<p style='color: white; margin-bottom: 20px; font-size: 1.1em;'>� <strong>Hola, {$user['name']}</strong></p>";
                        $message .= "<p style='color: rgba(255,255,255,0.9); margin-bottom: 25px;'>Haz clic en el botón de abajo para restablecer tu contraseña:</p>";
                        $message .= "<a href='$reset_link' style='display: inline-block; background: white; color: #667eea; padding: 15px 40px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 1.1em; box-shadow: 0 5px 15px rgba(0,0,0,0.2); transition: transform 0.2s;' onmouseover='this.style.transform=\"scale(1.05)\"' onmouseout='this.style.transform=\"scale(1)\"'>";
                        $message .= "🔐 Restablecer Mi Contraseña";
                        $message .= "</a>";
                        $message .= "<p style='color: rgba(255,255,255,0.8); margin-top: 25px; font-size: 0.9em;'>⏰ Este enlace expira en <strong>1 hora</strong></p>";
                        $message .= "</div>";
                        $message .= "<div style='background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
                        $message .= "</div>";
                    } else {
                        $message = 'Se ha enviado un correo con las instrucciones para restablecer su contraseña.';
                    }
                } else {
                    $error = 'Error al enviar el correo. Intente nuevamente más tarde.';
                }

                // Log del evento
                logSecurityEvent($user['id'], 'password_reset_requested', 'Solicitud de recuperación de contraseña');
            } else {
                // No revelar si el email existe o no por seguridad
                $message = 'Si el email está registrado, recibirá instrucciones para restablecer su contraseña.';
            }
        } else {
            $error = 'Error de conexión a la base de datos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olvidé mi Contraseña - Sistema de Edificios</title>
    <link rel="stylesheet" href="assets/css/bento-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bento-body">
    <div class="bento-login-container">
        <div class="bento-card bento-login-card fade-in">
            <div class="bento-login-header">
                <h1 class="bento-login-title"><i class="fas fa-key"></i> Recuperar Contraseña</h1>
                <p class="bento-login-subtitle">Ingrese su email para recibir instrucciones</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="bento-alert bento-alert-success">
                    <i class="fas fa-check-circle"></i> <strong>Éxito:</strong> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="bento-alert bento-alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="bento-form-group">
                    <label for="email" class="bento-form-label">
                        <i class="fas fa-envelope"></i> Email Registrado
                    </label>
                    <input type="email" id="email" name="email" class="bento-form-input"
                           required placeholder="Ingrese su email"
                           title="Email con el que se registró en el sistema">
                    <small class="bento-form-help">
                        <i class="fas fa-info-circle"></i> Recibirá un enlace seguro para restablecer su contraseña
                    </small>
                </div>

                <button type="submit" class="bento-btn bento-btn-primary bento-btn-full">
                    <i class="fas fa-paper-plane"></i> Enviar Instrucciones
                </button>
            </form>

            <div class="bento-login-links">
                <a href="login.php" class="bento-login-link">
                    <i class="fas fa-arrow-left"></i> Volver al Login
                </a>
                <a href="register.php" class="bento-login-link">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </a>
            </div>

            <div class="bento-security-info">
                <h4><i class="fas fa-shield-alt"></i> Información de Seguridad</h4>
                <ul>
                    <li>El enlace de recuperación es válido por 1 hora</li>
                    <li>Nunca compartimos su información con terceros</li>
                    <li>Use una contraseña segura para proteger su cuenta</li>
                </ul>
            </div>
        </div>
    </div>

    <style>
        .bento-security-info {
            margin-top: 2rem;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: var(--border-radius-md);
            border-left: 4px solid var(--color-dark-blue);
        }

        .bento-security-info h4 {
            margin: 0 0 1rem 0;
            color: var(--text-primary);
            font-size: var(--font-size-lg);
        }

        .bento-security-info ul {
            margin: 0;
            padding-left: 1.5rem;
        }

        .bento-security-info li {
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-size: var(--font-size-sm);
        }

        .bento-login-links {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--color-light-gray);
        }

        .bento-login-link {
            color: var(--color-dark-blue);
            text-decoration: none;
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            transition: var(--transition-normal);
        }

        .bento-login-link:hover {
            color: var(--color-pink);
        }
    </style>
</body>
</html>