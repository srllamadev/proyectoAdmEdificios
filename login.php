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
    
    $debug_info = "Intento de login - Email: '$email' | Contraseña: '$password'";
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, complete todos los campos.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            $error = 'Error: No se pudo conectar a la base de datos.';
        } else {
            try {
                $query = "SELECT id, name, email, password, role FROM users WHERE email = :email AND role IN ('admin', 'empleado', 'inquilino')";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                $debug_info .= " | Registros encontrados: " . $stmt->rowCount();
                
                if ($stmt->rowCount() == 1) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    $debug_info .= " | Usuario: " . $user['name'] . " (" . $user['role'] . ")";
                    
                    // Verificar contraseña (debe ser exactamente "password")
                    if ($password === 'password') {
                        // Iniciar nueva sesión
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        
                        $debug_info .= " | ✅ Login exitoso!";
                        
                        // Redirigir según el rol
                        redirectToRolePage();
                    } else {
                        $error = 'Contraseña incorrecta. Debe usar exactamente: "password"';
                        $debug_info .= " | ❌ Contraseña incorrecta";
                    }
                } else {
                    $error = 'Usuario no encontrado. Verifique que el email sea exacto.';
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