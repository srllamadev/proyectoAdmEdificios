<?php
// EJEMPLO: Cómo debería funcionar el login REAL (no el actual simplificado)

// 1. Para crear un usuario con contraseña hasheada:
function createUser($email, $password, $role = 'inquilino') {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    return $stmt->execute([$email, $hashedPassword, $role]);
}

// 2. Para verificar login REAL:
function authenticateUser($email, $password) {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($password, $user['password'])) {
            // Login exitoso
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
    }
    return false;
}

// 3. Para cambiar contraseña:
function changePassword($userId, $newPassword) {
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$hashedPassword, $userId]);
}

// EJEMPLOS DE USO:
/*
// Crear usuario
createUser('nuevo@usuario.com', 'mi_contraseña_segura', 'inquilino');

// Verificar login
if (authenticateUser('usuario@email.com', 'contraseña')) {
    echo "Login exitoso!";
}

// Cambiar contraseña
changePassword(1, 'nueva_contraseña_muy_segura');
*/
?>