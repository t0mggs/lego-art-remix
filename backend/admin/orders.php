<?php
// 📊 API PARA MANEJAR PEDIDOS Y ESTADÍSTICAS
// backend/admin/orders.php

session_start();
require_once '../config.php';

// TEMPORAL: Desactivar verificación de login para testing local
// Verificar que el admin está logueado
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     jsonResponse(false, 'No autorizado', null, 401);
// }

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'stats':
        getStatistics();
        break;
    case 'list':
        getOrdersList();
        break;
    case 'detail':
        getOrderDetail();
        break;
    case 'generate_pdf':
        generatePDFForOrder();
        break;
    case 'generate_pieces_pdf':
        generatePiecesPDFForOrder();
        break;
    case 'download_pdf':
        downloadPDFForOrder();
        break;
    // Nuevas acciones simplificadas
    case 'simple_stats':
        getSimpleStatistics();
        break;
    case 'simple_list':
        getSimpleOrdersList();
        break;
    default:
        jsonResponse(false, 'Acción no válida', null, 400);
}

// 📊 OBTENER ESTADÍSTICAS
function getStatistics() {
    try {
        $pdo = getDatabase();
        
        // Total de pedidos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
        $totalOrders = $stmt->fetch()['total'];
        
        // Ingresos totales
        $stmt = $pdo->query("SELECT SUM(order_value) as total FROM orders WHERE order_status = 'paid'");
        $totalRevenue = $stmt->fetch()['total'] ?? 0;
        
        // Pedidos pendientes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE order_status != 'paid'");
        $pendingOrders = $stmt->fetch()['total'];
        
        // Diseños generados
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM design_images");
        $totalDesigns = $stmt->fetch()['total'];
        
        // Diseños con PDF
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM design_images WHERE pdf_blob IS NOT NULL");
        $designsWithPDF = $stmt->fetch()['total'];
        
        // PDFs generados
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM order_pdfs");
        $pdfsGenerated = $stmt->fetch()['total'];
        
        jsonResponse(true, 'Estadísticas obtenidas', [
            'total_orders' => $totalOrders,
            'total_revenue' => number_format($totalRevenue, 2),
            'pending_orders' => $pendingOrders,
            'total_designs' => $totalDesigns,
            'designs_with_pdf' => $designsWithPDF,
            'pdfs_generated' => $pdfsGenerated
        ]);
        
    } catch (Exception $e) {
        logMessage('ERROR', 'Error obteniendo estadísticas: ' . $e->getMessage());
        jsonResponse(false, 'Error obteniendo estadísticas', null, 500);
    }
}

