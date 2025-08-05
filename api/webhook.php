<?php
// 🔗 WEBHOOK RECEIVER PARA VERCEL
// api/webhook.php

require_once 'config.php';

// Log de que se recibió el webhook
logMessage('WEBHOOK_RECEIVED', 'Nuevo webhook de Shopify', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'ip' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown'
]);

// Verificar que es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logMessage('ERROR', 'Método no permitido: ' . $_SERVER['REQUEST_METHOD']);
    jsonResponse(false, 'Método no permitido', null, 405);
}

// Obtener datos del webhook
$webhook_payload = file_get_contents('php://input');
$webhook_signature = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';

// En desarrollo, permitir webhooks sin firma (para testing)
if (!isDevelopmentMode()) {
    if (!verifyShopifyWebhook($webhook_payload, $webhook_signature)) {
        logMessage('ERROR', 'Webhook no auténtico', [
            'signature_provided' => !empty($webhook_signature),
            'payload_length' => strlen($webhook_payload)
        ]);
        jsonResponse(false, 'Webhook no auténtico', null, 401);
    }
}

// Decodificar datos del pedido
$order_data = json_decode($webhook_payload, true);

if (!$order_data) {
    logMessage('ERROR', 'JSON inválido en webhook', [
        'payload' => substr($webhook_payload, 0, 500) // Solo los primeros 500 chars
    ]);
    jsonResponse(false, 'Datos JSON inválidos', null, 400);
}

// Log del pedido recibido
logMessage('ORDER_RECEIVED', 'Pedido recibido de Shopify', [
    'order_id' => $order_data['id'] ?? 'unknown',
    'order_number' => $order_data['order_number'] ?? 'unknown',
    'email' => $order_data['email'] ?? 'unknown',
    'financial_status' => $order_data['financial_status'] ?? 'unknown'
]);

try {
    // 🔍 VERIFICAR SI ES UN PEDIDO VÁLIDO
    if (!isValidPaidOrder($order_data)) {
        logMessage('INFO', 'Pedido no válido para procesamiento', [
            'order_id' => $order_data['id'],
            'reason' => 'no_paid_or_invalid'
        ]);
        jsonResponse(true, 'Webhook recibido - Pedido no procesado (no pagado)', null, 200);
    }
    
    // 💾 GUARDAR PEDIDO EN BASE DE DATOS
    $internal_order_id = saveOrderToDatabase($order_data);
    
    // 📧 NOTIFICAR AL ADMIN (opcional - puede fallar sin afectar el proceso)
    try {
        notifyAdminNewOrder($order_data);
    } catch (Exception $e) {
        logMessage('WARNING', 'Error enviando notificación admin: ' . $e->getMessage());
        // No falla el proceso principal
    }
    
    // ✅ RESPUESTA EXITOSA
    logMessage('SUCCESS', 'Pedido procesado exitosamente', [
        'internal_order_id' => $internal_order_id,
        'shopify_order_id' => $order_data['id']
    ]);
    
    jsonResponse(true, 'Pedido procesado exitosamente', [
        'internal_order_id' => $internal_order_id,
        'shopify_order_id' => $order_data['id']
    ]);
    
} catch (Exception $e) {
    logMessage('ERROR', 'Error procesando pedido: ' . $e->getMessage(), [
        'order_id' => $order_data['id'] ?? 'unknown',
        'trace' => $e->getTraceAsString()
    ]);
    
    // En producción, no revelar detalles del error
    $errorMessage = isDevelopmentMode() ? $e->getMessage() : 'Error interno del servidor';
    jsonResponse(false, $errorMessage, null, 500);
}

// 🔍 FUNCIÓN PARA VALIDAR PEDIDOS PAGADOS
function isValidPaidOrder($order_data) {
    // Verificar que tiene número de pedido
    if (empty($order_data['order_number'])) {
        return false;
    }
    
    // Verificar que tiene cliente válido
    if (empty($order_data['customer']) || empty($order_data['email'])) {
        return false;
    }
    
    // Verificar que tiene valor monetario
    if (empty($order_data['total_price']) || floatval($order_data['total_price']) <= 0) {
        return false;
    }
    
    // Verificar estado de pago - SOLO procesar pedidos pagados
    if (empty($order_data['financial_status']) || $order_data['financial_status'] !== 'paid') {
        return false;
    }
    
    return true;
}

// 💾 FUNCIÓN PARA GUARDAR PEDIDO
function saveOrderToDatabase($order_data) {
    $pdo = getDBConnection();
    
    // Verificar si ya existe
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE shopify_order_id = ?");
    $stmt->execute([$order_data['id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Actualizar existente
        $stmt = $pdo->prepare("
            UPDATE orders SET 
                order_number = ?, 
                customer_name = ?, 
                customer_email = ?, 
                order_value = ?,
                order_status = ?,
                updated_at = NOW()
            WHERE shopify_order_id = ?
        ");
        
        $stmt->execute([
            $order_data['order_number'],
            getCustomerName($order_data),
            $order_data['email'],
            $order_data['total_price'],
            $order_data['financial_status'],
            $order_data['id']
        ]);
        
        return $existing['id'];
    } else {
        // Crear nuevo
        $stmt = $pdo->prepare("
            INSERT INTO orders (shopify_order_id, order_number, customer_name, customer_email, order_value, order_status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $order_data['id'],
            $order_data['order_number'],
            getCustomerName($order_data),
            $order_data['email'],
            $order_data['total_price'],
            $order_data['financial_status']
        ]);
        
        return $pdo->lastInsertId();
    }
}

// 👤 FUNCIÓN AUXILIAR PARA OBTENER NOMBRE DEL CLIENTE
function getCustomerName($order_data) {
    $firstName = $order_data['customer']['first_name'] ?? '';
    $lastName = $order_data['customer']['last_name'] ?? '';
    
    return trim($firstName . ' ' . $lastName) ?: 'Cliente sin nombre';
}

// 📧 FUNCIÓN PARA NOTIFICAR ADMIN (SIMPLE)
function notifyAdminNewOrder($order_data) {
    if (empty(ADMIN_EMAIL)) {
        return; // No hay email configurado
    }
    
    $subject = "🛒 Nuevo pedido pagado: {$order_data['order_number']}";
    $message = "
Nuevo pedido confirmado en VisuBloq:

Pedido: {$order_data['order_number']}
Cliente: " . getCustomerName($order_data) . "
Email: {$order_data['email']}
Valor: €{$order_data['total_price']}
Estado: {$order_data['financial_status']}

Accede al panel admin para generar el PDF de instrucciones.
    ";
    
    // Headers básicos para email
    $headers = [
        'From: VisuBloq <noreply@visubloq.com>',
        'Reply-To: ' . ADMIN_EMAIL,
        'Content-Type: text/plain; charset=utf-8'
    ];
    
    mail(ADMIN_EMAIL, $subject, $message, implode("\r\n", $headers));
}

?>
