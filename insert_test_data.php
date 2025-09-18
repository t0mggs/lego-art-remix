<?php
// Script para insertar datos de prueba
require_once 'backend/config.php';

try {
    $pdo = getDatabase();
    
    // Insertar pedidos de prueba
    $orders = [
        [
            'order_id' => 'TEST001',
            'customer_email' => 'test1@example.com',
            'customer_name' => 'Juan Pérez',
            'order_status' => 'paid',
            'order_value' => 49.99,
            'image_url' => 'https://example.com/image1.jpg',
            'created_at' => '2025-08-06 14:30:00'
        ],
        [
            'order_id' => 'TEST002',
            'customer_email' => 'test2@example.com',
            'customer_name' => 'María García',
            'order_status' => 'paid',
            'order_value' => 67.50,
            'image_url' => 'https://example.com/image2.jpg',
            'created_at' => '2025-08-07 10:15:00'
        ],
        [
            'order_id' => 'TEST003',
            'customer_email' => 'test3@example.com',
            'customer_name' => 'Carlos López',
            'order_status' => 'pending',
            'order_value' => 25.25,
            'image_url' => 'https://example.com/image3.jpg',
            'created_at' => '2025-08-07 16:45:00'
        ]
    ];
    
    // Limpiar datos existentes
    $pdo->exec("DELETE FROM order_pieces");
    $pdo->exec("DELETE FROM order_pdfs");
    $pdo->exec("DELETE FROM orders");
    
    // Insertar nuevos datos
    $stmt = $pdo->prepare("
        INSERT INTO orders (order_id, customer_email, customer_name, order_status, order_value, image_url, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($orders as $order) {
        $stmt->execute([
            $order['order_id'],
            $order['customer_email'],
            $order['customer_name'],
            $order['order_status'],
            $order['order_value'],
            $order['image_url'],
            $order['created_at']
        ]);
        echo "Pedido {$order['order_id']} insertado\n";
    }
    
    // Verificar inserción
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $count = $stmt->fetch()['total'];
    echo "\nTotal de pedidos en la base de datos: $count\n";
    
    echo "¡Datos de prueba insertados correctamente!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