// 📋 OBTENER LISTA DE PEDIDOS
function getOrdersList() {
    try {
        $pdo = getDatabase();
        
        $limit = $_GET['limit'] ?? 50;
        $offset = $_GET['offset'] ?? 0;
        
        $stmt = $pdo->prepare("
            SELECT 
                o.*,
                COUNT(DISTINCT pdf.id) as pdf_count,
                COUNT(DISTINCT di.id) as design_count,
                GROUP_CONCAT(DISTINCT di.design_id) as design_ids,
                op.total_pieces,
                op.piece_colors
            FROM orders o
            LEFT JOIN order_pdfs pdf ON o.id = pdf.order_id
            LEFT JOIN design_images di ON o.id = di.order_id
            LEFT JOIN order_pieces op ON o.id = op.order_id
            WHERE o.order_status = 'paid'
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $orders = $stmt->fetchAll();
        
        // Procesar datos para el frontend
        foreach ($orders as &$order) {
            if ($order['piece_colors']) {
                $order['piece_colors'] = json_decode($order['piece_colors'], true);
            }
            $order['design_ids'] = $order['design_ids'] ? explode(',', $order['design_ids']) : [];
        }
        
        jsonResponse(true, 'Pedidos pagados obtenidos', $orders);
        
    } catch (Exception $e) {
        logMessage('ERROR', 'Error obteniendo pedidos: ' . $e->getMessage());
        jsonResponse(false, 'Error obteniendo pedidos', null, 500);
    }
}

// 🔍 OBTENER DETALLE DE PEDIDO
function getOrderDetail() {
    try {
        $orderId = $_GET['id'] ?? null;
        
        if (!$orderId) {
            jsonResponse(false, 'ID de pedido requerido', null, 400);
        }
        
        $pdo = getDatabase();
        
        // Obtener información del pedido
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if (!$order) {
            jsonResponse(false, 'Pedido no encontrado', null, 404);
        }
        
        // Obtener información de piezas con análisis detallado
        $stmt = $pdo->prepare("SELECT * FROM order_pieces WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $pieces = $stmt->fetch();
        
        // Procesar información de piezas si existe
        $processedPieces = null;
        if ($pieces) {
            $pieceColors = json_decode($pieces['piece_colors'], true);
            $visubloqConfig = json_decode($pieces['visubloq_config'], true);
            
            // Calcular estadísticas
            $totalPieces = array_sum($pieceColors);
            $colorCount = count($pieceColors);
            
            // Ordenar colores por cantidad
            arsort($pieceColors);
            
            // Crear array de colores con porcentajes
            $colorAnalysis = [];
            foreach ($pieceColors as $hex => $quantity) {
                $percentage = round(($quantity / $totalPieces) * 100, 1);
                $colorAnalysis[] = [
                    'hex' => $hex,
                    'quantity' => $quantity,
                    'percentage' => $percentage
                ];
            }
            
            $processedPieces = [
                'raw_data' => $pieces,
                'total_pieces' => $totalPieces,
                'color_count' => $colorCount,
                'color_analysis' => $colorAnalysis,
                'dimensions' => $pieces['dimensions'],
                'visubloq_config' => $visubloqConfig
            ];
        }
        
        // Obtener PDFs asociados
        $stmt = $pdo->prepare("SELECT * FROM order_pdfs WHERE order_id = ? ORDER BY generated_at DESC");
        $stmt->execute([$orderId]);
        $pdfs = $stmt->fetchAll();
        
        jsonResponse(true, 'Detalle obtenido', [
            'order' => $order,
            'pieces' => $processedPieces,
            'pdfs' => $pdfs
        ]);
        
    } catch (Exception $e) {
        logMessage('ERROR', 'Error obteniendo detalle: ' . $e->getMessage());
        jsonResponse(false, 'Error obteniendo detalle', null, 500);
    }
}

// 📄 GENERAR PDF PARA PEDIDO
function generatePDFForOrder() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;
        
        if (!$orderId) {
            jsonResponse(false, 'ID de pedido requerido', null, 400);
        }
        
        $pdo = getDatabase();
        
        // Obtener información del pedido
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if (!$order) {
            jsonResponse(false, 'Pedido no encontrado', null, 404);
        }
        
        // Aquí es donde necesitarías integrar con tu sistema de generación de PDFs
        // Por ahora, simularemos la generación
        
        $pdfFilename = "visubloq_instructions_{$order['order_number']}_" . date('Y-m-d_H-i-s') . ".pdf";
        $pdfPath = PDF_STORAGE_PATH . $pdfFilename;
        
        // Simular generación de PDF (en realidad aquí llamarías a tu función de generación)
        $simulatedPDFContent = "PDF simulado para pedido {$order['order_number']}";
        
        // Crear directorio si no existe
        if (!file_exists(PDF_STORAGE_PATH)) {
            mkdir(PDF_STORAGE_PATH, 0755, true);
        }
        
        // Guardar archivo simulado
        file_put_contents($pdfPath, $simulatedPDFContent);
        
        // Guardar información en base de datos
        $stmt = $pdo->prepare("
            INSERT INTO order_pdfs (order_id, pdf_filename, pdf_path, pdf_size, pdf_type) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $orderId,
            $pdfFilename,
            $pdfPath,
            strlen($simulatedPDFContent),
            'instructions'
        ]);
        
        logMessage('PDF_GENERATED', 'PDF generado para pedido', [
            'order_id' => $orderId,
            'filename' => $pdfFilename
        ]);
        
        jsonResponse(true, 'PDF generado exitosamente', [
            'filename' => $pdfFilename,
            'path' => $pdfPath
        ]);
        
    } catch (Exception $e) {
        logMessage('ERROR', 'Error generando PDF: ' . $e->getMessage());
        jsonResponse(false, 'Error generando PDF', null, 500);
    }
}

