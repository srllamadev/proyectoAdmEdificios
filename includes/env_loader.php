<?php
/**
 * Cargador de variables de entorno desde archivo .env
 */

class EnvLoader {
    private static $loaded = false;
    private static $env = [];
    
    public static function load($filePath = null) {
        if (self::$loaded) {
            return;
        }
        
        if ($filePath === null) {
            $filePath = dirname(__DIR__) . '/.env';
        }
        
        if (!file_exists($filePath)) {
            error_log("Archivo .env no encontrado en: $filePath");
            self::$loaded = true;
            return;
        }
        
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Separar clave y valor
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Limpiar comillas
                $value = trim($value, '"\'');
                
                // Guardar en $_ENV y getenv
                $_ENV[$key] = $value;
                putenv("$key=$value");
                self::$env[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    public static function get($key, $default = null) {
        self::load();
        
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }
    
    public static function all() {
        self::load();
        return self::$env;
    }
}

// Cargar automáticamente al incluir este archivo
EnvLoader::load();
