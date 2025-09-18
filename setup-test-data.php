<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=visubloq_db', 'root', 'admin');
    
    echo "🧹 LIMPIANDO DATOS DE PRUEBA EXISTENTES...\n";
    
    // Limpiar datos de prueba existentes
    $pdo->exec("DELETE FROM order_pieces WHERE order_id IN (SELECT id FROM orders WHERE shopify_order_id = 999999)");
    $pdo->exec("DELETE FROM order_pdfs WHERE order_id IN (SELECT id FROM orders WHERE shopify_order_id = 999999)");
    $pdo->exec("DELETE FROM orders WHERE shopify_order_id = 999999");
    
    echo "✅ Datos antiguos eliminados\n\n";
    
    echo "📦 CREANDO NUEVOS DATOS DE PRUEBA...\n";
    
    // Crear varios pedidos de prueba
    $test_orders = [
        [999001, 'VB-001', 'Ana García', 'ana@example.com', 29.99, 'completed'],
        [999002, 'VB-002', 'Carlos López', 'carlos@example.com', 45.50, 'pending'],
        [999003, 'VB-003', 'María Rodríguez', 'maria@example.com', 67.25, 'processing'],
        [999004, 'VB-004', 'José Martín', 'jose@example.com', 89.00, 'shipped']
    ];
    
    foreach($test_orders as $order_data) {
        // Insertar pedido
        $stmt = $pdo->prepare("INSERT INTO orders (shopify_order_id, order_number, customer_name, customer_email, order_value, order_status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute($order_data);
        
        $order_id = $pdo->lastInsertId();
        
        // Configuración de VisuBloq
        $config = json_encode([
            'width' => rand(16, 48),
            'height' => rand(16, 48),
            'theme' => ['classic', 'modern', 'artistic'][rand(0, 2)]
        ]);
        
        // Colores de piezas
        $piece_colors = json_encode([
            'red' => rand(50, 200),
            'blue' => rand(30, 150),
            'yellow' => rand(20, 100),
            'green' => rand(25, 120),
            'white' => rand(40, 180)
        ]);
        
        $total_pieces = rand(200, 800);
        $dimensions = rand(16, 48) . 'x' . rand(16, 48);
        
        // Insertar piezas
        $stmt = $pdo->prepare("INSERT INTO order_pieces (order_id, visubloq_config, piece_colors, total_pieces, dimensions) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, $config, $piece_colors, $total_pieces, $dimensions]);
        
        // Crear PDF para algunos pedidos
        if (rand(0, 1)) {
            $stmt = $pdo->prepare("INSERT INTO order_pdfs (order_id, pdf_filename, pdf_path, file_size, generated_at) VALUES (?, ?, ?, ?, NOW())");
            $filename = "visubloq_instructions_{$order_data[1]}.pdf";
            $stmt->execute([$order_id, $filename, "/storage/pdfs/$filename", rand(500000, 2000000)]);
        }
        
        echo "✅ Pedido {$order_data[1]} creado\n";
    }
    
    echo "\n📊 RESUMEN FINAL:\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $total_orders = $stmt->fetch()['count'];
    echo "📦 Total pedidos: $total_orders\n";
    
    $stmt = $pdo->query("SELECT SUM(order_value) as total FROM orders");
    $total_revenue = $stmt->fetch()['total'];
    echo "💰 Ingresos totales: $" . number_format($total_revenue, 2) . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
    $pending_orders = $stmt->fetch()['count'];
    echo "⏳ Pedidos pendientes: $pending_orders\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM order_pdfs");
    $total_pdfs = $stmt->fetch()['count'];
    echo "📄 PDFs generados: $total_pdfs\n";
    
    echo "\n🎉 ¡DATOS DE PRUEBA CREADOS EXITOSAMENTE!\n";
    echo "🔄 Refresca el dashboard para ver los cambios\n";
    
} catch(Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
