<?php
// Función para hashear contraseñas (bcrypt)
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Función para verificar contraseñas
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Función para generar tokens seguros
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Función para sanitizar datos sensibles antes de loggear
function sanitizeForLog($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if (strpos(strtolower($key), 'password') !== false ||
                strpos(strtolower($key), 'token') !== false ||
                strpos(strtolower($key), 'secret') !== false) {
                $data[$key] = '[REDACTED]';
            }
        }
    }
    return $data;
}

// Función para encriptar datos sensibles (AES-256-CBC)
function encryptData($data, $key) {
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

// Función para desencriptar datos
function decryptData($encryptedData, $key) {
    $data = base64_decode($encryptedData);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
}

// Función para generar hash de archivos (integrity check)
function generateFileHash($filePath) {
    if (file_exists($filePath)) {
        return hash_file('sha256', $filePath);
    }
    return false;
}

// Función para validar email con regex seguro
function validateEmail($email) {
    $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    return preg_match($pattern, $email) === 1;
}

// Función para generar códigos de recuperación seguros
function generateRecoveryCode() {
    return strtoupper(substr(hash('sha256', random_bytes(32)), 0, 8));
}
?>