// 📄 GENERAR PDF DE LISTA DE PIEZAS PARA PEDIDO
function generatePiecesPDFForOrder() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;
        
        if (!$orderId) {
            jsonResponse(false, 'ID de pedido requerido', null, 400);
        }
        
        $pdo = getDatabase();
        
        // Obtener información del pedido y piezas
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
        
        // Generar PDF de piezas
        $pieceColors = json_decode($orderData['piece_colors'], true);
        $visubloqConfig = json_decode($orderData['visubloq_config'], true);
        
        $pdfFilename = "visubloq_pieces_list_{$orderData['order_number']}_" . date('Y-m-d_H-i-s') . ".pdf";
        $pdfPath = PDF_STORAGE_PATH . $pdfFilename;
        
        // Crear directorio si no existe
        if (!file_exists(PDF_STORAGE_PATH)) {
            mkdir(PDF_STORAGE_PATH, 0755, true);
        }
        
        // Generar contenido del PDF
        $pdfContent = generatePiecesListContent($orderData, $pieceColors, $visubloqConfig);
        file_put_contents($pdfPath, $pdfContent);
        
        // Guardar información en base de datos
        $stmt = $pdo->prepare("
            INSERT INTO order_pdfs (order_id, pdf_filename, pdf_path, pdf_size, pdf_type) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $orderId,
            $pdfFilename,
            $pdfPath,
            strlen($pdfContent),
            'pieces_list'
        ]);
        
        logMessage('PIECES_PDF_GENERATED', 'PDF de piezas generado para pedido', [
            'order_id' => $orderId,
            'filename' => $pdfFilename
        ]);
        
        jsonResponse(true, 'PDF de piezas generado exitosamente', [
            'filename' => $pdfFilename,
            'path' => $pdfPath,
            'download_url' => BASE_URL . '/backend/api/generate-pdf.php?action=download&file=' . urlencode($pdfFilename)
        ]);
        
    } catch (Exception $e) {
        logMessage('ERROR', 'Error generando PDF de piezas: ' . $e->getMessage());
        jsonResponse(false, 'Error generando PDF de piezas', null, 500);
    }
}

// 📄 GENERAR CONTENIDO PARA PDF DE PIEZAS
function generatePiecesListContent($orderData, $pieceColors, $visubloqConfig) {
    $totalPieces = array_sum($pieceColors);
    $pieceTypes = count($pieceColors);
    
    $content = "===========================================\n";
    $content .= "       VISUBLOQ - LISTA DE PIEZAS LEGO      \n";
    $content .= "===========================================\n\n";
    
    $content .= "INFORMACIÓN DEL PEDIDO:\n";
    $content .= "-----------------------\n";
    $content .= "Número de Pedido: {$orderData['order_number']}\n";
    $content .= "Cliente: {$orderData['customer_name']}\n";
    $content .= "Email: {$orderData['customer_email']}\n";
    $content .= "Dimensiones: {$orderData['dimensions']}\n";
    $content .= "Fecha: " . date('d/m/Y H:i', strtotime($orderData['created_at'])) . "\n";
    $content .= "Valor del Pedido: €{$orderData['order_value']}\n\n";
    
    $content .= "RESUMEN DEL DISEÑO:\n";
    $content .= "-------------------\n";
    $content .= "Total de Piezas: {$totalPieces}\n";
    $content .= "Tipos de Colores: {$pieceTypes}\n\n";
    
    $content .= "LISTA DETALLADA DE PIEZAS:\n";
    $content .= "==========================\n";
    $content .= sprintf("%-10s | %-8s | %-6s | %-10s\n", "Color Hex", "Cantidad", "%", "Descripción");
    $content .= str_repeat("-", 50) . "\n";
    
    // Ordenar por cantidad (descendente)
    arsort($pieceColors);
    
    foreach ($pieceColors as $hexColor => $quantity) {
        $percentage = round(($quantity / $totalPieces) * 100, 1);
        $content .= sprintf("%-10s | %-8d | %-6s | %-10s\n", 
                           $hexColor, 
                           $quantity, 
                           $percentage . '%',
                           'Pieza LEGO');
    }
    
    $content .= "\n" . str_repeat("=", 50) . "\n";
    $content .= "INSTRUCCIONES DE MONTAJE:\n";
    $content .= "1. Organiza las piezas por colores\n";
    $content .= "2. Sigue el patrón del diseño original\n";
    $content .= "3. Construye fila por fila desde abajo\n";
    $content .= "4. Verifica que cada color esté en su lugar\n\n";
    
    $content .= "INFORMACIÓN TÉCNICA:\n";
    $content .= "Resolución: {$orderData['dimensions']}\n";
    $content .= "Tipo de Piezas: LEGO originales\n";
    $content .= "Compatibilidad: 100% LEGO System\n\n";
    
    $content .= "Generado el: " . date('d/m/Y H:i:s') . "\n";
    $content .= "© VisuBloq - Construye tu obra maestra LEGO\n";
    
    return $content;
}

