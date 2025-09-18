<?php
// 🔒 WEBHOOK HANDLER PARA SHOPIFY - PEDIDOS REALES
// Archivo: shopify-webhook-handler.php
// Subir a tu servidor en: https://tu-dominio.com/webhook/shopify-order

header('Content-Type: application/json');

// 🔒 VERIFICAR AUTENTICIDAD DEL WEBHOOK SHOPIFY
function verifyShopifyWebhook($payload, $receivedHmac) {
    $webhookSecret = 'TU_WEBHOOK_SECRET_DE_SHOPIFY'; // ⚠️ CAMBIAR POR TU SECRET REAL
    $calculatedHmac = base64_encode(hash_hmac('sha256', $payload, $webhookSecret, true));
    return hash_equals($calculatedHmac, $receivedHmac);
}

// Obtener datos del webhook
$payload = file_get_contents('php://input');
$receivedHmac = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '';

// Log para debug
error_log("Shopify Webhook recibido: " . date('Y-m-d H:i:s'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Verificar autenticidad
if (!verifyShopifyWebhook($payload, $receivedHmac)) {
    error_log("❌ Webhook no auténtico");
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Procesar pedido
require_once __DIR__ . '/backend/config.php';

$orderData = json_decode($payload, true);

// ✅ VERIFICACIONES DE PEDIDO REAL
if (!$orderData || 
    !isset($orderData['total_price']) || 
    floatval($orderData['total_price']) <= 0 ||
    !isset($orderData['financial_status']) ||
    $orderData['financial_status'] !== 'paid') {
    error_log("❌ Pedido no válido - no es una compra real");
    http_response_code(200); // Responder OK pero no procesar
    echo json_encode(['status' => 'ignored', 'reason' => 'Not a valid paid order']);
    exit;
}

// Guardar pedido en la base de datos
try {
    $pdo = getDatabase();
    // Insertar pedido principal
    $stmt = $pdo->prepare("INSERT INTO orders (shopify_order_id, order_number, customer_name, customer_email, order_value, order_status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $orderData['id'],
        $orderData['order_number'] ?? $orderData['name'],
        $orderData['customer']['first_name'] . ' ' . $orderData['customer']['last_name'],
        $orderData['email'],
        $orderData['total_price'],
        $orderData['financial_status']
    ]);
    $orderId = $pdo->lastInsertId();

    // Recorrer cada producto personalizado (VisuBloq)
    foreach ($orderData['line_items'] as $item) {
        // Extraer propiedades personalizadas
        $piecesUsed = null;
        $config = null;
        $imageUrl = null;
        if (isset($item['properties']) && is_array($item['properties'])) {
            foreach ($item['properties'] as $prop) {
                if (isset($prop['name']) && $prop['name'] === 'pieces_used') {
                    $piecesUsed = $prop['value'];
                }
                if (isset($prop['name']) && $prop['name'] === 'config') {
                    $config = $prop['value'];
                }
                if (isset($prop['name']) && $prop['name'] === 'image_url') {
                    $imageUrl = $prop['value'];
                }
            }
        }
        // Insertar en order_pieces si hay piezas
        if ($piecesUsed) {
            $stmt2 = $pdo->prepare("INSERT INTO order_pieces (order_id, pieces_data, image_resolution, created_at) VALUES (?, ?, ?, NOW())");
            $stmt2->execute([
                $orderId,
                $piecesUsed,
                $config ?? ''
            ]);
        }
    }
    error_log("✅ Pedido y piezas guardados en la base de datos");
} catch (Exception $e) {
    error_log("❌ Error guardando pedido: " . $e->getMessage());
}

// Responder a Shopify
http_response_code(200);
echo json_encode(['status' => 'ok']);
