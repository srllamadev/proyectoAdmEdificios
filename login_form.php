<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Administración de Edificios</title>
    <link rel="stylesheet" href="assets/css/bento-glass-emerald.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <style>
        /* Layout de dos columnas para login */
        .bento-login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .bento-login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1200px;
            width: 100%;
            background: var(--glass-white);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: var(--shadow-xl), var(--glow-emerald);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Columna izquierda - Logo */
        .bento-login-logo-section {
            background: linear-gradient(135deg, #009B77 0%, #7ED957 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem;
            position: relative;
            overflow: hidden;
        }
        
        .bento-login-logo-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        .bento-login-logo-section img {
            max-width: 80%;
            height: auto;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.3));
            position: relative;
            z-index: 1;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .bento-login-logo-text {
            margin-top: 2rem;
            text-align: center;
            color: white;
            position: relative;
            z-index: 1;
        }
        
        .bento-login-logo-text h2 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .bento-login-logo-text p {
            font-size: 1rem;
            opacity: 0.95;
            font-weight: 500;
        }
        
        /* Columna derecha - Formulario */
        .bento-login-form-section {
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
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
        
        .bento-input-group {
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .bento-input-toggle {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 8px;
            border-radius: var(--border-radius-sm);
            transition: var(--transition-normal);
        }
        
        .bento-input-toggle:hover {
            color: var(--text-primary);
            background: var(--bg-secondary);
        }
        
        .bento-input-group .bento-form-input {
            padding-right: 50px;
        }
        
        .bento-login-links {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 155, 119, 0.1);
        }
        
        .bento-login-link {
            color: var(--color-emerald);
            text-decoration: none;
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            transition: var(--transition-normal);
        }
        
        .bento-login-link:hover {
            color: var(--color-lime);
        }
        
        /* Responsive */
        @media (max-width: 968px) {
            .bento-login-container {
                grid-template-columns: 1fr;
            }
            
            .bento-login-logo-section {
                padding: 3rem 2rem;
            }
            
            .bento-login-logo-section img {
                max-width: 60%;
            }
            
            .bento-login-form-section {
                padding: 3rem 2rem;
            }
        }
    </style>
</head>
<body class="bento-body">
    <div class="bento-login-wrapper">
        <div class="bento-login-container fade-in">
            <!-- Columna Izquierda - Logo -->
            <div class="bento-login-logo-section">
                <img src="assets/img/logo1.png" alt="SLH - Sistema de Gestión de Edificios">
                <div class="bento-login-logo-text">
                    <h2><i class="fas fa-leaf"></i> SLH</h2>
                    <p>Sistema de Administración de Edificios</p>
                </div>
            </div>
            
            <!-- Columna Derecha - Formulario -->
            <div class="bento-login-form-section">
                <div class="bento-login-header" style="margin-bottom: 2rem;">
                    <h1 class="bento-page-title" style="font-size: 2rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </h1>
                    <p class="bento-page-subtitle" style="font-size: 1rem;">Acceso al panel de administración</p>
                </div>
            
            <?php if (isset($success_message) && !empty($success_message)): ?>
                <div class="bento-alert bento-alert-success">
                    <i class="fas fa-check-circle"></i> <strong>Éxito:</strong> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isLoggedIn() && !$login_attempt): ?>
                <div class="bento-alert bento-alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Atención:</strong> Ya hay una sesión activa para <?php echo $_SESSION['user_name']; ?> (<?php echo $_SESSION['role']; ?>).
                    <br><a href="?clear_session=1" class="bento-alert-link">Haz clic aquí para cerrar la sesión actual</a>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <!--<div class="bento-alert bento-alert-error">-->
                    <!--<i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> <?php echo $error; ?>-->
                <!--</div>-->
            <?php endif; ?>
            
            <?php if (isset($debug_info) && !empty($debug_info)): ?>
                <div class="bento-alert bento-alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Debug:</strong> <?php echo $debug_info; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="bento-form-group">
                    <label for="email" class="bento-form-label"><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" id="email" name="email" class="bento-form-input" required 
                           placeholder="Ingresa tu email">
                </div>
                
                <div class="bento-form-group">
                    <label for="password" class="bento-form-label"><i class="fas fa-lock"></i> Contraseña:</label>
                    <div class="bento-input-group">
                        <input type="password" id="password" name="password" class="bento-form-input" required 
                               placeholder="Ingresa tu contraseña">
                        <button type="button" class="bento-input-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="password-icon"></i>
                        </button>
                    </div>
                </div>
                
                <!-- reCAPTCHA -->
                <div class="bento-form-group bento-recaptcha-group">
                    <div class="bento-recaptcha-container">
                        <div class="bento-recaptcha-header">
                            <i class="fas fa-shield-alt"></i>
                            <strong>Verificación de Seguridad</strong>
                        </div>
                        <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                    </div>
                </div>
                
                <button type="submit" class="bento-btn bento-btn-primary bento-btn-full">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>
            
            <div class="bento-login-links">
                <a href="register.php" class="bento-login-link">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </a>
                <a href="forgot-password.php" class="bento-login-link">
                    <i class="fas fa-key"></i> Olvidé mi Contraseña
                </a>
            </div>
            
            <!--elimine unas cositas con codigo: DERFS12-->
                
                <div class="bento-demo-tip" style="margin-top: 1rem;">
                    
                </div>
            </div>
        </div>
    </div>
    </div>
    
    <script>
        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
        
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('password-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>