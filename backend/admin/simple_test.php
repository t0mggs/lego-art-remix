<?php
// Test super simple de conexión
echo "<h2>Test Simple de Conexión</h2>";

echo "<h3>Configuración actual:</h3>";
echo "Host: localhost<br>";
echo "Puerto: 3306<br>";
echo "Usuario: root<br>";
echo "Contraseña: admin<br>";
echo "Base de datos: visubloq_db<br>";

echo "<h3>Test de conexión:</h3>";
try {
    $pdo = new PDO(
        "mysql:host=localhost;port=3306;dbname=visubloq_db;charset=utf8mb4",
        'root',
        'admin'
    );
    echo "✅ Conexión exitosa<br>";
    
    // Test básico
    $result = $pdo->query("SELECT 1 as test")->fetch();
    echo "✅ Query test: " . $result['test'] . "<br>";
    
    // Verificar base de datos
    $result = $pdo->query("SELECT DATABASE() as current_db")->fetch();
    echo "✅ Base de datos actual: " . $result['current_db'] . "<br>";
    
    // Contar pedidos
    $result = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch();
    echo "📊 Total de pedidos: " . $result['count'] . "<br>";
    
    if ($result['count'] == 0) {
        echo "<br>🔧 <strong>Insertando 1 pedido de prueba...</strong><br>";
        $pdo->exec("INSERT INTO orders (order_id, customer_email, customer_name, order_status, order_value, image_url, created_at) VALUES ('SIMPLE_TEST', 'test@example.com', 'Test User', 'paid', 99.99, 'test.jpg', NOW())");
        
        $result = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch();
        echo "✅ Nuevo total: " . $result['count'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Código de error: " . $e->getCode() . "<br>";
}
?>
