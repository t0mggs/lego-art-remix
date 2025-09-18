<?php
/**
 * Script para insertar datos de prueba automáticamente
 */

require_once 'backend/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>📊 Insertando Pedidos de Prueba</h1>";
    
    // Insertar pedidos de ejemplo
    $orders = [
        [1001234567, '#1001', 'Juan Pérez', 'juan@example.com', 29.99, 'paid'],
        [1001234568, '#1002', 'María García', 'maria@example.com', 45.50, 'paid'],
        [1001234569, '#1003', 'Carlos López', 'carlos@example.com', 32.75, 'pending'],
        [1001234570, '#1004', 'Ana Martínez', 'ana@example.com', 28.00, 'paid']
    ];
    
    foreach ($orders as $order) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO orders 
                (shopify_order_id, order_number, customer_name, customer_email, order_value, order_status) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute($order);
            echo "<p>✅ Pedido insertado: " . $order[1] . " - " . $order[2] . "</p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Pedido ya existe: " . $order[1] . "</p>";
        }
    }
    
    // Insertar más diseños
    $designs = [
        ['VB-1758000001-SHOP', 'SESS-001', 1, 32, 32, 48, 5],
        ['VB-1758000002-SHOP', 'SESS-002', 2, 48, 48, 72, 6],
        ['VB-1758000003-SHOP', 'SESS-003', 4, 24, 24, 36, 4]
    ];
    
    foreach ($designs as $design) {
        try {
            $pieces_data = json_encode([
                'totalPieces' => $design[5],
                'uniqueColors' => $design[6],
                'studMap' => []
            ]);
            $config = json_encode([
                'imageWidth' => $design[3],
                'imageHeight' => $design[4],
                'studSize' => 8
            ]);
            
            $stmt = $pdo->prepare("
                INSERT INTO design_images 
                (design_id, session_id, order_id, width, height, pieces_data, pdf_blob, visubloq_config, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $design[0], $design[1], $design[2], $design[3], $design[4], 
                $pieces_data, 'JVBERi0xLjQKMSAwIG9iago=', $config, 'purchased'
            ]);
            echo "<p>✅ Diseño insertado: " . $design[0] . "</p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Diseño ya existe: " . $design[0] . "</p>";
        }
    }
    
    // Verificar totales
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $orders_count = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM design_images");
    $designs_count = $stmt->fetchColumn();
    
    echo "<h3>📈 Resumen Final</h3>";
    echo "<p>🛒 <strong>Pedidos totales:</strong> $orders_count</p>";
    echo "<p>🎨 <strong>Diseños totales:</strong> $designs_count</p>";
    
    echo "<h3>🎯 Ahora Prueba el Dashboard</h3>";
    echo "<p><a href='backend/admin/orders-dashboard.php' target='_blank' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 18px;'>📊 VER DASHBOARD CON PEDIDOS</a></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
h1, h3 { color: #333; }
p { margin: 10px 0; }
</style>