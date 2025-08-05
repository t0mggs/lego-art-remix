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

// ✅ Añadir marca de verificación
$orderData['webhook_verified'] = true;

// Log del pedido válido
error_log("✅ Pedido válido: #" . $orderData['order_number'] . " - €" . $orderData['total_price']);

// 📧 ENVIAR A VISUBLOQ PARA GENERAR PDF
?>
<!DOCTYPE html>
<html>
<head>
    <title>Procesando Pedido Shopify</title>
    <script>
        // Enviar datos a VisuBloq para generar PDF
        window.parent.postMessage({
            type: 'shopify_order_created',
            order: <?php echo json_encode($orderData); ?>
        }, '*');
        
        console.log('📦 Pedido enviado a VisuBloq:', <?php echo json_encode($orderData['order_number']); ?>);
    </script>
</head>
<body>
    <p>Procesando pedido <?php echo htmlspecialchars($orderData['order_number']); ?>...</p>
</body>
</html>

<?php
// Responder a Shopify
http_response_code(200);
error_log("✅ Pedido procesado correctamente");
?>
