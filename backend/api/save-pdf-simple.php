<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://t0mggs.github.io');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

try {
    // Leer datos del request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Datos inválidos');
    }
    
    $pdfLink = $data['pdf_link'] ?? '';
    $designData = $data['design_data'] ?? [];
    $createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
    
    // Extraer filename del link
    $filename = basename(parse_url($pdfLink, PHP_URL_PATH));
    
    // Crear directorio de PDFs si no existe
    $pdfDir = __DIR__ . '/../../pdfs';
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0755, true);
    }
    
    // Generar PDF (esto debe adaptarse a tu sistema actual de generación de PDFs)
    $pdfContent = generatePDFContent($designData);
    
    // Guardar PDF
    $pdfPath = $pdfDir . '/' . $filename;
    file_put_contents($pdfPath, $pdfContent);
    
    // Log simple en archivo de texto (sin base de datos)
    $logFile = __DIR__ . '/../../logs/pdf_links.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = [
        'timestamp' => $createdAt,
        'pdf_link' => $pdfLink,
        'filename' => $filename,
        'status' => 'generated'
    ];
    
    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'message' => 'PDF guardado correctamente',
        'pdf_link' => $pdfLink,
        'filename' => $filename
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error guardando PDF: ' . $e->getMessage()
    ]);
}

function generatePDFContent($designData) {
    // PLACEHOLDER: Aquí debes integrar tu sistema actual de generación de PDFs
    // Por ahora, creamos un PDF simple
    
    // Puedes usar TCPDF, FPDF, o cualquier librería que ya tengas
    // O redirigir a tu función actual de generación de PDFs
    
    return "PDF content placeholder - integrar con tu sistema actual";
}
?>