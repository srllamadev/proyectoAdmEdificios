<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Administración de Edificios</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <style>
        /* Estilos para reCAPTCHA */
        .g-recaptcha {
            display: inline-block;
            margin: 0 auto;
            transform: scale(0.9);
            transform-origin: center;
        }
        
        @media (max-width: 480px) {
            .g-recaptcha {
                transform: scale(0.8);
            }
        }
        
        .recaptcha-info {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-left: 4px solid #2196f3;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card fade-in">
            <div class="login-header">
                <h1><i class="fas fa-building"></i> Sistema de Edificios</h1>
                <p>Acceso al panel de administración</p>
            </div>
            
            <?php if (isset($success_message) && !empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <strong>Éxito:</strong> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isLoggedIn() && !$login_attempt): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Atención:</strong> Ya hay una sesión activa para <?php echo $_SESSION['user_name']; ?> (<?php echo $_SESSION['role']; ?>).
                    <br><a href="?clear_session=1" style="color: #e74c3c; font-weight: bold; text-decoration: underline;">Haz clic aquí para cerrar la sesión actual</a>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($debug_info) && !empty($debug_info)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Debug:</strong> <?php echo $debug_info; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           placeholder="Ingresa tu email">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Contraseña:</label>
                    <input type="password" id="password" name="password" class="form-control" required 
                           placeholder="Ingresa tu contraseña">
                </div>
                
                <!-- reCAPTCHA -->
                <div class="form-group" style="text-align: center; margin: 25px 0;">
                    <div class="recaptcha-container" style="background: #f8f9fa; padding: 20px; border-radius: 10px; border: 2px dashed #dee2e6;">
                        <div style="margin-bottom: 15px;">
                            <i class="fas fa-shield-alt" style="color: #28a745; font-size: 1.2rem;"></i>
                            <strong style="color: #495057; margin-left: 8px;">Verificación de Seguridad</strong>
                        </div>
                        <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                        
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-full">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>
            
            <div class="demo-credentials">
                <h4><i class="fas fa-users"></i> Usuarios de Prueba:</h4>
                
                <div style="margin-bottom: 15px;">
                    <p><strong>Administrador:</strong></p>
                    <p style="font-family: monospace; background: white; padding: 8px; border-radius: 6px; margin: 5px 0;">
                        <strong style="color: var(--primary-blue);">admin@edificio.com</strong> / 
                        <strong style="color: var(--secondary-green);">password</strong>
                        <button type="button" onclick="fillCredentials('admin@edificio.com', 'password')" 
                                class="btn btn-secondary" style="float: right; padding: 4px 8px; font-size: 0.8rem;">
                            <i class="fas fa-copy"></i> Usar
                        </button>
                    </p>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <p><strong>Empleados:</strong></p>
                    <p style="font-family: monospace; background: white; padding: 8px; border-radius: 6px; margin: 5px 0;">
                        <strong style="color: var(--primary-blue);">empleado1@edificio.com</strong> / 
                        <strong style="color: var(--secondary-green);">password</strong>
                        <button type="button" onclick="fillCredentials('empleado1@edificio.com', 'password')" 
                                class="btn btn-secondary" style="float: right; padding: 4px 8px; font-size: 0.8rem;">
                            <i class="fas fa-copy"></i> Usar
                        </button>
                    </p>
                </div>
                
                <div>
                    <p><strong>Inquilinos:</strong></p>
                    <p style="font-family: monospace; background: white; padding: 8px; border-radius: 6px; margin: 5px 0;">
                        <strong style="color: var(--primary-blue);">inquilino1@edificio.com</strong> / 
                        <strong style="color: var(--secondary-green);">password</strong>
                        <button type="button" onclick="fillCredentials('inquilino1@edificio.com', 'password')" 
                                class="btn btn-secondary" style="float: right; padding: 4px 8px; font-size: 0.8rem;">
                            <i class="fas fa-copy"></i> Usar
                        </button>
                    </p>
                </div>
                
                <div style="background: var(--primary-blue); background: linear-gradient(135deg, var(--primary-blue), var(--secondary-green)); color: white; padding: 10px; border-radius: 6px; margin-top: 15px; text-align: center;">
                    <small><i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Haz clic en "Usar" para autocompletar las credenciales</small>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>