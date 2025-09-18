<?php
/**
 * VisuBloq Backend Configuration
 * 
 * Copia este archivo como config.php y modifica los valores según tu entorno
 */

// ==============================================
// CONFIGURACIÓN DE BASE DE DATOS
// ==============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'visubloq_db');
define('DB_USER', 'tu_usuario_mysql');
define('DB_PASS', 'tu_password_mysql');
define('DB_CHARSET', 'utf8mb4');

// ==============================================
// CONFIGURACIÓN DE SHOPIFY
// ==============================================

define('SHOPIFY_SHOP', 'tu-tienda.myshopify.com');
define('SHOPIFY_ACCESS_TOKEN', 'shpat_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('SHOPIFY_WEBHOOK_SECRET', 'tu_webhook_secret_seguro');

// ==============================================
// CONFIGURACIÓN DE PATHS Y URLs
// ==============================================

define('BASE_URL', 'https://tu-dominio.com');
define('BACKEND_URL', BASE_URL . '/backend');
define('PDF_STORAGE_PATH', __DIR__ . '/../storage/pdfs/');
define('PDF_URL_BASE', BASE_URL . '/storage/pdfs/');

// ==============================================
// CONFIGURACIÓN DE ADMIN
// ==============================================

define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', password_hash('tu_password_admin', PASSWORD_DEFAULT));

// ==============================================
// CONFIGURACIÓN DE PDF
// ==============================================

define('PDF_FONT_SIZE', 12);
define('PDF_MARGIN', 20);
define('PDF_TITLE_FONT_SIZE', 16);

// ==============================================
// CONFIGURACIÓN DE LOGS
// ==============================================

define('ENABLE_DEBUG_LOGS', true);
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// ==============================================
// CONFIGURACIÓN DE EMAIL (para futuras mejoras)
// ==============================================

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'tu-email@gmail.com');
define('SMTP_PASS', 'tu_password_email');
define('FROM_EMAIL', 'noreply@tu-dominio.com');
define('FROM_NAME', 'VisuBloq Admin');

// ==============================================
// CONFIGURACIÓN DE LÍMITES
// ==============================================

define('MAX_ORDERS_PER_PAGE', 20);
define('MAX_PDF_SIZE_MB', 5);
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos

// ==============================================
// CONFIGURACIÓN DE SEGURIDAD
// ==============================================

define('ALLOWED_ORIGINS', ['https://tu-dominio.com', 'https://tu-tienda.myshopify.com']);
define('RATE_LIMIT_REQUESTS', 100); // Por hora
define('CSRF_TOKEN_LIFETIME', 1800); // 30 minutos

// ==============================================
// TIMEZONE
// ==============================================

date_default_timezone_set('Europe/Madrid');

// ==============================================
// FUNCIONES DE UTILIDAD
// ==============================================

/**
 * Conectar a la base de datos
 */
function getDatabase() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    return $pdo;
}

/**
 * Log de sistema
 */
function logSystem($level, $message, $context = []) {
    if (!ENABLE_DEBUG_LOGS) return;
    
    try {
        $pdo = getDatabase();
        $stmt = $pdo->prepare("INSERT INTO system_logs (level, message, context, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$level, $message, json_encode($context)]);
    } catch (Exception $e) {
        error_log("Failed to log system message: " . $e->getMessage());
    }
}

/**
 * Verificar autenticación de admin
 */
function isAdminAuthenticated() {
    session_start();
    return isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'] === true;
}

/**
 * Verificar webhook de Shopify
 */
function verifyShopifyWebhook($data, $hmac_header) {
    $calculated_hmac = base64_encode(hash_hmac('sha256', $data, SHOPIFY_WEBHOOK_SECRET, true));
    return hash_equals($calculated_hmac, $hmac_header);
}

/**
 * Sanitizar input
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_SESSION['csrf_token_time']) || 
        time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
        
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           isset($_SESSION['csrf_token_time']) &&
           time() - $_SESSION['csrf_token_time'] <= CSRF_TOKEN_LIFETIME &&
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Respuesta JSON
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Verificar origen permitido
 */
function checkOrigin() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (!in_array($origin, ALLOWED_ORIGINS)) {
        http_response_code(403);
        exit('Origin not allowed');
    }
    header("Access-Control-Allow-Origin: $origin");
}

// ==============================================
// INICIALIZACIÓN
// ==============================================

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Configurar reporte de errores según el entorno
if (ENABLE_DEBUG_LOGS) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

?>
