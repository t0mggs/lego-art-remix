<?php
// 📄 API PARA GENERAR PDF DE INSTRUCCIONES DESDE EL BACKEND
// backend/api/generate-pdf.php

require_once '../config.php';

// Permitir CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'generate':
        generatePDFFromOrder();
        break;
    case 'download':
        downloadPDF();
        break;
    default:
        jsonResponse(false, 'Acción no válida', null, 400);
}

// 📄 GENERAR PDF DESDE DATOS DE PEDIDO
function generatePDFFromOrder() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;
        
        if (!$orderId) {
            jsonResponse(false, 'ID de pedido requerido', null, 400);
        }
        
        $pdo = getDBConnection();
        
        // Obtener datos del pedido
        $stmt = $pdo->prepare("
            SELECT o.*, op.* 
            FROM orders o 
            LEFT JOIN order_pieces op ON o.id = op.order_id 
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $orderData = $stmt->fetch();
        
        if (!$orderData) {
            jsonResponse(false, 'Pedido no encontrado', null, 404);
        }
        
        if (!$orderData['piece_colors']) {
            jsonResponse(false, 'No hay datos de piezas para este pedido', null, 404);
        }
        
        // Generar PDF
        $pdfResult = generatePiecesListPDF($orderData);
        
        if ($pdfResult['success']) {
            // Guardar información del PDF en base de datos
            $stmt = $pdo->prepare("
                INSERT INTO order_pdfs (order_id, pdf_filename, pdf_path, pdf_size, pdf_type) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $orderId,
                $pdfResult['filename'],
                $pdfResult['path'],
                $pdfResult['size'],
                'pieces_list'
            ]);
            
            logMessage('PDF_GENERATED', 'PDF de piezas generado', [
                'order_id' => $orderId,
                'filename' => $pdfResult['filename']
            ]);
            
            jsonResponse(true, 'PDF generado exitosamente', $pdfResult);
        } else {
            jsonResponse(false, 'Error generando PDF: ' . $pdfResult['error'], null, 500);
        }
        
    } catch (Exception $e) {
        logMessage('ERROR', 'Error generando PDF: ' . $e->getMessage());
        jsonResponse(false, 'Error interno del servidor', null, 500);
    }
}

