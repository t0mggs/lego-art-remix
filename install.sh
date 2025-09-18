#!/bin/bash

# ============================================
# INSTALADOR AUTOMÁTICO DE VISUBLOQ ADMIN
# ============================================

echo "🧱 VisuBloq Admin Panel - Instalador Automático"
echo "================================================"
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para mostrar mensajes
show_message() {
    echo -e "${GREEN}✓${NC} $1"
}

show_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

show_error() {
    echo -e "${RED}✗${NC} $1"
}

show_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Verificar si estamos en el directorio correcto
if [ ! -f "index.html" ] || [ ! -d "backend" ]; then
    show_error "Por favor ejecuta este script desde el directorio raíz de VisuBloq"
    exit 1
fi

show_info "Iniciando instalación del sistema VisuBloq Admin..."
echo ""

# ============================================
# 1. VERIFICAR REQUISITOS
# ============================================

echo "1. Verificando requisitos del sistema..."

# Verificar PHP
if ! command -v php &> /dev/null; then
    show_error "PHP no está instalado. Por favor instala PHP 7.4 o superior."
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;" 2>/dev/null)
show_message "PHP $PHP_VERSION detectado"

# Verificar MySQL
if ! command -v mysql &> /dev/null; then
    show_warning "MySQL no detectado. Asegúrate de tener MySQL/MariaDB instalado."
fi

# Verificar extensiones PHP necesarias
php -m | grep -q "pdo" || { show_error "Extensión PHP PDO no encontrada"; exit 1; }
php -m | grep -q "pdo_mysql" || { show_error "Extensión PHP PDO MySQL no encontrada"; exit 1; }
php -m | grep -q "json" || { show_error "Extensión PHP JSON no encontrada"; exit 1; }
php -m | grep -q "curl" || { show_error "Extensión PHP cURL no encontrada"; exit 1; }

show_message "Todas las extensiones PHP necesarias están disponibles"
echo ""

# ============================================
# 2. CONFIGURAR DIRECTORIOS
# ============================================

echo "2. Configurando estructura de directorios..."

# Crear directorio de storage
mkdir -p storage/pdfs
mkdir -p storage/logs
mkdir -p storage/backups

# Configurar permisos
chmod 755 storage
chmod 755 storage/pdfs
chmod 755 storage/logs
chmod 755 storage/backups

# Crear archivos .htaccess para seguridad
cat > storage/.htaccess << 'EOF'
<Files "*">
    Order Deny,Allow
    Deny from all
</Files>
EOF

cat > backend/.htaccess << 'EOF'
# Permitir solo archivos PHP específicos
<Files "*.php">
    Order Allow,Deny
    Allow from all
</Files>

# Denegar acceso a archivos de configuración
<Files "config.php">
    Order Deny,Allow
    Deny from all
</Files>

<Files "*.sql">
    Order Deny,Allow
    Deny from all
</Files>
EOF

show_message "Directorios y permisos configurados"
echo ""

# ============================================
# 3. CONFIGURACIÓN DE BASE DE DATOS
# ============================================

echo "3. Configuración de base de datos..."

read -p "Nombre de la base de datos [visubloq_db]: " DB_NAME
DB_NAME=${DB_NAME:-visubloq_db}

read -p "Usuario de MySQL [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -s -p "Contraseña de MySQL: " DB_PASS
echo ""

read -p "Host de MySQL [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

# Verificar conexión a la base de datos
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1;" 2>/dev/null
if [ $? -ne 0 ]; then
    show_error "No se pudo conectar a MySQL. Verifica las credenciales."
    exit 1
fi

# Crear base de datos si no existe
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

# Importar estructura de base de datos
if [ -f "backend/database_structure.sql" ]; then
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < backend/database_structure.sql
    show_message "Estructura de base de datos importada"
else
    show_error "Archivo database_structure.sql no encontrado"
    exit 1
fi

echo ""

# ============================================
# 4. CONFIGURACIÓN DE SHOPIFY
# ============================================

echo "4. Configuración de Shopify..."

read -p "Tu tienda Shopify (ej: mi-tienda.myshopify.com): " SHOPIFY_SHOP
read -p "Access Token de Shopify: " SHOPIFY_ACCESS_TOKEN
read -p "Webhook Secret de Shopify: " SHOPIFY_WEBHOOK_SECRET

echo ""

# ============================================
# 5. CONFIGURACIÓN DEL DOMINIO
# ============================================

echo "5. Configuración del dominio..."

read -p "URL base de tu sitio (ej: https://mi-sitio.com): " BASE_URL

echo ""

# ============================================
# 6. CONFIGURACIÓN DE ADMIN
# ============================================

echo "6. Configuración de administrador..."

read -p "Usuario administrador [admin]: " ADMIN_USERNAME
ADMIN_USERNAME=${ADMIN_USERNAME:-admin}

read -s -p "Contraseña del administrador: " ADMIN_PASSWORD
echo ""

# Generar hash de la contraseña
ADMIN_PASSWORD_HASH=$(php -r "echo password_hash('$ADMIN_PASSWORD', PASSWORD_DEFAULT);")

echo ""

# ============================================
# 7. CREAR ARCHIVO DE CONFIGURACIÓN
# ============================================

echo "7. Generando archivo de configuración..."

# Crear config.php basado en el template
cat > backend/config.php << EOF
<?php
/**
 * VisuBloq Backend Configuration
 * Generado automáticamente por el instalador
 */

