<?php
// 🔗 API PARA GUARDAR DATOS DE DISEÑO VISUBLOQ
// backend/api/save-design-data.php

require_once '../config.php';

// Permitir CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Solo se permite método POST', null, 405);
}

try {
    // Obtener datos del diseño
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        jsonResponse(false, 'Datos inválidos', null, 400);
    }
    
    // Validar datos requeridos
    $requiredFields = ['piece_colors', 'visubloq_config'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            jsonResponse(false, "Campo requerido: $field", null, 400);
        }
    }
    
    $pdo = getDBConnection();
    
    // Generar ID único para este diseño
    $design_id = bin2hex(random_bytes(16));
    $session_id = $input['session_id'] ?? session_create_id();
    $tracking_code = $input['tracking_code'] ?? null;
    
    // Si hay código de seguimiento, incluirlo en session_id
    if ($tracking_code) {
        $session_id = $session_id . '_TRACK_' . $tracking_code;
    }
    
    // Si hay shopify_order_id, asociarlo con el pedido
    $order_id = null;
    if (isset($input['shopify_order_id'])) {
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE shopify_order_id = ?");
        $stmt->execute([$input['shopify_order_id']]);
        $order = $stmt->fetch();
        if ($order) {
            $order_id = $order['id'];
        }
    }
    
    // Calcular total de piezas
    $total_pieces = array_sum($input['piece_colors']);
    
    // Obtener dimensiones del config
    $dimensions = $input['visubloq_config']['resolution'] ?? 'unknown';
    
    // Manejar PDF si se proporciona
    $pdf_blob = null;
    if (isset($input['pdf_data'])) {
        $pdf_blob = base64_decode($input['pdf_data']);
    }
    
    // Añadir tracking code a la configuración
    $visubloq_config = $input['visubloq_config'];
    if ($tracking_code) {
        $visubloq_config['tracking_code'] = $tracking_code;
    }
    $visubloq_config['timestamp'] = date('Y-m-d H:i:s');
    
    // Crear nuevo registro de diseño
    $stmt = $pdo->prepare("
        INSERT INTO design_images (
            design_id, session_id, order_id, width, height, 
            pieces_data, pdf_blob, visubloq_config, created_at, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'generated')
    ");
    
    $width = $input['visubloq_config']['width'] ?? 0;
    $height = $input['visubloq_config']['height'] ?? 0;
    
    $stmt->execute([
        $design_id,
        $session_id,
        $order_id,
        (int)$width,
        (int)$height,
        json_encode($input['piece_colors']),
        $pdf_blob,
        json_encode($visubloq_config)
    ]);
    
    // Si hay un pedido asociado, también actualizar la tabla order_pieces
    if ($order_id) {
        // Verificar si ya existe información de piezas para este pedido
        $stmt = $pdo->prepare("SELECT id FROM order_pieces WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Actualizar existente
            $stmt = $pdo->prepare("
                UPDATE order_pieces SET 
                    visubloq_config = ?, 
                    piece_colors = ?, 
                    total_pieces = ?,
                    dimensions = ?,
                    design_id = ?
                WHERE order_id = ?
            ");
            
            $stmt->execute([
                json_encode($input['visubloq_config']),
                json_encode($input['piece_colors']),
                $total_pieces,
                $dimensions,
                $design_id,
                $order_id
            ]);
            
        } else {
            // Crear nuevo registro
            $stmt = $pdo->prepare("
                INSERT INTO order_pieces (order_id, visubloq_config, piece_colors, total_pieces, dimensions, design_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $order_id,
                json_encode($input['visubloq_config']),
                json_encode($input['piece_colors']),
                $total_pieces,
                $dimensions,
                $design_id
            ]);
        }
    }
    
    // Respuesta exitosa
    jsonResponse(true, 'Datos de diseño guardados exitosamente', [
        'design_id' => $design_id,
        'order_id' => $order_id,
        'total_pieces' => $total_pieces,
        'piece_types' => count($input['piece_colors']),
        'has_pdf' => !is_null($pdf_blob)
    ]);
    
} catch (Exception $e) {
    logMessage('ERROR', 'Error guardando datos de diseño: ' . $e->getMessage(), [
        'input' => $input ?? null,
        'trace' => $e->getTraceAsString()
    ]);
    
    jsonResponse(false, 'Error interno del servidor', null, 500);
}

?>
