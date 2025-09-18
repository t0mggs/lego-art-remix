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
    
    // 🛒 VERIFICAR QUE CONTIENE EL PRODUCTO VISUBLOQ
    if (!containsVisuBloqProduct($order_data)) {
        logMessage('INFO', 'Pedido no contiene producto VisuBloq', [
            'order_id' => $order_data['id'],
            'products' => getOrderProductNames($order_data)
        ]);
        jsonResponse(true, 'Pedido recibido pero no es de VisuBloq', null, 200);
    }
    
    // 💾 GUARDAR PEDIDO EN BASE DE DATOS
    $order_id = saveOrderToDatabase($pdo, $order_data);
    
    // 🔗 INTENTAR ASOCIAR CON DISEÑOS EXISTENTES
    associateOrderWithDesigns($pdo, $order_data);
    
    // 🧱 PROCESAR DATOS DE VISUBLOQ SI EXISTEN
    processVisuBloqData($pdo, $order_id, $order_data);
    
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

// 🧱 FUNCIÓN PARA PROCESAR DATOS DE VISUBLOQ
function processVisuBloqData($pdo, $order_id, $order_data) {
    try {
        // Buscar datos de VisuBloq en las líneas de productos o atributos del pedido
        $visubloq_data = extractVisuBloqDataFromOrder($order_data);
        
        if ($visubloq_data) {
            logMessage('VISUBLOQ_DATA_FOUND', 'Datos de VisuBloq encontrados en pedido', [
                'order_id' => $order_id,
                'total_pieces' => $visubloq_data['total_pieces'] ?? 0
            ]);
            
            // Guardar información de piezas
            $stmt = $pdo->prepare("
                INSERT INTO order_pieces (order_id, visubloq_config, piece_colors, total_pieces, dimensions)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                visubloq_config = VALUES(visubloq_config),
                piece_colors = VALUES(piece_colors),
                total_pieces = VALUES(total_pieces),
                dimensions = VALUES(dimensions)
            ");
            
            $stmt->execute([
                $order_id,
                json_encode($visubloq_data['visubloq_config'] ?? []),
                json_encode($visubloq_data['piece_colors'] ?? []),
                $visubloq_data['total_pieces'] ?? 0,
                $visubloq_data['dimensions'] ?? 'unknown'
            ]);
            
            logMessage('VISUBLOQ_DATA_SAVED', 'Datos de VisuBloq guardados exitosamente', [
                'order_id' => $order_id
            ]);
        } else {
            logMessage('VISUBLOQ_DATA_NOT_FOUND', 'No se encontraron datos de VisuBloq en el pedido', [
                'order_id' => $order_id
            ]);
        }
        
    } catch (Exception $e) {
        logMessage('ERROR', 'Error procesando datos de VisuBloq: ' . $e->getMessage(), [
            'order_id' => $order_id,
            'trace' => $e->getTraceAsString()
        ]);
    }
}

// 🔍 FUNCIÓN PARA EXTRAER DATOS DE VISUBLOQ DEL PEDIDO
function extractVisuBloqDataFromOrder($order_data) {
    // Método 1: Buscar en note_attributes del pedido
    if (isset($order_data['note_attributes']) && is_array($order_data['note_attributes'])) {
        foreach ($order_data['note_attributes'] as $attribute) {
            if ($attribute['name'] === 'visubloq_data' || $attribute['name'] === 'design_data') {
                try {
                    $design_data = json_decode($attribute['value'], true);
                    if ($design_data && isset($design_data['pieces_detail'])) {
                        return [
                            'piece_colors' => $design_data['pieces_detail'],
                            'total_pieces' => array_sum($design_data['pieces_detail']),
                            'dimensions' => $design_data['resolution'] ?? 'unknown',
                            'visubloq_config' => $design_data['visubloq_config'] ?? []
                        ];
                    }
                } catch (Exception $e) {
                    logMessage('ERROR', 'Error decodificando datos de VisuBloq: ' . $e->getMessage());
                }
            }
        }
    }
    
    // Método 2: Buscar en properties de los line_items
    if (isset($order_data['line_items']) && is_array($order_data['line_items'])) {
        foreach ($order_data['line_items'] as $item) {
            if (isset($item['properties']) && is_array($item['properties'])) {
                foreach ($item['properties'] as $property) {
                    if ($property['name'] === 'visubloq_data' || $property['name'] === 'design_data') {
                        try {
                            $design_data = json_decode($property['value'], true);
                            if ($design_data && isset($design_data['pieces_detail'])) {
                                return [
                                    'piece_colors' => $design_data['pieces_detail'],
                                    'total_pieces' => array_sum($design_data['pieces_detail']),
                                    'dimensions' => $design_data['resolution'] ?? 'unknown',
                                    'visubloq_config' => $design_data['visubloq_config'] ?? []
                                ];
                            }
                        } catch (Exception $e) {
                            logMessage('ERROR', 'Error decodificando datos de VisuBloq del item: ' . $e->getMessage());
                        }
                    }
                }
            }
        }
    }
    
    // Método 3: Buscar en custom_attributes del customer
    if (isset($order_data['customer']['metafields']) && is_array($order_data['customer']['metafields'])) {
        foreach ($order_data['customer']['metafields'] as $metafield) {
            if ($metafield['key'] === 'visubloq_last_design') {
                try {
                    $design_data = json_decode($metafield['value'], true);
                    if ($design_data && isset($design_data['pieces_detail'])) {
                        return [
                            'piece_colors' => $design_data['pieces_detail'],
                            'total_pieces' => array_sum($design_data['pieces_detail']),
                            'dimensions' => $design_data['resolution'] ?? 'unknown',
                            'visubloq_config' => $design_data['visubloq_config'] ?? []
                        ];
                    }
                } catch (Exception $e) {
                    logMessage('ERROR', 'Error decodificando metafield de VisuBloq: ' . $e->getMessage());
                }
            }
        }
    }
    
    return null;
}

// 🔗 FUNCIÓN PARA ASOCIAR PEDIDO CON DISEÑOS EXISTENTES
function associateOrderWithDesigns($pdo, $order_data) {
    try {
        $customer_email = $order_data['email'];
        $order_id = $order_data['id'];
        
        // Buscar código de seguimiento en las notas del pedido
        $tracking_code = null;
        $order_note = $order_data['note'] ?? '';
        
        if (!empty($order_note) && preg_match('/VB-\d+-[A-Z0-9]+/', $order_note, $matches)) {
            $tracking_code = $matches[0];
            logMessage('INFO', 'Código de seguimiento encontrado en pedido', [
                'order_id' => $order_id,
                'tracking_code' => $tracking_code
            ]);
        }
        
        // Buscar el ID interno del pedido
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE shopify_order_id = ?");
        $stmt->execute([$order_id]);
        $internal_order = $stmt->fetch();
        
        if (!$internal_order) {
            logMessage('WARNING', 'No se encontró pedido interno para asociar diseños', $order_id);
            return;
        }
        
        $internal_order_id = $internal_order['id'];
        
        // Si tenemos código de seguimiento, buscar diseño específico
        if ($tracking_code) {
            $stmt = $pdo->prepare("
                SELECT design_id, session_id, created_at, pieces_data, pdf_blob IS NOT NULL as has_pdf
                FROM design_images 
                WHERE order_id IS NULL 
                AND (session_id LIKE ? OR visubloq_config LIKE ?)
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $stmt->execute(["%{$tracking_code}%", "%{$tracking_code}%"]);
            $specific_design = $stmt->fetch();
            
            if ($specific_design) {
                // Asociar diseño específico
                $update_stmt = $pdo->prepare("
                    UPDATE design_images 
                    SET order_id = ?, status = 'purchased' 
                    WHERE design_id = ?
                ");
                $update_stmt->execute([$internal_order_id, $specific_design['design_id']]);
                
                logMessage('SUCCESS', 'Diseño asociado con código de seguimiento', [
                    'design_id' => $specific_design['design_id'],
                    'order_id' => $order_id,
                    'tracking_code' => $tracking_code
                ]);
                
                return; // Terminamos aquí si encontramos el diseño específico
            }
        }
        
        // Si no hay código de seguimiento o no se encontró, buscar por email y fecha
        $stmt = $pdo->prepare("
            SELECT design_id, session_id, created_at, pieces_data, pdf_blob IS NOT NULL as has_pdf
            FROM design_images 
            WHERE order_id IS NULL 
            AND (session_id LIKE ? OR created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute(["%{$customer_email}%"]);
        $potential_designs = $stmt->fetchAll();
        
        if (empty($potential_designs)) {
            logMessage('INFO', 'No se encontraron diseños para asociar con el pedido', $order_id);
            return;
        }
        
        $associated_count = 0;
        
        foreach ($potential_designs as $design) {
            // Si el diseño tiene PDF y fue creado recientemente, es muy probable que sea del cliente
            if ($design['has_pdf'] || strpos($design['session_id'], $customer_email) !== false) {
                // Asociar diseño con el pedido
                $update_stmt = $pdo->prepare("
                    UPDATE design_images 
                    SET order_id = ?, status = 'purchased' 
                    WHERE design_id = ?
                ");
                $update_stmt->execute([$internal_order_id, $design['design_id']]);
                
                // También actualizar order_pieces si existe
                $pieces_stmt = $pdo->prepare("
                    UPDATE order_pieces 
                    SET design_id = ? 
                    WHERE order_id = ?
                ");
                $pieces_stmt->execute([$design['design_id'], $internal_order_id]);
                
                $associated_count++;
                
                logMessage('SUCCESS', 'Diseño asociado con pedido (fallback)', [
                    'design_id' => $design['design_id'],
                    'order_id' => $order_id,
                    'has_pdf' => $design['has_pdf']
                ]);
                
                // Si ya encontramos un diseño con PDF, probablemente es el correcto
                if ($design['has_pdf']) {
                    break;
                }
            }
        }
        
        if ($associated_count > 0) {
            logMessage('SUCCESS', "Se asociaron {$associated_count} diseños con el pedido {$order_id}");
        } else {
            logMessage('INFO', 'No se pudo asociar ningún diseño con el pedido automáticamente', $order_id);
        }
        
    } catch (Exception $e) {
        logMessage('ERROR', 'Error asociando diseños con pedido: ' . $e->getMessage(), [
            'order_id' => $order_data['id'],
            'trace' => $e->getTraceAsString()
        ]);
    }
}

// 🛒 VERIFICAR QUE EL PEDIDO CONTIENE EL PRODUCTO VISUBLOQ
function containsVisuBloqProduct($order_data) {
    // Productos que consideramos como VisuBloq (puedes ajustar)
    $visubloq_product_names = [
        'visubloq personalizado',
        'visubloq-personalizado', 
        'visubloq',
        'lego art personalizado',
        'diseño personalizado'
    ];
    
    if (!isset($order_data['line_items']) || !is_array($order_data['line_items'])) {
        return false;
    }
    
    foreach ($order_data['line_items'] as $item) {
        $product_name = strtolower($item['title'] ?? '');
        $product_handle = strtolower($item['product_handle'] ?? '');
        
        foreach ($visubloq_product_names as $visubloq_name) {
            if (strpos($product_name, $visubloq_name) !== false || 
                strpos($product_handle, $visubloq_name) !== false) {
                return true;
            }
        }
    }
    
    return false;
}

// 📋 OBTENER NOMBRES DE PRODUCTOS DEL PEDIDO (para logs)
function getOrderProductNames($order_data) {
    $product_names = [];
    
    if (isset($order_data['line_items']) && is_array($order_data['line_items'])) {
        foreach ($order_data['line_items'] as $item) {
            $product_names[] = $item['title'] ?? 'Producto sin nombre';
        }
    }
    
    return $product_names;
}

?>
