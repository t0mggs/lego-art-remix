<?php
// 📊 API PARA MANEJAR PEDIDOS Y ESTADÍSTICAS
// backend/admin/orders.php

session_start();
require_once '../config.php';

// Verificar que el admin está logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    jsonResponse(false, 'No autorizado', null, 401);
}

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
    default:
        jsonResponse(false, 'Acción no válida', null, 400);
}

// 📊 OBTENER ESTADÍSTICAS
function getStatistics() {
    try {
        $pdo = getDBConnection();
        
        // Total de pedidos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
        $totalOrders = $stmt->fetch()['total'];
        
        // Ingresos totales
        $stmt = $pdo->query("SELECT SUM(order_value) as total FROM orders WHERE order_status = 'paid'");
        $totalRevenue = $stmt->fetch()['total'] ?? 0;
        
        // Pedidos pendientes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE order_status != 'paid'");
        $pendingOrders = $stmt->fetch()['total'];
        
        // PDFs generados
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM order_pdfs");
        $pdfsGenerated = $stmt->fetch()['total'];
        
        jsonResponse(true, 'Estadísticas obtenidas', [
            'total_orders' => $totalOrders,
            'total_revenue' => number_format($totalRevenue, 2),
            'pending_orders' => $pendingOrders,
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
        $pdo = getDBConnection();
        
        $limit = $_GET['limit'] ?? 50;
        $offset = $_GET['offset'] ?? 0;
        
        $stmt = $pdo->prepare("
            SELECT 
                o.*,
                COUNT(pdf.id) as pdf_count
            FROM orders o
            LEFT JOIN order_pdfs pdf ON o.id = pdf.order_id
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$limit, $offset]);
        $orders = $stmt->fetchAll();
        
        jsonResponse(true, 'Pedidos obtenidos', $orders);
        
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
        
        $pdo = getDBConnection();
        
        // Obtener información del pedido
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if (!$order) {
            jsonResponse(false, 'Pedido no encontrado', null, 404);
        }
        
        // Obtener información de piezas
        $stmt = $pdo->prepare("SELECT * FROM order_pieces WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $pieces = $stmt->fetch();
        
        // Obtener PDFs asociados
        $stmt = $pdo->prepare("SELECT * FROM order_pdfs WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $pdfs = $stmt->fetchAll();
        
        jsonResponse(true, 'Detalle obtenido', [
            'order' => $order,
            'pieces' => $pieces,
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
        
        $pdo = getDBConnection();
        
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

?>
