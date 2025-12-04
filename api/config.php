<?php
/**
 * Configurações do banco de dados e aplicação
 * TV Corporativa PHP
 */

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'u711845530_tv_asti');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb3');

// Configurações da Aplicação
define('APP_NAME', 'TV Corporativa');
define('APP_VERSION', '1.0.0');
define('APP_TIMEZONE', 'America/Sao_Paulo');

// Configurações de Upload
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Configuração de timezone
date_default_timezone_set(APP_TIMEZONE);

// Ambiente (production ou development)
define('APP_ENV', getenv('APP_ENV') ?: 'production');

// Configurações de erro baseadas no ambiente
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Domínios permitidos para CORS (configure conforme necessário)
define('CORS_ALLOWED_ORIGINS', getenv('CORS_ORIGINS') ?: '*');

// Headers CORS para API
function setCorsHeaders() {
    $allowedOrigins = CORS_ALLOWED_ORIGINS;
    
    // Se configurado para aceitar origens específicas
    if ($allowedOrigins !== '*') {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowed = array_map('trim', explode(',', $allowedOrigins));
        if (in_array($origin, $allowed)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
    } else {
        header('Access-Control-Allow-Origin: *');
    }
    
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}
