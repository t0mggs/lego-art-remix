<?php
// Verificar todas las tablas necesarias para el dashboard
try {
    $dsn = "mysql:host=localhost;dbname=visubloq_db;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', 'admin', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "📋 VERIFICANDO ESTRUCTURA DE BASE DE DATOS:\n\n";
    
    // Tablas requeridas
    $required_tables = [
        'orders',
        'order_pieces', 
        'order_pdfs',
        'admin_users',
        'system_logs',
        'usage_stats'
    ];
    
    // Verificar cada tabla
    foreach($required_tables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll();
            echo "✅ $table (" . count($columns) . " columnas)\n";
            
            // Mostrar cantidad de registros
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "   └─ Registros: $count\n";
            
        } catch(Exception $e) {
            echo "❌ $table - ERROR: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🔍 CREANDO DATOS DE PRUEBA...\n";
    
    // Insertar usuario admin si no existe
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO admin_users (username, password_hash, email, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin@visubloq.com']);
        echo "✅ Usuario admin creado\n";
    } catch(Exception $e) {
        echo "ℹ️ Usuario admin ya existe\n";
    }
    
    // Insertar pedido de prueba si no hay pedidos
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $order_count = $stmt->fetch()['count'];
    
    if ($order_count == 0) {
        echo "📦 Creando pedido de prueba...\n";
        $stmt = $pdo->prepare("INSERT INTO orders (shopify_order_id, order_number, customer_name, customer_email, order_value, order_status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([999999, 'TEST-001', 'Cliente Prueba', 'test@example.com', 49.99, 'pending']);
        
        $order_id = $pdo->lastInsertId();
        
        // Agregar piezas del pedido
        $stmt = $pdo->prepare("INSERT INTO order_pieces (order_id, piece_type, piece_color, quantity, position_x, position_y) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, '1x1 brick', 'red', 10, 1, 1]);
        $stmt->execute([$order_id, '2x2 brick', 'blue', 5, 2, 2]);
        
        echo "✅ Pedido de prueba creado\n";
    }
    
    echo "\n🎯 RESUMEN FINAL:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $total_orders = $stmt->fetch()['count'];
    echo "📦 Total pedidos: $total_orders\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM order_pdfs");
    $total_pdfs = $stmt->fetch()['count'];
    echo "📄 Total PDFs: $total_pdfs\n";
    
} catch(PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
