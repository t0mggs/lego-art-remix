<?php
// 🔗 WEBHOOK RECEIVER PARA SHOPIFY
// backend/api/shopify-webhook.php

require_once '../config.php';

// Registrar que se recibió una solicitud
logMessage('WEBHOOK', 'Webhook recibido', $_SERVER);

// Verificar que es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logMessage('ERROR', 'Método no permitido: ' . $_SERVER['REQUEST_METHOD']);
    jsonResponse(false, 'Método no permitido', null, 405);
}

// Obtener datos del webhook
$webhook_payload = file_get_contents('php://input');
$webhook_signature = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';

// Verificar autenticidad del webhook (IMPORTANTE para seguridad)
if (!verifyShopifyWebhook($webhook_payload, $webhook_signature)) {
    logMessage('ERROR', 'Webhook no auténtico', [
        'signature' => $webhook_signature,
        'payload_length' => strlen($webhook_payload)
    ]);
    jsonResponse(false, 'Webhook no auténtico', null, 401);
}

// Decodificar datos del pedido
$order_data = json_decode($webhook_payload, true);

if (!$order_data) {
    logMessage('ERROR', 'Datos de pedido inválidos', $webhook_payload);
    jsonResponse(false, 'Datos inválidos', null, 400);
}

logMessage('INFO', 'Pedido recibido de Shopify', [
    'order_id' => $order_data['id'],
    'order_number' => $order_data['order_number'],
    'email' => $order_data['email']
]);

try {
    $pdo = getDBConnection();
    
    // 🔍 VERIFICAR SI ES UN PEDIDO VÁLIDO (con pago confirmado)
    if (!isValidPaidOrder($order_data)) {
        logMessage('INFO', 'Pedido no válido o no pagado', $order_data['id']);
        jsonResponse(true, 'Pedido recibido pero no procesado (no pagado)', null, 200);
    }
    
    // 💾 GUARDAR PEDIDO EN BASE DE DATOS
    $order_id = saveOrderToDatabase($pdo, $order_data);
    
    // 📧 NOTIFICAR AL ADMIN (opcional)
    notifyAdminNewOrder($order_data);
    
    // ✅ RESPUESTA EXITOSA
    logMessage('SUCCESS', 'Pedido procesado exitosamente', [
        'order_id' => $order_id,
        'shopify_order_id' => $order_data['id']
    ]);
    
    jsonResponse(true, 'Pedido procesado exitosamente', [
        'order_id' => $order_id,
        'shopify_order_id' => $order_data['id']
    ]);
    
} catch (Exception $e) {
    logMessage('ERROR', 'Error procesando pedido: ' . $e->getMessage(), [
        'order_data' => $order_data,
        'trace' => $e->getTraceAsString()
    ]);
    
    jsonResponse(false, 'Error interno del servidor', null, 500);
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
    
    // Verificar estado de pago
    if (empty($order_data['financial_status']) || $order_data['financial_status'] !== 'paid') {
        return false;
    }
    
    return true;
}

// 💾 FUNCIÓN PARA GUARDAR PEDIDO
function saveOrderToDatabase($pdo, $order_data) {
    // Verificar si ya existe
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE shopify_order_id = ?");
    $stmt->execute([$order_data['id']]);
    
    if ($stmt->fetch()) {
        // Ya existe, actualizar
        $stmt = $pdo->prepare("
            UPDATE orders SET 
                order_number = ?, 
                customer_name = ?, 
                customer_email = ?, 
                order_value = ?,
                order_status = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE shopify_order_id = ?
        ");
        
        $stmt->execute([
            $order_data['order_number'],
            ($order_data['customer']['first_name'] ?? '') . ' ' . ($order_data['customer']['last_name'] ?? ''),
            $order_data['email'],
            $order_data['total_price'],
            $order_data['financial_status'],
            $order_data['id']
        ]);
        
        return $order_data['id'];
    } else {
        // Nuevo pedido
        $stmt = $pdo->prepare("
            INSERT INTO orders (shopify_order_id, order_number, customer_name, customer_email, order_value, order_status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $order_data['id'],
            $order_data['order_number'],
            ($order_data['customer']['first_name'] ?? '') . ' ' . ($order_data['customer']['last_name'] ?? ''),
            $order_data['email'],
            $order_data['total_price'],
            $order_data['financial_status']
        ]);
        
        return $pdo->lastInsertId();
    }
}

// 📧 FUNCIÓN PARA NOTIFICAR ADMIN
function notifyAdminNewOrder($order_data) {
    // Enviar email simple al admin
    $subject = "🛒 Nuevo pedido pagado #{$order_data['order_number']}";
    $message = "
    Nuevo pedido confirmado:
    
    Pedido: {$order_data['order_number']}
    Cliente: {$order_data['customer']['first_name']} {$order_data['customer']['last_name']}
    Email: {$order_data['email']}
    Valor: €{$order_data['total_price']}
    
    Accede al panel admin para ver más detalles y generar el PDF.
    ";
    
    mail(ADMIN_EMAIL, $subject, $message);
}

?>