// 📥 DESCARGAR PDF DE PEDIDO
function downloadPDFForOrder() {
    try {
        $designId = $_GET['design_id'] ?? '';
        $orderId = $_GET['order_id'] ?? '';
        
        if (!$designId && !$orderId) {
            jsonResponse(false, 'ID de diseño o pedido requerido', null, 400);
        }
        
        $pdo = getDatabase();
        
        if ($designId) {
            // Buscar por design_id en design_images
            $stmt = $pdo->prepare("SELECT pdf_blob, design_id, width, height FROM design_images WHERE design_id = ? AND pdf_blob IS NOT NULL");
            $stmt->execute([$designId]);
            $design = $stmt->fetch();
            
            if (!$design) {
                jsonResponse(false, 'PDF no encontrado para este diseño', null, 404);
            }
            
            $filename = "visubloq_design_{$designId}.pdf";
            $pdfData = $design['pdf_blob'];
            
        } else {
            // Buscar por order_id
            $stmt = $pdo->prepare("
                SELECT di.pdf_blob, di.design_id, di.width, di.height, o.shopify_order_id 
                FROM design_images di 
                JOIN orders o ON di.order_id = o.id 
                WHERE o.id = ? AND di.pdf_blob IS NOT NULL 
                ORDER BY di.created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$orderId]);
            $design = $stmt->fetch();
            
            if (!$design) {
                jsonResponse(false, 'PDF no encontrado para este pedido', null, 404);
            }
            
            $filename = "visubloq_order_{$design['shopify_order_id']}.pdf";
            $pdfData = $design['pdf_blob'];
        }
        
        // Enviar headers para descarga de PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfData));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Enviar el PDF
        echo $pdfData;
        exit;
        
    } catch (Exception $e) {
        logMessage('ERROR', 'Error descargando PDF: ' . $e->getMessage());
        jsonResponse(false, 'Error descargando PDF', null, 500);
    }
}

// 📊 ESTADÍSTICAS SIMPLIFICADAS
function getSimpleStatistics() {
    try {
        $pdo = getDatabase();
        
        // Total de pedidos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
        $totalOrders = $stmt->fetch()['total'];
        
        // Ingresos totales de pedidos pagados
        $stmt = $pdo->query("SELECT SUM(order_value) as total FROM orders WHERE order_status = 'paid'");
        $totalRevenue = number_format($stmt->fetch()['total'] ?? 0, 2);
        
        // Pedidos pagados
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE order_status = 'paid'");
        $paidOrders = $stmt->fetch()['total'];
        
        // Pedidos pendientes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE order_status = 'pending'");
        $pendingOrders = $stmt->fetch()['total'];
        
        jsonResponse(true, 'Estadísticas obtenidas', [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'paid_orders' => $paidOrders,
            'pending_orders' => $pendingOrders
        ]);
        
    } catch (Exception $e) {
        jsonResponse(false, 'Error obteniendo estadísticas: ' . $e->getMessage(), null, 500);
    }
}

// 📋 LISTA SIMPLE DE PEDIDOS CON PDF
function getSimpleOrdersList() {
    try {
        $pdo = getDatabase();
        
        // Obtener pedidos que tienen diseños/PDFs asociados
        $sql = "
            SELECT 
                o.id,
                o.order_number,
                o.customer_name,
                o.customer_email,
                o.order_value,
                o.order_status,
                o.created_at,
                d.design_id,
                d.status as design_status
            FROM orders o
            INNER JOIN design_images d ON o.id = d.order_id
            WHERE d.pdf_blob IS NOT NULL
            ORDER BY o.created_at DESC
        ";
        
        $stmt = $pdo->query($sql);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse(true, 'Pedidos obtenidos', $orders);
        
    } catch (Exception $e) {
        jsonResponse(false, 'Error obteniendo pedidos: ' . $e->getMessage(), null, 500);
    }
}

?>
