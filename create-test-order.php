<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=visubloq_db', 'root', 'admin');
    
    // Crear pedido de prueba
    $stmt = $pdo->prepare("INSERT INTO orders (shopify_order_id, order_number, customer_name, customer_email, order_value, order_status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([999999, 'TEST-001', 'Cliente Prueba', 'test@example.com', 49.99, 'pending']);
    
    $order_id = $pdo->lastInsertId();
    
    // Agregar configuración de piezas
    $config = json_encode([
        'width' => 32,
        'height' => 32,
        'colors' => ['red', 'blue', 'yellow']
    ]);
    
    $piece_colors = json_encode([
        'red' => 150,
        'blue' => 100,
        'yellow' => 75
    ]);
    
    $stmt = $pdo->prepare("INSERT INTO order_pieces (order_id, visubloq_config, piece_colors, total_pieces, dimensions) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$order_id, $config, $piece_colors, 325, '32x32']);
    
    echo "✅ Pedido de prueba creado correctamente\n";
    echo "ID del pedido: $order_id\n";
    
    // Verificar totales
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $total_orders = $stmt->fetch()['count'];
    echo "📦 Total pedidos: $total_orders\n";
    
} catch(Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