// 📄 GENERAR PDF CON LISTA DE PIEZAS
function generatePiecesListPDF($orderData) {
    try {
        // Crear directorio si no existe
        if (!file_exists(PDF_STORAGE_PATH)) {
            mkdir(PDF_STORAGE_PATH, 0755, true);
        }
        
        $pieceColors = json_decode($orderData['piece_colors'], true);
        $visubloqConfig = json_decode($orderData['visubloq_config'], true);
        
        $filename = "visubloq_pieces_list_order_{$orderData['order_number']}_" . date('Y-m-d_H-i-s') . ".pdf";
        $filepath = PDF_STORAGE_PATH . $filename;
        
        // Crear contenido HTML para el PDF
        $html = generatePiecesListHTML($orderData, $pieceColors, $visubloqConfig);
        
        // Aquí puedes usar librerías como TCPDF, mPDF o wkhtmltopdf
        // Por simplicidad, crearemos un archivo HTML que se pueda convertir
        $htmlFile = PDF_STORAGE_PATH . str_replace('.pdf', '.html', $filename);
        file_put_contents($htmlFile, $html);
        
        // Para una implementación real, aquí convertirías HTML a PDF
        // Por ahora simulamos creando un archivo de texto
        $pdfContent = generateSimplePDFContent($orderData, $pieceColors, $visubloqConfig);
        file_put_contents($filepath, $pdfContent);
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $filepath,
            'size' => strlen($pdfContent),
            'download_url' => BASE_URL . '/backend/api/generate-pdf.php?action=download&file=' . urlencode($filename)
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// 📄 GENERAR CONTENIDO HTML PARA PDF
function generatePiecesListHTML($orderData, $pieceColors, $visubloqConfig) {
    $totalPieces = array_sum($pieceColors);
    $pieceTypes = count($pieceColors);
    
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>VisuBloq - Lista de Piezas</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; border-bottom: 2px solid #ff6b35; padding-bottom: 20px; }
            .logo { font-size: 24px; font-weight: bold; color: #ff6b35; }
            .order-info { margin: 20px 0; }
            .pieces-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .pieces-table th, .pieces-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .pieces-table th { background-color: #f2f2f2; }
            .color-sample { width: 30px; height: 20px; border: 1px solid #000; display: inline-block; }
            .summary { background-color: #f9f9f9; padding: 15px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <div class='logo'>🧱 VisuBloq - Lista de Piezas LEGO</div>
            <p>Diseño personalizado</p>
        </div>
        
        <div class='order-info'>
            <h3>Información del Pedido</h3>
            <p><strong>Número de Pedido:</strong> {$orderData['order_number']}</p>
            <p><strong>Cliente:</strong> {$orderData['customer_name']}</p>
            <p><strong>Email:</strong> {$orderData['customer_email']}</p>
            <p><strong>Dimensiones:</strong> {$orderData['dimensions']}</p>
            <p><strong>Fecha:</strong> " . date('d/m/Y H:i', strtotime($orderData['created_at'])) . "</p>
        </div>
        
        <div class='summary'>
            <h3>Resumen del Diseño</h3>
            <p><strong>Total de Piezas:</strong> {$totalPieces}</p>
            <p><strong>Tipos de Colores:</strong> {$pieceTypes}</p>
        </div>
        
        <h3>Lista Detallada de Piezas</h3>
        <table class='pieces-table'>
            <thead>
                <tr>
                    <th>Color</th>
                    <th>Código Hex</th>
                    <th>Cantidad</th>
                    <th>Porcentaje</th>
                </tr>
            </thead>
            <tbody>";
    
    // Ordenar colores por cantidad (descendente)
    arsort($pieceColors);
    
    foreach ($pieceColors as $hexColor => $quantity) {
        $percentage = round(($quantity / $totalPieces) * 100, 1);
        $html .= "
                <tr>
                    <td><div class='color-sample' style='background-color: {$hexColor};'></div></td>
                    <td>{$hexColor}</td>
                    <td>{$quantity}</td>
                    <td>{$percentage}%</td>
                </tr>";
    }
    
    $html .= "
            </tbody>
        </table>
        
        <div style='margin-top: 30px; text-align: center; color: #666;'>
            <p>Generado el " . date('d/m/Y H:i:s') . "</p>
            <p>VisuBloq - Construye tu obra maestra LEGO</p>
        </div>
    </body>
    </html>";
    
    return $html;
}

// 📄 GENERAR CONTENIDO SIMPLE PARA PDF (simulación)
function generateSimplePDFContent($orderData, $pieceColors, $visubloqConfig) {
    $content = "VISUBLOQ - LISTA DE PIEZAS LEGO\n";
    $content .= "====================================\n\n";
    $content .= "Pedido: {$orderData['order_number']}\n";
    $content .= "Cliente: {$orderData['customer_name']}\n";
    $content .= "Email: {$orderData['customer_email']}\n";
    $content .= "Dimensiones: {$orderData['dimensions']}\n";
    $content .= "Fecha: " . date('d/m/Y H:i', strtotime($orderData['created_at'])) . "\n\n";
    
    $totalPieces = array_sum($pieceColors);
    $content .= "RESUMEN DEL DISEÑO:\n";
    $content .= "- Total de piezas: {$totalPieces}\n";
    $content .= "- Tipos de colores: " . count($pieceColors) . "\n\n";
    
    $content .= "LISTA DETALLADA DE PIEZAS:\n";
    $content .= "-------------------------\n";
    
    // Ordenar por cantidad
    arsort($pieceColors);
    
    foreach ($pieceColors as $hexColor => $quantity) {
        $percentage = round(($quantity / $totalPieces) * 100, 1);
        $content .= sprintf("Color: %-8s | Cantidad: %-4d | Porcentaje: %s%%\n", 
                           $hexColor, $quantity, $percentage);
    }
    
    $content .= "\n\nGenerado el: " . date('d/m/Y H:i:s') . "\n";
    $content .= "VisuBloq - Construye tu obra maestra LEGO\n";
    
    return $content;
}

// 📥 DESCARGAR PDF
function downloadPDF() {
    $filename = $_GET['file'] ?? null;
    
    if (!$filename) {
        jsonResponse(false, 'Nombre de archivo requerido', null, 400);
    }
    
    $filepath = PDF_STORAGE_PATH . $filename;
    
    if (!file_exists($filepath)) {
        jsonResponse(false, 'Archivo no encontrado', null, 404);
    }
    
    // Configurar headers para descarga
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    
    // Enviar archivo
    readfile($filepath);
    exit;
}

?>
