<?php
// 🔧 CONFIGURACIÓN PRINCIPAL DEL SISTEMA
// config.php

// 📊 CONFIGURACIÓN DE BASE DE DATOS
define('DB_HOST', 'localhost');
define('DB_NAME', 'visubloq_db');
define('DB_USER', 'visubloq_user');
define('DB_PASS', 'tu_password_seguro');

// 🛒 CONFIGURACIÓN DE SHOPIFY
define('SHOPIFY_SHOP', 'VisuBloq.myshopify.com');
define('SHOPIFY_ACCESS_TOKEN', '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');
define('SHOPIFY_API_VERSION', '2024-01');
define('SHOPIFY_WEBHOOK_SECRET', 'tu_webhook_secret'); // Lo configurarás en Shopify

// 📧 CONFIGURACIÓN DE EMAIL (para notificaciones admin)
define('ADMIN_EMAIL', 'admin@visubloq.com');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'tu-email@gmail.com');
define('SMTP_PASS', 'tu-app-password');

// 📁 RUTAS DE ARCHIVOS
define('PDF_STORAGE_PATH', __DIR__ . '/../storage/pdfs/');
define('BASE_URL', 'https://tu-dominio.com'); // Cambiar por tu dominio real

// 🔐 CONFIGURACIÓN DE SEGURIDAD
define('ADMIN_PASSWORD', password_hash('tu_password_admin', PASSWORD_DEFAULT));
define('JWT_SECRET', 'tu_jwt_secret_muy_seguro');

// 🛠️ CONFIGURACIÓN DE DESARROLLO
define('DEBUG_MODE', true); // Cambiar a false en producción
define('LOG_LEVEL', 'INFO');

// 🌐 CORS para el frontend
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// 📱 FUNCIÓN PARA CONECTAR A LA BASE DE DATOS
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die('Error de conexión: ' . $e->getMessage());
        } else {
            die('Error de conexión a la base de datos');
        }
    }
}

// 📝 FUNCIÓN PARA LOGGING
function logMessage($type, $message, $data = null) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO system_logs (log_type, message, data) VALUES (?, ?, ?)");
        $stmt->execute([$type, $message, json_encode($data)]);
    } catch (Exception $e) {
        error_log("Error logging: " . $e->getMessage());
    }
}

// 🔍 FUNCIÓN PARA VERIFICAR WEBHOOK DE SHOPIFY
function verifyShopifyWebhook($data, $hmac_header) {
    $calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_WEBHOOK_SECRET, true));
    return hash_equals($calculated_hmac, $hmac_header);
}

// 📄 RESPUESTA JSON ESTÁNDAR
function jsonResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('c')
    ]);
    exit;
}

?>
