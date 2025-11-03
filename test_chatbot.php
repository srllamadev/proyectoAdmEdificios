<?php
/**
 * Script de prueba para verificar la configuración del chatbot
 */

require_once __DIR__ . '/includes/env_loader.php';
require_once __DIR__ . '/includes/deepseek_client.php';

echo "<h1>🤖 Test del Chatbot - Edificio AI</h1>\n";
echo "<style>body { font-family: Arial, sans-serif; padding: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>\n";

// 1. Verificar archivo .env
echo "<h2>1. Verificación de archivo .env</h2>\n";
if (file_exists(__DIR__ . '/.env')) {
    echo "<p class='success'>✅ Archivo .env encontrado</p>\n";
} else {
    echo "<p class='error'>❌ Archivo .env NO encontrado. Copia .env.example a .env</p>\n";
    exit;
}

// 2. Verificar API Key
echo "<h2>2. Verificación de API Key</h2>\n";
$apiKey = EnvLoader::get('DEEPSEEK_API_KEY');
if ($apiKey && $apiKey !== 'tu_api_key_aqui') {
    echo "<p class='success'>✅ API Key configurada</p>\n";
    echo "<p class='info'>Longitud: " . strlen($apiKey) . " caracteres</p>\n";
} else {
    echo "<p class='error'>❌ API Key no configurada o usa el valor por defecto</p>\n";
    echo "<p>Edita el archivo .env y configura DEEPSEEK_API_KEY</p>\n";
    exit;
}

// 3. Verificar extensión cURL
echo "<h2>3. Verificación de cURL</h2>\n";
if (function_exists('curl_init')) {
    echo "<p class='success'>✅ Extensión cURL disponible</p>\n";
} else {
    echo "<p class='error'>❌ Extensión cURL NO disponible. Actívala en php.ini</p>\n";
    exit;
}

// 4. Test de conexión a la API
echo "<h2>4. Test de conexión con DeepSeek API</h2>\n";
try {
    $client = new DeepSeekClient();
    echo "<p class='success'>✅ Cliente DeepSeek creado correctamente</p>\n";
    
    echo "<p class='info'>Enviando mensaje de prueba...</p>\n";
    $response = $client->chat("Hola, di solo 'OK' si puedes leerme", [], "Eres un asistente de prueba. Responde solo con 'OK'.");
    
    if ($response['success']) {
        echo "<p class='success'>✅ Conexión exitosa con DeepSeek API</p>\n";
        echo "<details><summary>Ver respuesta</summary><pre>" . htmlspecialchars($response['message']) . "</pre></details>\n";
        
        if (isset($response['usage'])) {
            echo "<p class='info'>Tokens usados: " . $response['usage']['total_tokens'] . "</p>\n";
        }
    } else {
        echo "<p class='error'>❌ Error en la respuesta: " . htmlspecialchars($response['error']) . "</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al conectar con la API: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Posibles causas:</p>\n";
    echo "<ul>\n";
    echo "<li>API Key inválida</li>\n";
    echo "<li>Sin conexión a internet</li>\n";
    echo "<li>Firewall bloqueando la conexión</li>\n";
    echo "</ul>\n";
}

// 5. Verificar base de datos
echo "<h2>5. Verificación de Base de Datos</h2>\n";
try {
    require_once __DIR__ . '/config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p class='success'>✅ Conexión a base de datos OK</p>\n";
    
    // Verificar tablas necesarias
    $tables = ['departamentos', 'inquilinos', 'pagos', 'lecturas_consumo', 'reservas'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='success'>✅ Tabla '$table' existe</p>\n";
        } else {
            echo "<p class='error'>❌ Tabla '$table' NO existe</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "\n<hr>\n";
echo "<h2>✨ Resumen</h2>\n";
echo "<p>Si todos los tests pasaron, el chatbot está listo para usar.</p>\n";
echo "<p><a href='views/admin/dashboard.php' style='background: #667eea; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block;'>🚀 Ir al Dashboard</a></p>\n";
