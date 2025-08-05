<?php
// 📁 API PARA GUARDAR PDFs DESDE EL FRONTEND
// backend/api/save-pdf.php

require_once '../config.php';

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido', null, 405);
}

// Obtener datos del frontend
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['order']) || !isset($input['pdf'])) {
    jsonResponse(false, 'Datos incompletos', null, 400);
}

$orderData = $input['order'];
$pdfData = $input['pdf'];

try {
    $pdo = getDBConnection();
    
    // 1. VERIFICAR/CREAR PEDIDO EN BASE DE DATOS
    $orderId = getOrCreateOrder($pdo, $orderData);
    
    // 2. GUARDAR INFORMACIÓN DE PIEZAS
    savePiecesData($pdo, $orderId, $pdfData);
    
    // 3. GUARDAR PDF FÍSICO
    $pdfPath = savePDFFile($pdfData);
    
    // 4. GUARDAR REGISTRO DE PDF EN BD
    $pdfId = savePDFRecord($pdo, $orderId, $pdfData, $pdfPath);
    
    logMessage('PDF_SAVED', 'PDF guardado exitosamente', [
        'order_id' => $orderId,
        'pdf_id' => $pdfId,
        'filename' => $pdfData['filename']
    ]);
    
    jsonResponse(true, 'PDF guardado exitosamente', [
        'order_id' => $orderId,
        'pdf_id' => $pdfId,
        'filename' => $pdfData['filename']
    ]);
    
} catch (Exception $e) {
    logMessage('ERROR', 'Error guardando PDF: ' . $e->getMessage(), [
        'order_data' => $orderData,
        'trace' => $e->getTraceAsString()
    ]);
    
    jsonResponse(false, 'Error guardando PDF: ' . $e->getMessage(), null, 500);
}

// 🔍 FUNCIÓN PARA OBTENER O CREAR PEDIDO
function getOrCreateOrder($pdo, $orderData) {
    // Buscar si ya existe
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE shopify_order_id = ?");
    $stmt->execute([$orderData['id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        return $existing['id'];
    }
    
    // Crear nuevo
    $stmt = $pdo->prepare("
        INSERT INTO orders (shopify_order_id, order_number, customer_name, customer_email, order_value, order_status) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $customerName = ($orderData['customer']['first_name'] ?? '') . ' ' . ($orderData['customer']['last_name'] ?? '');
    
    $stmt->execute([
        $orderData['id'],
        $orderData['order_number'],
        trim($customerName),
        $orderData['email'],
        $orderData['total_price'] ?? 0,
        $orderData['financial_status'] ?? 'pending'
    ]);
    
    return $pdo->lastInsertId();
}

// 🧩 FUNCIÓN PARA GUARDAR DATOS DE PIEZAS
function savePiecesData($pdo, $orderId, $pdfData) {
    // Verificar si ya existe
    $stmt = $pdo->prepare("SELECT id FROM order_pieces WHERE order_id = ?");
    $stmt->execute([$orderId]);
    
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
    } else {
        // Crear nuevo
        $stmt = $pdo->prepare("
            INSERT INTO order_pieces (order_id, piece_types, total_pieces, pieces_data, image_resolution) 
            VALUES (?, ?, ?, ?, ?)
        ");
    }
    
    $pieceTypes = count($pdfData['studMap']);
    $totalPieces = array_sum($pdfData['studMap']);
    $piecesDataJson = json_encode($pdfData['studMap']);
    $resolution = $pdfData['resolution'] ?? 'unknown';
    
    if ($stmt->columnCount() === 5) {
        // INSERT
        $stmt->execute([$orderId, $pieceTypes, $totalPieces, $piecesDataJson, $resolution]);
    } else {
        // UPDATE
        $stmt->execute([$pieceTypes, $totalPieces, $piecesDataJson, $resolution, $orderId]);
    }
}

// 📄 FUNCIÓN PARA GUARDAR ARCHIVO PDF
function savePDFFile($pdfData) {
    // Crear directorio si no existe
    if (!file_exists(PDF_STORAGE_PATH)) {
        mkdir(PDF_STORAGE_PATH, 0755, true);
    }
    
    // Generar nombre único
    $filename = $pdfData['filename'];
    $filepath = PDF_STORAGE_PATH . $filename;
    
    // Si existe, añadir timestamp
    if (file_exists($filepath)) {
        $pathinfo = pathinfo($filename);
        $filename = $pathinfo['filename'] . '_' . time() . '.' . $pathinfo['extension'];
        $filepath = PDF_STORAGE_PATH . $filename;
    }
    
    // Decodificar base64 y guardar
    $pdfContent = base64_decode($pdfData['base64']);
    
    if (file_put_contents($filepath, $pdfContent) === false) {
        throw new Exception('Error escribiendo archivo PDF');
    }
    
    return [
        'filename' => $filename,
        'filepath' => $filepath,
        'size' => strlen($pdfContent)
    ];
}

// 💾 FUNCIÓN PARA GUARDAR REGISTRO DE PDF
function savePDFRecord($pdo, $orderId, $pdfData, $pdfPath) {
    $stmt = $pdo->prepare("
        INSERT INTO order_pdfs (order_id, pdf_filename, pdf_path, pdf_size, pdf_type) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $orderId,
        $pdfPath['filename'],
        $pdfPath['filepath'],
        $pdfPath['size'],
        'pieces_list'
    ]);
    
    return $pdo->lastInsertId();
}

?>
