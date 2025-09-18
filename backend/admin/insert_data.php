<?php
require_once '../config.php';

echo "<h2>🔧 Insertar Datos de Prueba</h2>";

try {
    $pdo = getDatabase();
    echo "✅ Conexión exitosa<br>";
    
    // Limpiar datos existentes
    $pdo->exec("DELETE FROM order_pieces");
    $pdo->exec("DELETE FROM order_pdfs");  
    $pdo->exec("DELETE FROM orders");
    echo "🧹 Datos anteriores limpiados<br>";
    
    // Insertar pedidos de prueba
    $stmt = $pdo->prepare("
        INSERT INTO orders (order_id, customer_email, customer_name, order_status, order_value, image_url, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $orders = [
        ['TEST001', 'juan@example.com', 'Juan Pérez', 'paid', 49.99, 'https://example.com/img1.jpg', '2025-08-07 10:00:00'],
        ['TEST002', 'maria@example.com', 'María García', 'paid', 67.50, 'https://example.com/img2.jpg', '2025-08-07 11:00:00'],
        ['TEST003', 'carlos@example.com', 'Carlos López', 'pending', 25.25, 'https://example.com/img3.jpg', '2025-08-07 12:00:00'],
        ['TEST004', 'ana@example.com', 'Ana Martín', 'paid', 89.99, 'https://example.com/img4.jpg', '2025-08-06 15:30:00'],
        ['TEST005', 'luis@example.com', 'Luis Rodríguez', 'cancelled', 35.75, 'https://example.com/img5.jpg', '2025-08-05 09:45:00']
    ];
    
    foreach ($orders as $order) {
        $stmt->execute($order);
        echo "✅ Insertado: {$order[0]} - {$order[2]} - €{$order[4]}<br>";
    }
    
    // Verificar inserción
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $total = $stmt->fetch()['total'];
    echo "<br><strong>📊 Total de pedidos en BD: $total</strong><br>";
    
    // Mostrar resumen
    $stmt = $pdo->query("SELECT order_status, COUNT(*) as count, SUM(order_value) as total FROM orders GROUP BY order_status");
    $stats = $stmt->fetchAll();
    
    echo "<h3>📈 Resumen por estado:</h3>";
    foreach ($stats as $stat) {
        echo "- {$stat['order_status']}: {$stat['count']} pedidos, €{$stat['total']}<br>";
    }
    
    echo "<br>🎉 <strong>Datos insertados correctamente!</strong><br>";
    echo '<a href="orders.php?action=stats">🔗 Probar API Stats</a> | ';
    echo '<a href="orders.php?action=list">🔗 Probar API List</a> | ';
    echo '<a href="index.php">🔗 Ir al Dashboard</a>';
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
