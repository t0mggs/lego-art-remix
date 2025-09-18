<?php
echo "<h2>🔍 Diagnóstico Completo de Conexión BD</h2>";

echo "<h3>1. Configuración actual:</h3>";
echo "DB_HOST: localhost<br>";
echo "DB_NAME: visubloq_db<br>";
echo "DB_USER: root<br>";
echo "DB_PASS: admin<br>";

echo "<h3>2. Test de conexiones:</h3>";

// Test 1: Sin contraseña (configuración normal de XAMPP)
echo "<h4>Test 1: Conexión sin contraseña</h4>";
try {
    $pdo1 = new PDO(
        "mysql:host=localhost;dbname=visubloq_db;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    echo "✅ Conexión SIN contraseña: EXITOSA<br>";
    
    // Test de datos
    $stmt = $pdo1->query("SELECT COUNT(*) as total FROM orders");
    $count = $stmt->fetch()['total'];
    echo "📊 Pedidos encontrados (sin password): $count<br>";
    
} catch (Exception $e) {
    echo "❌ Conexión sin contraseña falló: " . $e->getMessage() . "<br>";
}

// Test 2: Con contraseña 'admin'
echo "<h4>Test 2: Conexión con contraseña 'admin'</h4>";
try {
    $pdo2 = new PDO(
        "mysql:host=localhost;dbname=visubloq_db;charset=utf8mb4",
        'root',
        'admin',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    echo "✅ Conexión CON contraseña 'admin': EXITOSA<br>";
    
    // Test de datos
    $stmt = $pdo2->query("SELECT COUNT(*) as total FROM orders");
    $count = $stmt->fetch()['total'];
    echo "📊 Pedidos encontrados (con password): $count<br>";
    
} catch (Exception $e) {
    echo "❌ Conexión con contraseña 'admin' falló: " . $e->getMessage() . "<br>";
}

// Test 3: Probar con la función del config actual
echo "<h4>Test 3: Usando función getDatabase() actual</h4>";
try {
    require_once '../config.php';
    $pdo3 = getDatabase();
    echo "✅ Función getDatabase(): EXITOSA<br>";
    
    // Test de datos
    $stmt = $pdo3->query("SELECT COUNT(*) as total FROM orders");
    $count = $stmt->fetch()['total'];
    echo "📊 Pedidos encontrados (getDatabase): $count<br>";
    
    // Mostrar algunos pedidos
    if ($count > 0) {
        $stmt = $pdo3->query("SELECT * FROM orders LIMIT 3");
        $orders = $stmt->fetchAll();
        echo "<h5>Primeros pedidos:</h5>";
        foreach ($orders as $order) {
            echo "- ID: {$order['id']}, Order: {$order['order_id']}, Status: {$order['order_status']}, Valor: €{$order['order_value']}<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Función getDatabase() falló: " . $e->getMessage() . "<br>";
}

echo "<h3>3. Test directo de MySQL:</h3>";
echo "Probando conexión directa a MySQL...<br>";

// Verificar que MySQL esté corriendo
$connection = @mysqli_connect('localhost', 'root', '');
if ($connection) {
    echo "✅ MySQL está corriendo (sin contraseña)<br>";
    mysqli_close($connection);
} else {
    echo "❌ No se puede conectar a MySQL sin contraseña<br>";
}

$connection = @mysqli_connect('localhost', 'root', 'admin');
if ($connection) {
    echo "✅ MySQL está corriendo (con contraseña 'admin')<br>";
    mysqli_close($connection);
} else {
    echo "❌ No se puede conectar a MySQL con contraseña 'admin'<br>";
}
?>
