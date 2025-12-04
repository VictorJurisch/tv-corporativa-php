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

// Configurações de erro (desativar em produção)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Headers CORS para API
function setCorsHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}