// Base de datos
define('DB_HOST', '$DB_HOST');
define('DB_NAME', '$DB_NAME');
define('DB_USER', '$DB_USER');
define('DB_PASS', '$DB_PASS');
define('DB_CHARSET', 'utf8mb4');

// Shopify
define('SHOPIFY_SHOP', '$SHOPIFY_SHOP');
define('SHOPIFY_ACCESS_TOKEN', '$SHOPIFY_ACCESS_TOKEN');
define('SHOPIFY_WEBHOOK_SECRET', '$SHOPIFY_WEBHOOK_SECRET');

// URLs y paths
define('BASE_URL', '$BASE_URL');
define('BACKEND_URL', BASE_URL . '/backend');
define('PDF_STORAGE_PATH', __DIR__ . '/../storage/pdfs/');
define('PDF_URL_BASE', BASE_URL . '/storage/pdfs/');

// Admin
define('ADMIN_USERNAME', '$ADMIN_USERNAME');
define('ADMIN_PASSWORD', '$ADMIN_PASSWORD_HASH');

// Configuración general
define('ENABLE_DEBUG_LOGS', true);
define('LOG_LEVEL', 'INFO');
define('MAX_ORDERS_PER_PAGE', 20);
define('SESSION_TIMEOUT', 3600);

// Timezone
date_default_timezone_set('Europe/Madrid');

// Funciones de utilidad
function getDatabase() {
    static \$pdo = null;
    
    if (\$pdo === null) {
        try {
            \$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException \$e) {
            error_log("Database connection failed: " . \$e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    return \$pdo;
}

function logSystem(\$level, \$message, \$context = []) {
    if (!ENABLE_DEBUG_LOGS) return;
    
    try {
        \$pdo = getDatabase();
        \$stmt = \$pdo->prepare("INSERT INTO system_logs (level, message, context, created_at) VALUES (?, ?, ?, NOW())");
        \$stmt->execute([\$level, \$message, json_encode(\$context)]);
    } catch (Exception \$e) {
        error_log("Failed to log system message: " . \$e->getMessage());
    }
}

function isAdminAuthenticated() {
    session_start();
    return isset(\$_SESSION['admin_authenticated']) && \$_SESSION['admin_authenticated'] === true;
}

function verifyShopifyWebhook(\$data, \$hmac_header) {
    \$calculated_hmac = base64_encode(hash_hmac('sha256', \$data, SHOPIFY_WEBHOOK_SECRET, true));
    return hash_equals(\$calculated_hmac, \$hmac_header);
}

function jsonResponse(\$data, \$status = 200) {
    http_response_code(\$status);
    header('Content-Type: application/json');
    echo json_encode(\$data);
    exit;
}

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

?>
EOF

chmod 600 backend/config.php
show_message "Archivo de configuración creado y protegido"
echo ""

# ============================================
# 8. CREAR USUARIO ADMINISTRADOR
# ============================================

echo "8. Creando usuario administrador..."

mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" << EOF
INSERT INTO admin_users (username, password_hash, email, full_name, created_at) 
VALUES ('$ADMIN_USERNAME', '$ADMIN_PASSWORD_HASH', 'admin@localhost', 'Administrador', NOW())
ON DUPLICATE KEY UPDATE password_hash = '$ADMIN_PASSWORD_HASH';
EOF

show_message "Usuario administrador creado/actualizado"
echo ""

# ============================================
# 9. VERIFICAR INSTALACIÓN
# ============================================

echo "9. Verificando instalación..."

# Verificar conexión a base de datos
php -r "
require 'backend/config.php';
try {
    \$pdo = getDatabase();
    echo 'Conexión a base de datos: OK\n';
} catch (Exception \$e) {
    echo 'Error de conexión: ' . \$e->getMessage() . '\n';
    exit(1);
}
"

show_message "Verificación de base de datos completada"
echo ""

# ============================================
# 10. RESUMEN FINAL
# ============================================

echo ""
echo "🎉 ¡INSTALACIÓN COMPLETADA!"
echo "=========================="
echo ""
show_info "Configuración completada:"
echo "  • Base de datos: $DB_NAME en $DB_HOST"
echo "  • Tienda Shopify: $SHOPIFY_SHOP"
echo "  • URL del sitio: $BASE_URL"
echo "  • Usuario admin: $ADMIN_USERNAME"
echo ""
show_info "URLs importantes:"
echo "  • Panel de admin: $BASE_URL/backend/admin/dashboard.php"
echo "  • Webhook URL: $BASE_URL/backend/api/shopify-webhook.php"
echo ""
show_warning "Próximos pasos:"
echo "  1. Configura el webhook en Shopify Admin:"
echo "     → Settings > Notifications > Webhooks"
echo "     → Crear webhook para 'Order payment'"
echo "     → URL: $BASE_URL/backend/api/shopify-webhook.php"
echo "     → Secret: $SHOPIFY_WEBHOOK_SECRET"
echo ""
echo "  2. Accede al panel de administrador:"
echo "     → $BASE_URL/backend/admin/dashboard.php"
echo "     → Usuario: $ADMIN_USERNAME"
echo "     → Contraseña: [la que configuraste]"
echo ""
echo "  3. Realiza una compra de prueba para verificar que todo funciona"
echo ""
show_message "¡Sistema VisuBloq Admin listo para usar!"
echo ""
