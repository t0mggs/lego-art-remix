<?php
echo "<h1>🔍 Diagnóstico Completo - MySQL Local + XAMPP</h1>";

echo "<h2>1. Test de Conexión Directa a MySQL Local</h2>";

// Test directo sin config.php
try {
    $pdo_direct = new PDO(
        "mysql:host=localhost;port=3306;dbname=visubloq_db;charset=utf8mb4",
        'root',
        'admin',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✅ <strong>Conexión directa a MySQL local: EXITOSA</strong><br>";
    
    // Verificar si la base de datos existe
    $stmt = $pdo_direct->query("SHOW DATABASES LIKE 'visubloq_db'");
    $db_exists = $stmt->rowCount() > 0;
    echo "📊 Base de datos 'visubloq_db' existe: " . ($db_exists ? "✅ SÍ" : "❌ NO") . "<br>";
    
    if ($db_exists) {
        // Verificar tablas
        $stmt = $pdo_direct->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "📋 Tablas encontradas: " . implode(', ', $tables) . "<br>";
        
        // Verificar datos en orders
        if (in_array('orders', $tables)) {
            $stmt = $pdo_direct->query("SELECT COUNT(*) as total FROM orders");
            $count = $stmt->fetch()['total'];
            echo "📊 Pedidos en tabla 'orders': <strong>$count</strong><br>";
            
            if ($count > 0) {
                $stmt = $pdo_direct->query("SELECT * FROM orders LIMIT 3");
                $orders = $stmt->fetchAll();
                echo "<h4>Primeros 3 pedidos:</h4>";
                foreach ($orders as $order) {
                    echo "- ID: {$order['id']}, Order: {$order['order_id']}, Cliente: {$order['customer_name']}, Valor: €{$order['order_value']}<br>";
                }
            } else {
                echo "⚠️ <strong>NO HAY DATOS en la tabla orders</strong><br>";
            }
        } else {
            echo "❌ <strong>Tabla 'orders' NO EXISTE</strong><br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Error en conexión directa: " . $e->getMessage() . "</strong><br>";
}

echo "<hr>";
echo "<h2>2. Test usando config.php</h2>";

try {
    require_once '../config.php';
    $pdo_config = getDatabase();
    echo "✅ <strong>Conexión usando config.php: EXITOSA</strong><br>";
    
    $stmt = $pdo_config->query("SELECT COUNT(*) as total FROM orders");
    $count = $stmt->fetch()['total'];
    echo "📊 Pedidos via config.php: <strong>$count</strong><br>";
    
} catch (Exception $e) {
    echo "❌ <strong>Error con config.php: " . $e->getMessage() . "</strong><br>";
}

echo "<hr>";
echo "<h2>3. Insertar datos de prueba si no existen</h2>";

try {
    if (isset($pdo_direct)) {
        // Verificar si hay datos
        $stmt = $pdo_direct->query("SELECT COUNT(*) as total FROM orders");
        $current_count = $stmt->fetch()['total'];
        
        if ($current_count == 0) {
            echo "🔧 <strong>Insertando datos de prueba...</strong><br>";
            
            // Insertar datos
            $stmt = $pdo_direct->prepare("
                INSERT INTO orders (order_id, customer_email, customer_name, order_status, order_value, image_url, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $test_orders = [
                ['TEST001', 'juan@test.com', 'Juan Pérez', 'paid', 49.99, 'img1.jpg', '2025-08-07 10:00:00'],
                ['TEST002', 'maria@test.com', 'María García', 'paid', 67.50, 'img2.jpg', '2025-08-07 11:00:00'],
                ['TEST003', 'carlos@test.com', 'Carlos López', 'pending', 25.25, 'img3.jpg', '2025-08-07 12:00:00']
            ];
            
            foreach ($test_orders as $order) {
                $stmt->execute($order);
                echo "✅ Insertado: {$order[0]} - {$order[2]}<br>";
            }
            
            // Verificar inserción
            $stmt = $pdo_direct->query("SELECT COUNT(*) as total FROM orders");
            $new_count = $stmt->fetch()['total'];
            echo "📊 <strong>Total después de insertar: $new_count pedidos</strong><br>";
        } else {
            echo "ℹ️ Ya hay $current_count pedidos en la base de datos<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error insertando datos: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>4. Test de APIs</h2>";
echo '<a href="orders.php?action=stats" target="_blank">🔗 Test API Stats</a><br>';
echo '<a href="orders.php?action=list" target="_blank">🔗 Test API List</a><br>';
echo '<a href="index.php" target="_blank">🔗 Ir al Dashboard</a><br>';

echo "<hr>";
echo "<h2>5. Información del Sistema</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "MySQL Extensions: " . (extension_loaded('pdo_mysql') ? "✅ PDO_MySQL" : "❌ No PDO_MySQL") . "<br>";
echo "XAMPP Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
?>
