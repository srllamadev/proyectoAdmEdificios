<?php
/**
 * Script de prueba directa del chatbot
 * Para verificar que todas las funciones trabajan correctamente
 */

// Simular sesión de admin
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/deepseek_client.php';
require_once __DIR__ . '/config/database.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Chatbot - Edificio Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .test-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .test-section h3 {
            color: #333;
            margin-top: 0;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .info {
            color: #17a2b8;
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            max-height: 300px;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        .btn:hover {
            opacity: 0.9;
        }
        #chat-container {
            margin-top: 20px;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 20px;
            background: white;
        }
        #messages {
            height: 300px;
            overflow-y: auto;
            margin-bottom: 15px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        .message {
            margin: 10px 0;
            padding: 10px 15px;
            border-radius: 8px;
        }
        .user-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-left: 20%;
        }
        .bot-message {
            background: #e9ecef;
            color: #333;
            margin-right: 20%;
        }
        .input-group {
            display: flex;
            gap: 10px;
        }
        #message-input {
            flex: 1;
            padding: 12px;
            border: 2px solid #667eea;
            border-radius: 8px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Test del Chatbot</h1>
        <p class="subtitle">Prueba directa del chatbot con contexto del edificio</p>

        <!-- Test 1: Conexión a Base de Datos -->
        <div class="test-section">
            <h3>✅ Test 1: Conexión a Base de Datos</h3>
            <?php
            try {
                $db = new Database();
                $conn = $db->getConnection();
                echo "<p class='success'>✓ Conectado exitosamente</p>";
            } catch (Exception $e) {
                echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>

        <!-- Test 2: API Key -->
        <div class="test-section">
            <h3>🔑 Test 2: API Key DeepSeek</h3>
            <?php
            require_once __DIR__ . '/includes/env_loader.php';
            $apiKey = EnvLoader::get('DEEPSEEK_API_KEY');
            if ($apiKey) {
                echo "<p class='success'>✓ API Key cargada: " . substr($apiKey, 0, 10) . "...</p>";
            } else {
                echo "<p class='error'>✗ No se encontró la API Key</p>";
            }
            ?>
        </div>

        <!-- Test 3: Contexto del Edificio -->
        <div class="test-section">
            <h3>📊 Test 3: Contexto del Edificio</h3>
            <?php
            require_once __DIR__ . '/api/chatbot.php';
            
            // Capturar la función getBuildingContext
            $context = getBuildingContext();
            
            if ($context) {
                echo "<p class='success'>✓ Contexto obtenido exitosamente</p>";
                echo "<pre>" . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
            } else {
                echo "<p class='error'>✗ Error obteniendo contexto</p>";
            }
            ?>
        </div>

        <!-- Test 4: Chat Interactivo -->
        <div class="test-section">
            <h3>💬 Test 4: Chat Interactivo</h3>
            <div id="chat-container">
                <div id="messages"></div>
                <div class="input-group">
                    <input type="text" id="message-input" placeholder="Escribe un mensaje..." />
                    <button class="btn" onclick="sendMessage()">Enviar</button>
                </div>
            </div>
            <div style="margin-top: 15px;">
                <strong>Sugerencias:</strong>
                <button class="btn" onclick="sendSuggestion('¿Cuántos departamentos hay?')">Departamentos</button>
                <button class="btn" onclick="sendSuggestion('¿Cuánto se debe en total?')">Deudas</button>
                <button class="btn" onclick="sendSuggestion('¿Hay reservas pendientes?')">Reservas</button>
            </div>
        </div>
    </div>

    <script>
        const conversationHistory = [];

        function addMessage(text, isUser) {
            const messagesDiv = document.getElementById('messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
            messageDiv.textContent = text;
            messagesDiv.appendChild(messageDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        function sendSuggestion(text) {
            document.getElementById('message-input').value = text;
            sendMessage();
        }

        async function sendMessage() {
            const input = document.getElementById('message-input');
            const message = input.value.trim();
            
            if (!message) return;

            addMessage(message, true);
            input.value = '';

            // Añadir mensaje del usuario al historial
            conversationHistory.push({
                role: 'user',
                content: message
            });

            try {
                const response = await fetch('/proyectoAdmEdificios/api/chatbot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'send_message',
                        message: message,
                        history: conversationHistory
                    })
                });

                const data = await response.json();

                if (data.success) {
                    addMessage(data.message, false);
                    
                    // Añadir respuesta del bot al historial
                    conversationHistory.push({
                        role: 'assistant',
                        content: data.message
                    });
                } else {
                    addMessage('❌ Error: ' + (data.error || 'Error desconocido'), false);
                }
            } catch (error) {
                console.error('Error:', error);
                addMessage('❌ Error de conexión', false);
            }
        }

        // Permitir enviar con Enter
        document.getElementById('message-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Mensaje de bienvenida
        addMessage('¡Hola! Soy el asistente virtual del edificio. ¿En qué puedo ayudarte?', false);
    </script>
</body>
</html>
