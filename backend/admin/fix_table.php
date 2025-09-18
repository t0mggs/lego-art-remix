<?php
echo "<h2>🔧 Corrección de Estructura de Tabla</h2>";

try {
    $pdo = new PDO(
        "mysql:host=localhost;port=3306;dbname=visubloq_db;charset=utf8mb4",
        'root',
        'admin'
    );
    
    echo "✅ Conexión exitosa<br>";
    
    // Primero vamos a ver la estructura actual
    echo "<h3>Estructura actual de la tabla orders:</h3>";
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})<br>";
    }
    
    echo "<h3>Correcciones necesarias:</h3>";
    
    // Verificar si existe la columna order_id
    $has_order_id = false;
    foreach ($columns as $col) {
        if ($col['Field'] == 'order_id') {
            $has_order_id = true;
            break;
        }
    }
    
    if (!$has_order_id) {
        echo "🔧 Agregando columna 'order_id'...<br>";
        $pdo->exec("ALTER TABLE orders ADD COLUMN order_id VARCHAR(100) NOT NULL AFTER id");
        echo "✅ Columna 'order_id' agregada<br>";
    } else {
        echo "✅ Columna 'order_id' ya existe<br>";
    }
    
    // Verificar otras columnas necesarias
    $required_columns = [
        'customer_name' => 'VARCHAR(255)',
        'customer_email' => 'VARCHAR(255)', 
        'order_status' => 'VARCHAR(50)',
        'order_value' => 'DECIMAL(10,2)',
        'image_url' => 'TEXT'
    ];
    
    $current_columns = array_column($columns, 'Field');
    
    foreach ($required_columns as $col_name => $col_type) {
        if (!in_array($col_name, $current_columns)) {
            echo "🔧 Agregando columna '$col_name'...<br>";
            $pdo->exec("ALTER TABLE orders ADD COLUMN $col_name $col_type");
            echo "✅ Columna '$col_name' agregada<br>";
        } else {
            echo "✅ Columna '$col_name' ya existe<br>";
        }
    }
    
    echo "<h3>Insertando datos de prueba:</h3>";
    
    // Insertar datos de prueba
    $stmt = $pdo->prepare("
        INSERT INTO orders (order_id, customer_email, customer_name, order_status, order_value, image_url, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $test_orders = [
        ['LEGO001', 'juan.perez@email.com', 'Juan Pérez', 'paid', 49.99, 'https://example.com/lego1.jpg'],
        ['LEGO002', 'maria.garcia@email.com', 'María García', 'paid', 67.50, 'https://example.com/lego2.jpg'],
        ['LEGO003', 'carlos.lopez@email.com', 'Carlos López', 'pending', 25.25, 'https://example.com/lego3.jpg'],
        ['LEGO004', 'ana.martin@email.com', 'Ana Martín', 'paid', 89.99, 'https://example.com/lego4.jpg'],
        ['LEGO005', 'luis.rodriguez@email.com', 'Luis Rodríguez', 'paid', 124.75, 'https://example.com/lego5.jpg']
    ];
    
    foreach ($test_orders as $order) {
        $stmt->execute($order);
        echo "✅ Insertado: {$order[0]} - {$order[2]} - €{$order[4]}<br>";
    }
    
    // Verificar inserción
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $total = $stmt->fetch()['total'];
    echo "<br><strong>📊 Total de pedidos: $total</strong><br>";
    
    // Mostrar resumen
    $stmt = $pdo->query("SELECT order_status, COUNT(*) as count, SUM(order_value) as total FROM orders GROUP BY order_status");
    $stats = $stmt->fetchAll();
    
    echo "<h4>📈 Resumen:</h4>";
    foreach ($stats as $stat) {
        echo "- {$stat['order_status']}: {$stat['count']} pedidos, €{$stat['total']}<br>";
    }
    
    echo "<br>🎉 <strong>¡Tabla corregida y datos insertados!</strong><br>";
    echo '<br><a href="orders.php?action=stats">🔗 Probar API Stats</a><br>';
    echo '<a href="orders.php?action=list">🔗 Probar API List</a><br>';
    echo '<a href="index.php">🔗 Ir al Dashboard</a><br>';
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
