<?php
// 🔧 CONFIGURACIÓN PARA VERCEL + PLANETSCALE
// api/config.php

// 📊 CONFIGURACIÓN DE BASE DE DATOS (PlanetScale)
// Estas variables vienen de Vercel Environment Variables
$db_host = $_ENV['DB_HOST'] ?? 'aws.connect.psdb.cloud';
$db_name = $_ENV['DB_NAME'] ?? 'visubloq';
$db_user = $_ENV['DB_USER'] ?? 'tu_usuario';
$db_pass = $_ENV['DB_PASS'] ?? 'tu_password';

// 🛒 CONFIGURACIÓN DE SHOPIFY
define('SHOPIFY_SHOP', 'VisuBloq.myshopify.com');
define('SHOPIFY_ACCESS_TOKEN', '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');
define('SHOPIFY_API_VERSION', '2024-01');
define('SHOPIFY_WEBHOOK_SECRET', $_ENV['SHOPIFY_WEBHOOK_SECRET'] ?? 'tu_webhook_secret');

// 📧 CONFIGURACIÓN DE EMAIL
define('ADMIN_EMAIL', 'admin@visubloq.com');

// 🔐 CONFIGURACIÓN DE SEGURIDAD
$admin_password = $_ENV['ADMIN_PASSWORD'] ?? 'cambiar_en_produccion';
define('ADMIN_PASSWORD_HASH', password_hash($admin_password, PASSWORD_DEFAULT));

// 🌐 CORS para permitir requests desde Shopify
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Manejar preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 📱 FUNCIÓN PARA CONECTAR A PLANETSCALE
function getDBConnection() {
    global $db_host, $db_name, $db_user, $db_pass;
    
    try {
        // PlanetScale requiere SSL y configuración específica
        $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_SSL_CA => true,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ];
        
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        throw new Exception('Error de conexión a la base de datos');
    }
}

// 📝 FUNCIÓN PARA LOGGING SIMPLE (sin base de datos si falla)
function logMessage($type, $message, $data = null) {
    $logEntry = [
        'timestamp' => date('c'),
        'type' => $type,
        'message' => $message,
        'data' => $data
    ];
    
    // Intentar guardar en base de datos
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO system_logs (log_type, message, data) VALUES (?, ?, ?)");
        $stmt->execute([$type, $message, json_encode($data)]);
    } catch (Exception $e) {
        // Si falla, al menos log en error_log
        error_log('VisuBloq Log: ' . json_encode($logEntry));
    }
}

// 🔍 FUNCIÓN PARA VERIFICAR WEBHOOK DE SHOPIFY
function verifyShopifyWebhook($data, $hmac_header) {
    if (empty(SHOPIFY_WEBHOOK_SECRET)) {
        return false; // En desarrollo puede estar vacío
    }
    
    $calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_WEBHOOK_SECRET, true));
    return hash_equals($calculated_hmac, $hmac_header);
}

// 📄 RESPUESTA JSON ESTÁNDAR
function jsonResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 🧪 FUNCIÓN PARA TESTING
function isDevelopmentMode() {
    return ($_ENV['VERCEL_ENV'] ?? 'development') !== 'production';
}

// 📊 FUNCIÓN PARA OBTENER INFO DEL ENTORNO
function getEnvironmentInfo() {
    return [
        'environment' => $_ENV['VERCEL_ENV'] ?? 'local',
        'region' => $_ENV['VERCEL_REGION'] ?? 'unknown',
        'url' => $_ENV['VERCEL_URL'] ?? 'localhost',
        'php_version' => PHP_VERSION,
        'timestamp' => date('c')
    ];
}

?>
