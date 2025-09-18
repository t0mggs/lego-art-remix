<?php
echo "<h2>📊 Insertar Datos de Prueba para VisuBloq</h2>";

try {
    $pdo = new PDO(
        "mysql:host=localhost;port=3306;dbname=visubloq_db;charset=utf8mb4",
        'root',
        'admin'
    );
    
    echo "✅ Conexión exitosa<br>";
    
    // Limpiar datos existentes
    $pdo->exec("DELETE FROM order_pieces");
    $pdo->exec("DELETE FROM order_pdfs");
    $pdo->exec("DELETE FROM orders");
    echo "🧹 Datos anteriores limpiados<br>";
    
    echo "<h3>Insertando pedidos de prueba:</h3>";
    
    // Preparar la consulta
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_id, 
            customer_email, 
            customer_name, 
            order_status, 
            order_value, 
            image_url, 
            created_at,
            shopify_order_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    // Datos de prueba realistas para VisuBloq
    $test_orders = [
        [
            'VB001', 
            'juan.perez@gmail.com', 
            'Juan Pérez', 
            'paid', 
            49.99, 
            'https://example.com/lego-portrait-1.jpg', 
            '2025-08-07 10:30:00',
            'SHOP001'
        ],
        [
            'VB002', 
            'maria.garcia@hotmail.com', 
            'María García', 
            'paid', 
            67.50, 
            'https://example.com/lego-landscape-1.jpg', 
            '2025-08-07 11:15:00',
            'SHOP002'
        ],
        [
            'VB003', 
            'carlos.lopez@yahoo.com', 
            'Carlos López', 
            'pending', 
            25.25, 
            'https://example.com/lego-custom-1.jpg', 
            '2025-08-07 12:00:00',
            'SHOP003'
        ],
        [
            'VB004', 
            'ana.martin@gmail.com', 
            'Ana Martín', 
            'paid', 
            89.99, 
            'https://example.com/lego-art-1.jpg', 
            '2025-08-06 15:30:00',
            'SHOP004'
        ],
        [
            'VB005', 
            'luis.rodriguez@outlook.com', 
            'Luis Rodríguez', 
            'paid', 
            124.75, 
            'https://example.com/lego-mosaic-1.jpg', 
            '2025-08-05 09:45:00',
            'SHOP005'
        ],
        [
            'VB006', 
            'sofia.hernandez@gmail.com', 
            'Sofía Hernández', 
            'cancelled', 
            35.99, 
            'https://example.com/lego-mini-1.jpg', 
            '2025-08-04 14:20:00',
            'SHOP006'
        ]
    ];
    
    foreach ($test_orders as $order) {
        $stmt->execute($order);
        echo "✅ Insertado: {$order[0]} - {$order[2]} - €{$order[4]} ({$order[3]})<br>";
    }
    
    echo "<h3>📊 Verificación de datos:</h3>";
    
    // Contar total
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $total = $stmt->fetch()['total'];
    echo "Total de pedidos: <strong>$total</strong><br>";
    
    // Estadísticas por estado
    $stmt = $pdo->query("
        SELECT 
            order_status, 
            COUNT(*) as count, 
            SUM(order_value) as total_value 
        FROM orders 
        GROUP BY order_status
    ");
    $stats = $stmt->fetchAll();
    
    echo "<h4>Resumen por estado:</h4>";
    $total_revenue = 0;
    foreach ($stats as $stat) {
        echo "- <strong>{$stat['order_status']}</strong>: {$stat['count']} pedidos, €{$stat['total_value']}<br>";
        if ($stat['order_status'] == 'paid') {
            $total_revenue = $stat['total_value'];
        }
    }
    
    echo "<h4>🎯 Lo que debería mostrar el dashboard:</h4>";
    echo "- <strong>Total Pedidos</strong>: $total<br>";
    echo "- <strong>Ingresos Totales</strong>: €$total_revenue (solo pedidos pagados)<br>";
    echo "- <strong>Pedidos Pendientes</strong>: " . ($total - count(array_filter($stats, fn($s) => $s['order_status'] == 'paid'))) . "<br>";
    
    echo "<br>🎉 <strong>¡Datos de prueba insertados correctamente!</strong><br>";
    echo "<br><strong>Ahora puedes probar:</strong><br>";
    echo '<a href="orders.php?action=stats" target="_blank">🔗 API de Estadísticas</a><br>';
    echo '<a href="orders.php?action=list" target="_blank">🔗 API de Lista de Pedidos</a><br>';
    echo '<a href="index.php" target="_blank">🔗 Dashboard Admin</a><br>';
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Código: " . $e->getCode() . "<br>";
}
?>
