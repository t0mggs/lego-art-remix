<?php
require_once 'backend/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    
    echo "<h2>🔍 VERIFICACIÓN DE DATOS</h2>";
    
    // Ver todos los pedidos
    echo "<h3>📋 PEDIDOS EN LA BD:</h3>";
    $stmt = $pdo->query('SELECT id, order_number, customer_name, order_status FROM orders ORDER BY id');
    while ($row = $stmt->fetch()) {
        echo "<p>ID: {$row['id']} - {$row['order_number']} - <strong>{$row['customer_name']}</strong> - {$row['order_status']}</p>";
    }
    
    // Ver todos los diseños
    echo "<h3>🎨 DISEÑOS EN LA BD:</h3>";
    $stmt = $pdo->query('SELECT id, design_id, order_id, status FROM design_images ORDER BY id');
    while ($row = $stmt->fetch()) {
        echo "<p>ID: {$row['id']} - {$row['design_id']} - Pedido: {$row['order_id']} - {$row['status']}</p>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
p { margin: 5px 0; }
</style>