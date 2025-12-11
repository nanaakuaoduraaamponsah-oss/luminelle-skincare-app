<?php
// Environment Configuration Parser

function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        die("Environment file not found: " . $filePath);
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Set as environment variable
            putenv("$key=$value");
            
            // Also define as constant for easy access
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}

// Automatically load .env file
$envFile = __DIR__ . '/.env';
loadEnv($envFile);
?>