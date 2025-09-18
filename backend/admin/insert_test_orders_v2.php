<?php
echo "<h2>📊 Insertar Datos - Versión Completa</h2>";

try {
    $pdo = new PDO(
        "mysql:host=localhost;port=3306;dbname=visubloq_db;charset=utf8mb4",
        'root',
        'admin'
    );
    
    echo "✅ Conexión exitosa<br>";
    
    // Primero vamos a ver qué campos tiene la tabla
    echo "<h3>Campos disponibles en la tabla orders:</h3>";
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll();
    
    $available_fields = [];
    foreach ($columns as $col) {
        $available_fields[] = $col['Field'];
        echo "- {$col['Field']}<br>";
    }
    
    // Limpiar datos existentes
    $pdo->exec("DELETE FROM order_pieces");
    $pdo->exec("DELETE FROM order_pdfs");
    $pdo->exec("DELETE FROM orders");
    echo "<br>🧹 Datos anteriores limpiados<br>";
    
    echo "<h3>Insertando pedidos con TODOS los campos necesarios:</h3>";
    
    // Preparar inserción con todos los campos posibles
    $insert_fields = [];
    $placeholders = [];
    
    // Campos que vamos a llenar
    $fields_to_insert = [
        'order_id',
        'shopify_order_id', 
        'order_number',
        'customer_name',
        'customer_email',
        'order_status',
        'order_value',
        'image_url',
        'created_at'
    ];
    
    // Solo usar campos que existen en la tabla
    foreach ($fields_to_insert as $field) {
        if (in_array($field, $available_fields)) {
            $insert_fields[] = $field;
            $placeholders[] = '?';
        }
    }
    
    $sql = "INSERT INTO orders (" . implode(', ', $insert_fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    echo "SQL a ejecutar: $sql<br><br>";
    
    $stmt = $pdo->prepare($sql);
    
    // Datos de prueba completos
    $test_orders = [
        [
            'VB001',           // order_id
            'SHOP001',         // shopify_order_id
            '1001',           // order_number
            'Juan Pérez',      // customer_name
            'juan.perez@gmail.com', // customer_email
            'paid',           // order_status
            49.99,            // order_value
            'https://example.com/lego1.jpg', // image_url
            '2025-08-07 10:30:00' // created_at
        ],
        [
            'VB002',
            'SHOP002', 
            '1002',
            'María García',
            'maria.garcia@hotmail.com',
            'paid',
            67.50,
            'https://example.com/lego2.jpg',
            '2025-08-07 11:15:00'
        ],
        [
            'VB003',
            'SHOP003',
            '1003', 
            'Carlos López',
            'carlos.lopez@yahoo.com',
            'pending',
            25.25,
            'https://example.com/lego3.jpg',
            '2025-08-07 12:00:00'
        ],
        [
            'VB004',
            'SHOP004',
            '1004',
            'Ana Martín',
            'ana.martin@gmail.com', 
            'paid',
            89.99,
            'https://example.com/lego4.jpg',
            '2025-08-06 15:30:00'
        ],
        [
            'VB005',
            'SHOP005',
            '1005',
            'Luis Rodríguez',
            'luis.rodriguez@outlook.com',
            'paid', 
            124.75,
            'https://example.com/lego5.jpg',
            '2025-08-05 09:45:00'
        ]
    ];
    
    foreach ($test_orders as $order_data) {
        // Solo tomar los valores que corresponden a los campos que vamos a insertar
        $values_to_insert = [];
        for ($i = 0; $i < count($insert_fields); $i++) {
            $values_to_insert[] = $order_data[$i];
        }
        
        $stmt->execute($values_to_insert);
        echo "✅ Insertado: {$order_data[0]} - {$order_data[3]} - €{$order_data[6]}<br>";
    }
    
    echo "<h3>📊 Verificación final:</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $total = $stmt->fetch()['total'];
    echo "Total de pedidos: <strong>$total</strong><br>";
    
    // Mostrar algunos datos insertados
    $stmt = $pdo->query("SELECT order_id, customer_name, order_status, order_value FROM orders LIMIT 3");
    $sample_orders = $stmt->fetchAll();
    
    echo "<h4>Muestra de datos insertados:</h4>";
    foreach ($sample_orders as $order) {
        echo "- {$order['order_id']}: {$order['customer_name']} - {$order['order_status']} - €{$order['order_value']}<br>";
    }
    
    echo "<br>🎉 <strong>¡Datos insertados correctamente!</strong><br>";
    echo "<br><strong>Ahora prueba:</strong><br>";
    echo '<a href="orders.php?action=stats" target="_blank">🔗 API de Estadísticas</a><br>';
    echo '<a href="orders.php?action=list" target="_blank">🔗 API de Lista</a><br>';
    echo '<a href="index.php" target="_blank">🔗 Dashboard</a><br>';
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Código: " . $e->getCode() . "<br>";
}
?>
