<?php
/**
 * Cliente para la API de DeepSeek
 */

require_once __DIR__ . '/env_loader.php';

class DeepSeekClient {
    private $apiKey;
    private $apiUrl = 'https://api.deepseek.com/v1/chat/completions';
    
    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey ?? EnvLoader::get('DEEPSEEK_API_KEY');
        
        if (!$this->apiKey) {
            throw new Exception('API Key de DeepSeek no configurada');
        }
    }
    
    /**
     * Enviar mensaje al chatbot
     */
    public function chat($message, $context = [], $systemPrompt = null) {
        $messages = [];
        
        // Prompt del sistema
        if ($systemPrompt) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt
            ];
        }
        
        // Contexto previo (historial)
        foreach ($context as $msg) {
            $messages[] = $msg;
        }
        
        // Mensaje actual del usuario
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];
        
        $data = [
            'model' => 'deepseek-chat',
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'stream' => false
        ];
        
        $response = $this->makeRequest($data);
        
        if (isset($response['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'message' => $response['choices'][0]['message']['content'],
                'usage' => $response['usage'] ?? null
            ];
        }
        
        return [
            'success' => false,
            'error' => 'No se pudo obtener respuesta del chatbot',
            'response' => $response
        ];
    }
    
    /**
     * Realizar petición HTTP a la API
     */
    private function makeRequest($data) {
        $ch = curl_init($this->apiUrl);
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Error de conexión: $error");
        }
        
        $decoded = json_decode($response, true);
        
        if ($httpCode !== 200) {
            throw new Exception("Error de API (HTTP $httpCode): " . ($decoded['error']['message'] ?? 'Error desconocido'));
        }
        
        return $decoded;
    }
}
