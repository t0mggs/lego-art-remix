<?php
session_start();
require_once '../config.php';

echo "<h2>Debug de Sesión y API</h2>";

echo "<h3>1. Estado de la Sesión:</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Admin logged in: " . (isset($_SESSION['admin_logged_in']) ? 'SÍ' : 'NO') . "<br>";
echo "Session data: ";
var_dump($_SESSION);

echo "<h3>2. Test de Base de Datos:</h3>";
try {
    $pdo = getDatabase();
    
    // Contar pedidos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    echo "Total de pedidos en BD: $totalOrders<br>";
    
    // Mostrar pedidos
    $stmt = $pdo->query("SELECT * FROM orders LIMIT 3");
    $orders = $stmt->fetchAll();
    echo "Primeros pedidos:<br>";
    foreach ($orders as $order) {
        echo "- ID: {$order['id']}, Order ID: {$order['order_id']}, Status: {$order['order_status']}, Value: {$order['order_value']}<br>";
    }
    
} catch (Exception $e) {
    echo "Error BD: " . $e->getMessage();
}

echo "<h3>3. Test de API Stats:</h3>";
try {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        echo "❌ No hay sesión de admin activa<br>";
        
        // Forzar login para testing
        $_SESSION['admin_logged_in'] = true;
        echo "✅ Sesión forzada para testing<br>";
    }
    
    // Simular llamada a getStatistics
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
    
    // PDFs generados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM order_pdfs");
    $pdfsGenerated = $stmt->fetch()['total'];
    
    echo "Estadísticas calculadas:<br>";
    echo "- Total Orders: $totalOrders<br>";
    echo "- Total Revenue: $totalRevenue<br>";
    echo "- Pending Orders: $pendingOrders<br>";
    echo "- PDFs Generated: $pdfsGenerated<br>";
    
} catch (Exception $e) {
    echo "Error en stats: " . $e->getMessage();
}

echo "<h3>4. Test directo de orders.php?action=stats:</h3>";
// Redirigir a la API real
echo '<a href="orders.php?action=stats" target="_blank">Probar API Stats</a><br>';
echo '<a href="orders.php?action=list" target="_blank">Probar API List</a><br>';
?>
