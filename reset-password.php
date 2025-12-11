<?php
require_once 'includes/functions.php';

$message = '';
$error = '';
$token = $_GET['token'] ?? '';
$valid_token = false;

if (!empty($token)) {
    $user = verifyPasswordResetToken($token);
    $valid_token = !empty($user);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $error = 'Por favor complete todos los campos.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $result = resetPasswordWithToken($token, $password);
        if ($result['success']) {
            $message = $result['message'];
            $valid_token = false; // Token ya usado
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Sistema de Edificios</title>
    <link rel="stylesheet" href="assets/css/bento-glass-emerald.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bento-body">
    <div class="bento-login-container">
        <div class="bento-card bento-login-card fade-in">
            <div class="bento-login-header">
                <h1 class="bento-login-title"><i class="fas fa-lock-open"></i> Restablecer Contraseña</h1>
                <p class="bento-login-subtitle">Ingrese su nueva contraseña</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="bento-alert bento-alert-success">
                    <i class="fas fa-check-circle"></i> <strong>Éxito:</strong> <?php echo $message; ?>
                    <br><a href="login.php" class="bento-alert-link">Ir al login</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="bento-alert bento-alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($token)): ?>
                <div class="bento-alert bento-alert-warning">
                    <i class="fas fa-exclamation-circle"></i> <strong>Token requerido:</strong>
                    Esta página requiere un token de recuperación válido.
                    <br><a href="forgot-password.php" class="bento-alert-link">Solicitar nuevo token</a>
                </div>
            <?php elseif (!$valid_token): ?>
                <div class="bento-alert bento-alert-error">
                    <i class="fas fa-times-circle"></i> <strong>Token inválido:</strong>
                    El token de recuperación es inválido o ha expirado.
                    <br><a href="forgot-password.php" class="bento-alert-link">Solicitar nuevo token</a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="bento-form-group">
                        <label for="password" class="bento-form-label">
                            <i class="fas fa-lock"></i> Nueva Contraseña
                        </label>
                        <div class="bento-input-group">
                            <input type="password" id="password" name="password" class="bento-form-input"
                                   required placeholder="Ingrese nueva contraseña"
                                   minlength="8"
                                   title="Mínimo 8 caracteres con mayúsculas, minúsculas, números y símbolos">
                            <button type="button" class="bento-input-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                        <div class="bento-password-strength" id="password-strength"></div>
                    </div>

                    <div class="bento-form-group">
                        <label for="confirm_password" class="bento-form-label">
                            <i class="fas fa-lock"></i> Confirmar Contraseña
                        </label>
                        <div class="bento-input-group">
                            <input type="password" id="confirm_password" name="confirm_password" class="bento-form-input"
                                   required placeholder="Confirme la nueva contraseña">
                            <button type="button" class="bento-input-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye" id="confirm_password-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="bento-password-requirements">
                        <h4><i class="fas fa-shield-alt"></i> Requisitos de Contraseña</h4>
                        <ul id="requirements-list">
                            <li id="req-length" class="requirement unmet">
                                <i class="fas fa-times"></i> Mínimo 8 caracteres
                            </li>
                            <li id="req-uppercase" class="requirement unmet">
                                <i class="fas fa-times"></i> Al menos una mayúscula
                            </li>
                            <li id="req-lowercase" class="requirement unmet">
                                <i class="fas fa-times"></i> Al menos una minúscula
                            </li>
                            <li id="req-number" class="requirement unmet">
                                <i class="fas fa-times"></i> Al menos un número
                            </li>
                            <li id="req-special" class="requirement unmet">
                                <i class="fas fa-times"></i> Al menos un carácter especial
                            </li>
                            <li id="req-match" class="requirement unmet">
                                <i class="fas fa-times"></i> Las contraseñas coinciden
                            </li>
                        </ul>
                    </div>

                    <button type="submit" class="bento-btn bento-btn-primary bento-btn-full" id="submit-btn" disabled>
                        <i class="fas fa-save"></i> Actualizar Contraseña
                    </button>
                </form>
            <?php endif; ?>

            <div class="bento-login-links">
                <a href="login.php" class="bento-login-link">
                    <i class="fas fa-arrow-left"></i> Volver al Login
                </a>
                <a href="forgot-password.php" class="bento-login-link">
                    <i class="fas fa-key"></i> Nuevo Token
                </a>
            </div>
        </div>
    </div>

    <style>
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

        .bento-password-strength {
            margin-top: 8px;
            height: 4px;
            background: var(--color-light-gray);
            border-radius: 2px;
            overflow: hidden;
        }

        .bento-password-strength::after {
            content: '';
            display: block;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #ff4444, #ffaa00, #44aa44);
            transition: width 0.3s ease;
        }

        .bento-password-requirements {
            margin: 1.5rem 0;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: var(--border-radius-md);
            border-left: 4px solid var(--color-dark-blue);
        }

        .bento-password-requirements h4 {
            margin: 0 0 1rem 0;
            color: var(--text-primary);
            font-size: var(--font-size-lg);
        }

        .bento-password-requirements ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
        }

        .requirement.met {
            color: #28a745;
        }

        .requirement.met i {
            color: #28a745;
        }

        .requirement.unmet i {
            color: var(--color-pink);
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

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '-icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        function checkPasswordStrength(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password)
            };

            return requirements;
        }

        function updatePasswordValidation() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            const requirements = checkPasswordStrength(password);
            const match = password === confirmPassword && password.length > 0;

            // Actualizar requisitos visuales
            document.getElementById('req-length').className = requirements.length ? 'requirement met' : 'requirement unmet';
            document.getElementById('req-uppercase').className = requirements.uppercase ? 'requirement met' : 'requirement unmet';
            document.getElementById('req-lowercase').className = requirements.lowercase ? 'requirement met' : 'requirement unmet';
            document.getElementById('req-number').className = requirements.number ? 'requirement met' : 'requirement unmet';
            document.getElementById('req-special').className = requirements.special ? 'requirement met' : 'requirement unmet';
            document.getElementById('req-match').className = match ? 'requirement met' : 'requirement unmet';

            // Actualizar íconos
            document.querySelectorAll('.requirement i').forEach(icon => {
                const li = icon.parentElement;
                icon.className = li.classList.contains('met') ? 'fas fa-check' : 'fas fa-times';
            });

            // Actualizar barra de fortaleza
            const strengthBar = document.querySelector('.bento-password-strength::after');
            const metCount = Object.values(requirements).filter(Boolean).length;
            const strengthPercent = (metCount / 5) * 100;
            document.querySelector('.bento-password-strength').style.setProperty('--strength', strengthPercent + '%');

            // Habilitar/deshabilitar botón
            const submitBtn = document.getElementById('submit-btn');
            const allMet = Object.values(requirements).every(Boolean) && match;
            submitBtn.disabled = !allMet;
        }

        // Event listeners
        document.getElementById('password').addEventListener('input', updatePasswordValidation);
        document.getElementById('confirm_password').addEventListener('input', updatePasswordValidation);

        // Inicializar validación
        updatePasswordValidation();
    </script>
</body>
</html>