<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=visubloq_db', 'root', 'admin');
    
    echo "📦 CREANDO DATOS DE PRUEBA SIMPLES...\n";
    
    // Crear algunos pedidos de prueba
    $test_orders = [
        [999001, 'VB-001', 'Ana García', 'ana@example.com', 29.99, 'completed'],
        [999002, 'VB-002', 'Carlos López', 'carlos@example.com', 45.50, 'pending'],
        [999003, 'VB-003', 'María Rodríguez', 'maria@example.com', 67.25, 'processing']
    ];
    
    foreach($test_orders as $order_data) {
        // Insertar pedido
        $stmt = $pdo->prepare("INSERT IGNORE INTO orders (shopify_order_id, order_number, customer_name, customer_email, order_value, order_status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute($order_data);
        
        echo "✅ Pedido {$order_data[1]} creado\n";
    }
    
    echo "\n📊 RESUMEN:\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $total_orders = $stmt->fetch()['count'];
    echo "📦 Total pedidos: $total_orders\n";
    
    $stmt = $pdo->query("SELECT SUM(order_value) as total FROM orders");
    $total_revenue = $stmt->fetch()['total'] ?? 0;
    echo "💰 Ingresos totales: $" . number_format($total_revenue, 2) . "\n";
    
    echo "\n🎉 ¡LISTO! Refresca el dashboard\n";
    
} catch(Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
