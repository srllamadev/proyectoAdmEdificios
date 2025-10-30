<?php
// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Credenciales simples para testing
$TEST_USERS = [
    'admin@admin.com' => ['password' => '12345678', 'name' => 'Administrador'],
    'empleado1@edificio.com' => ['password' => '12345678', 'name' => 'Empleado 1'],
    'empleado2@edificio.com' => ['password' => '12345678', 'name' => 'Empleado 2'],
    'empleado3@edificio.com' => ['password' => '12345678', 'name' => 'Empleado 3'],
    'inquilino1@edificio.com' => ['password' => '12345678', 'name' => 'Inquilino 1'],
    'inquilino2@edificio.com' => ['password' => '12345678', 'name' => 'Inquilino 2'],
    'inquilino3@edificio.com' => ['password' => '12345678', 'name' => 'Inquilino 3'],
    'inquilino4@edificio.com' => ['password' => '12345678', 'name' => 'Inquilino 4'],
    'inquilino5@edificio.com' => ['password' => '12345678', 'name' => 'Inquilino 5'],
];

$error = '';
$success = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (isset($TEST_USERS[$email]) && $TEST_USERS[$email]['password'] === $password) {
        $_SESSION['test_correos_logged_in'] = true;
        $_SESSION['test_correos_email'] = $email;
        $_SESSION['test_correos_name'] = $TEST_USERS[$email]['name'];
        header('Location: test_correos.php');
        exit;
    } else {
        $error = 'Email o contraseña incorrectos';
    }
}

// Si ya está logueado, redirigir
if (isset($_SESSION['test_correos_logged_in']) && $_SESSION['test_correos_logged_in']) {
    header('Location: test_correos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Correos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .login-left {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-left h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #10b981;
        }

        .login-left p {
            font-size: 1.1em;
            line-height: 1.6;
            color: #cbd5e1;
            margin-bottom: 30px;
        }

        .features {
            list-style: none;
        }

        .features li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .features li i {
            color: #10b981;
            font-size: 1.2em;
        }

        .login-right {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            color: #1e293b;
            font-size: 2em;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #64748b;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .test-credentials {
            margin-top: 30px;
            padding: 20px;
            background: #f1f5f9;
            border-radius: 10px;
            border-left: 4px solid #10b981;
        }

        .test-credentials h4 {
            color: #1e293b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .test-credentials p {
            color: #475569;
            font-size: 0.95em;
            margin-bottom: 10px;
        }

        .test-credentials code {
            background: white;
            padding: 2px 8px;
            border-radius: 5px;
            color: #10b981;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }

            .login-left {
                padding: 40px 30px;
            }

            .login-right {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <h1><i class="fas fa-envelope"></i> Sistema de Correos</h1>
            <p>Accede a la bandeja de entrada del sistema de administración de edificios</p>
            
            <ul class="features">
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Visualiza todos los correos del sistema</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Monitorea notificaciones de seguridad</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Revisa recuperaciones de contraseña</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Interfaz estilo Gmail moderna</span>
                </li>
            </ul>
        </div>

        <div class="login-right">
            <div class="login-header">
                <h2>Iniciar Sesión</h2>
                <p>Ingresa tus credenciales de prueba</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="tu@email.com" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Acceder
                </button>
            </form>

            <div class="test-credentials">
                <h4><i class="fas fa-info-circle"></i> Credenciales de Prueba</h4>
                <p><strong>Cualquier usuario:</strong> <code>12345678</code></p>
                <p>
                    <strong>Ejemplos:</strong><br>
                    📧 admin@admin.com<br>
                    📧 empleado1@edificio.com<br>
                    📧 inquilino1@edificio.com
                </p>
            </div>
        </div>
    </div>
</body>
</html>
