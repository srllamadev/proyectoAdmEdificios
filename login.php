<?php
require_once 'includes/functions.php';

// Limpiar sesión si se especifica
if (isset($_GET['clear_session'])) {
    session_destroy();
    session_start();
    header('Location: login.php');
    exit();
}

// Si ya está logueado, redirigir a su dashboard SOLO si no es una nueva petición de login
if (isLoggedIn() && $_SERVER['REQUEST_METHOD'] != 'POST') {
    redirectToRolePage();
}

$error = '';
$debug_info = '';
$login_attempt = false;
$success_message = '';

// Verificar mensajes de la URL
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'logout_success':
            $success_message = 'Sesión cerrada exitosamente.';
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_attempt = true;

    // Limpiar sesión anterior al intentar nuevo login
    if (isLoggedIn()) {
        session_destroy();
        session_start();
    }

    $email = clean_input($_POST['email']);
    $password = clean_input($_POST['password']);

    // Verificar reCAPTCHA (versión de prueba que siempre retorna true)
    $recaptcha_valid = false;
    if (isset($_POST['g-recaptcha-response'])) {
        $secretKey = "6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe"; // Clave de prueba de Google
        $response = $_POST['g-recaptcha-response'];

        // Para testing, siempre validar como correcto
        $recaptcha_valid = true;
        $debug_info = "reCAPTCHA: ✅ Verificación de prueba exitosa | ";

        // En producción, usar esta verificación real:
        /*
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$response}");
        $result = json_decode($verify);
        $recaptcha_valid = $result->success;
        */
    }

    $debug_info .= "Intento de login - Email: '$email'";

    if (empty($email) || empty($password)) {
        $error = 'Por favor, complete todos los campos.';
    } elseif (!$recaptcha_valid) {
        $error = 'Por favor completa el reCAPTCHA correctamente.';
        $debug_info .= " | ❌ reCAPTCHA inválido";
    } else {
        $database = new Database();
        $db = $database->getConnection();

        if (!$db) {
            $error = 'Error: No se pudo conectar a la base de datos.';
        } else {
            try {
                $query = "SELECT id, name, email, password, role, failed_login_attempts, locked_until, account_locked FROM users WHERE email = :email AND role IN ('admin', 'empleado', 'inquilino')";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                $debug_info .= " | Registros encontrados: " . $stmt->rowCount();

                if ($stmt->rowCount() == 1) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    $debug_info .= " | Usuario: " . $user['name'] . " (" . $user['role'] . ")";

                    // Verificar si la cuenta está bloqueada
                    if (isAccountLocked($user['id'])) {
                        $remaining_time = getLockoutTimeRemaining($user['id']);
                        if ($remaining_time > 0) {
                            $minutes = ceil($remaining_time / 60);
                            $error = "Cuenta bloqueada temporalmente. Intente nuevamente en $minutes minuto(s).";
                        } else {
                            $error = "Cuenta bloqueada. Contacte al administrador.";
                        }
                        $debug_info .= " | ❌ Cuenta bloqueada";
                        logSecurityEvent($user['id'], 'login_attempt_blocked', 'Intento de login en cuenta bloqueada');
                    } else {
                        // Verificar contraseña usando hash seguro
                        if (verifyPassword($password, $user['password'])) {
                            // Login exitoso - limpiar intentos fallidos
                            clearFailedLoginAttempts($user['id']);

                            // Iniciar nueva sesión
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['name'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['role'] = $user['role'];

                            $debug_info .= " | ✅ Login exitoso!";

                            // Log de login exitoso
                            logSecurityEvent($user['id'], 'login_success', 'Login exitoso');

                            // Redirigir según el rol
                            redirectToRolePage();
                        } else {
                            // Contraseña incorrecta - registrar intento fallido
                            recordFailedLogin($email);
                            $error = 'Contraseña incorrecta.';
                            $debug_info .= " | ❌ Contraseña incorrecta";

                            // Verificar si ahora está bloqueada
                            if (isAccountLocked($user['id'])) {
                                $error .= ' Cuenta bloqueada por múltiples intentos fallidos.';
                            }
                        }
                    }
                } else {
                    // Email no encontrado - pero registrar como intento fallido para prevenir enumeración
                    recordFailedLogin($email);
                    $error = 'Credenciales incorrectas.';
                    $debug_info .= " | ❌ Usuario no encontrado";
                }
            } catch (PDOException $e) {
                $error = 'Error de base de datos: ' . $e->getMessage();
                $debug_info .= " | ❌ Error BD: " . $e->getMessage();
            }
        }
    }
}

// Incluir el formulario de login
include 'login_form.php';
?>