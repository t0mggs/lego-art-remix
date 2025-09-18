<?php
session_start();
require_once '../config.php';

// Forzar login para testing
$_SESSION['admin_logged_in'] = true;

echo "<h2>Test Directo de API</h2>";

echo "<h3>1. Test Base de Datos:</h3>";
try {
    $pdo = getDatabase();
    echo "✅ Conexión a BD exitosa<br>";
    
    // Verificar si hay datos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $count = $stmt->fetch()['total'];
    echo "Pedidos en BD: $count<br>";
    
    if ($count == 0) {
        echo "❌ No hay datos en la tabla orders<br>";
        echo "<strong>Insertando datos de prueba...</strong><br>";
        
        // Insertar datos directamente
        $pdo->exec("INSERT INTO orders (order_id, customer_email, customer_name, order_status, order_value, image_url, created_at) VALUES 
            ('TEST001', 'test1@example.com', 'Juan Pérez', 'paid', 49.99, 'https://example.com/image1.jpg', NOW()),
            ('TEST002', 'test2@example.com', 'María García', 'paid', 67.50, 'https://example.com/image2.jpg', NOW()),
            ('TEST003', 'test3@example.com', 'Carlos López', 'pending', 25.25, 'https://example.com/image3.jpg', NOW())");
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
        $newCount = $stmt->fetch()['total'];
        echo "✅ Datos insertados. Nuevo total: $newCount<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "<br>";
}

echo "<h3>2. Test API Stats Manual:</h3>";
try {
    // Simular exactamente lo que hace getStatistics()
    $pdo = getDatabase();
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT SUM(order_value) as total FROM orders WHERE order_status = 'paid'");
    $totalRevenue = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE order_status != 'paid'");
    $pendingOrders = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM order_pdfs");
    $pdfsGenerated = $stmt->fetch()['total'];
    
    echo "Total Orders: $totalOrders<br>";
    echo "Total Revenue: " . number_format($totalRevenue, 2) . "<br>";
    echo "Pending Orders: $pendingOrders<br>";
    echo "PDFs Generated: $pdfsGenerated<br>";
    
    // Crear la respuesta JSON que debería devolver
    $response = [
        'success' => true,
        'message' => 'Estadísticas obtenidas',
        'data' => [
            'total_orders' => $totalOrders,
            'total_revenue' => number_format($totalRevenue, 2),
            'pending_orders' => $pendingOrders,
            'pdfs_generated' => $pdfsGenerated
        ]
    ];
    
    echo "<h4>JSON que debería devolver:</h4>";
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ Error en stats: " . $e->getMessage() . "<br>";
}

echo "<h3>3. Enlaces para probar API:</h3>";
echo '<a href="orders.php?action=stats" target="_blank">🔗 Probar orders.php?action=stats</a><br>';
echo '<a href="orders.php?action=list" target="_blank">🔗 Probar orders.php?action=list</a><br>';
?>
