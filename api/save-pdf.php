<?php
// 📁 API PARA GUARDAR PDFs DESDE SHOPIFY LIQUID/JS
// api/save-pdf.php

require_once 'config.php';

// Log de request recibido
logMessage('PDF_REQUEST', 'Request para guardar PDF', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'unknown'
]);

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido', null, 405);
}

// Obtener datos del frontend
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    logMessage('ERROR', 'JSON inválido en save-pdf request');
    jsonResponse(false, 'Datos JSON inválidos', null, 400);
}

// Validar datos requeridos
if (!isset($input['order']) || !isset($input['pdf'])) {
    logMessage('ERROR', 'Datos incompletos en save-pdf', $input);
    jsonResponse(false, 'Datos incompletos. Se requieren order y pdf.', null, 400);
}

$orderData = $input['order'];
$pdfData = $input['pdf'];

// Validar datos del pedido
if (!isset($orderData['id']) || !isset($orderData['order_number'])) {
    logMessage('ERROR', 'Datos de orden inválidos', $orderData);
    jsonResponse(false, 'Datos de orden inválidos', null, 400);
}

// Validar datos del PDF
if (!isset($pdfData['base64']) || !isset($pdfData['filename']) || !isset($pdfData['studMap'])) {
    logMessage('ERROR', 'Datos de PDF inválidos', array_keys($pdfData));
    jsonResponse(false, 'Datos de PDF inválidos', null, 400);
}

try {
    $pdo = getDBConnection();
    
    // 1. VERIFICAR/CREAR PEDIDO EN BASE DE DATOS
    $orderId = getOrCreateOrder($pdo, $orderData);
    
    logMessage('INFO', 'Orden obtenida/creada', ['order_id' => $orderId]);
    
    // 2. GUARDAR INFORMACIÓN DE PIEZAS
    savePiecesData($pdo, $orderId, $pdfData);
    
    // 3. PROCESAR Y GUARDAR PDF
    $pdfInfo = processPDFData($pdfData);
    
    // 4. GUARDAR REGISTRO DE PDF EN BD
    $pdfId = savePDFRecord($pdo, $orderId, $pdfData, $pdfInfo);
    
    logMessage('SUCCESS', 'PDF guardado exitosamente', [
        'order_id' => $orderId,
        'pdf_id' => $pdfId,
        'filename' => $pdfInfo['filename'],
        'size_bytes' => $pdfInfo['size']
    ]);
    
    jsonResponse(true, 'PDF guardado exitosamente', [
        'order_id' => $orderId,
        'pdf_id' => $pdfId,
        'filename' => $pdfInfo['filename'],
        'size' => $pdfInfo['size']
    ]);
    
} catch (Exception $e) {
    logMessage('ERROR', 'Error guardando PDF: ' . $e->getMessage(), [
        'order_id' => $orderData['id'] ?? 'unknown',
        'trace' => $e->getTraceAsString()
    ]);
    
    $errorMessage = isDevelopmentMode() ? $e->getMessage() : 'Error interno del servidor';
    jsonResponse(false, 'Error guardando PDF: ' . $errorMessage, null, 500);
}

// 🔍 FUNCIÓN PARA OBTENER O CREAR PEDIDO
function getOrCreateOrder($pdo, $orderData) {
    // Buscar si ya existe por shopify_order_id
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE shopify_order_id = ?");
    $stmt->execute([$orderData['id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        return $existing['id'];
    }
    
    // Si no existe, crear uno nuevo (puede pasar en testing)
    $stmt = $pdo->prepare("
        INSERT INTO orders (shopify_order_id, order_number, customer_name, customer_email, order_value, order_status) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $customerName = '';
    if (isset($orderData['customer'])) {
        $customerName = trim(
            ($orderData['customer']['first_name'] ?? '') . ' ' . 
            ($orderData['customer']['last_name'] ?? '')
        );
    }
    
    $stmt->execute([
        $orderData['id'],
        $orderData['order_number'] ?? 'TEST-' . time(),
        $customerName ?: 'Cliente de prueba',
        $orderData['email'] ?? 'test@visubloq.com',
        $orderData['total_price'] ?? 0,
        $orderData['financial_status'] ?? 'test'
    ]);
    
    return $pdo->lastInsertId();
}

// 🧩 FUNCIÓN PARA GUARDAR DATOS DE PIEZAS
function savePiecesData($pdo, $orderId, $pdfData) {
    // Verificar si ya existe registro de piezas para este pedido
    $stmt = $pdo->prepare("SELECT id FROM order_pieces WHERE order_id = ?");
    $stmt->execute([$orderId]);
    
    $pieceTypes = count($pdfData['studMap']);
    $totalPieces = array_sum($pdfData['studMap']);
    $piecesDataJson = json_encode($pdfData['studMap']);
    $resolution = $pdfData['resolution'] ?? 'unknown';
    
    if ($stmt->fetch()) {
        // Actualizar existente
        $stmt = $pdo->prepare("
            UPDATE order_pieces SET 
                piece_types = ?, 
                total_pieces = ?, 
                pieces_data = ?, 
                image_resolution = ?
            WHERE order_id = ?
        ");
        $stmt->execute([$pieceTypes, $totalPieces, $piecesDataJson, $resolution, $orderId]);
    } else {
        // Crear nuevo
        $stmt = $pdo->prepare("
            INSERT INTO order_pieces (order_id, piece_types, total_pieces, pieces_data, image_resolution) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $pieceTypes, $totalPieces, $piecesDataJson, $resolution]);
    }
}

// 📄 FUNCIÓN PARA PROCESAR DATOS DEL PDF
function processPDFData($pdfData) {
    // Validar que el base64 es válido
    $pdfContent = base64_decode($pdfData['base64'], true);
    
    if ($pdfContent === false) {
        throw new Exception('Datos PDF base64 inválidos');
    }
    
    // Validar que parece ser un PDF real
    if (substr($pdfContent, 0, 4) !== '%PDF') {
        throw new Exception('El archivo no parece ser un PDF válido');
    }
    
    // Generar nombre único si es necesario
    $filename = $pdfData['filename'];
    $pathInfo = pathinfo($filename);
    
    // Asegurar extensión .pdf
    if (strtolower($pathInfo['extension']) !== 'pdf') {
        $filename = $pathInfo['filename'] . '.pdf';
    }
    
    // En Vercel, no podemos guardar archivos permanentemente en el filesystem
    // Pero podemos devolver la información para que se guarde en otro lugar
    // o mantenerla en base64 en la base de datos
    
    return [
        'filename' => $filename,
        'size' => strlen($pdfContent),
        'content' => $pdfContent, // Para guardar en base64 en BD si es necesario
        'hash' => hash('sha256', $pdfContent) // Para verificar integridad
    ];
}

// 💾 FUNCIÓN PARA GUARDAR REGISTRO DE PDF
function savePDFRecord($pdo, $orderId, $pdfData, $pdfInfo) {
    // En Vercel, guardaremos el PDF como base64 en la base de datos
    // ya que no podemos mantener archivos en el filesystem
    
    $stmt = $pdo->prepare("
        INSERT INTO order_pdfs (order_id, pdf_filename, pdf_path, pdf_size, pdf_type, pdf_content) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    // El "path" será simbólico ya que está en la BD
    $symbolicPath = '/database/pdf/' . $pdfInfo['filename'];
    
    $stmt->execute([
        $orderId,
        $pdfInfo['filename'],
        $symbolicPath,
        $pdfInfo['size'],
        'pieces_list',
        $pdfData['base64'] // Guardar el base64 directamente
    ]);
    
    return $pdo->lastInsertId();
}

?>
