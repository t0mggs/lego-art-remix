<?php
/**
 * Test simple de conexión a base de datos
 */

require_once 'backend/config.php';

echo "<h1>🔍 Verificación de Sistema Local</h1>";

// Test 1: Conexión a base de datos
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ <strong>Conexión a BD:</strong> OK</p>";
    
    // Test 2: Verificar tablas
    $tables = ['design_images', 'orders', 'order_pieces', 'admin_users'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p>✅ <strong>Tabla '$table':</strong> OK ($count registros)</p>";
        } catch (Exception $e) {
            echo "<p>❌ <strong>Tabla '$table':</strong> Error - " . $e->getMessage() . "</p>";
        }
    }
    
    // Test 3: Insertar datos de prueba
    echo "<h3>📊 Insertando datos de prueba...</h3>";
    
    // Insertar una imagen de prueba
    $stmt = $pdo->prepare("INSERT INTO design_images (session_id, config, pdf_blob, created_at) VALUES (?, ?, ?, NOW())");
    $test_config = json_encode(['test' => true, 'source' => 'test local']);
    $test_blob = 'test_pdf_data_' . time();
    $test_session = 'TEST-' . time() . '-LOCAL';
    
    $stmt->execute([$test_session, $test_config, $test_blob]);
    echo "<p>✅ <strong>Imagen de prueba insertada:</strong> ID " . $pdo->lastInsertId() . "</p>";
    echo "<p>📋 <strong>Código de prueba:</strong> $test_session</p>";
    
} catch (PDOException $e) {
    echo "<p>❌ <strong>Error de BD:</strong> " . $e->getMessage() . "</p>";
}

// Test 4: URLs del sistema
echo "<h3>🌐 URLs del Sistema</h3>";
echo "<ul>";
echo "<li><a href='index.html' target='_blank'>📱 Aplicación Principal</a></li>";
echo "<li><a href='backend/admin/orders-dashboard.php' target='_blank'>📊 Dashboard</a></li>";
echo "<li><a href='http://localhost/phpmyadmin' target='_blank'>🗄️ phpMyAdmin</a></li>";
echo "</ul>";

?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
h1, h3 { color: #333; }
p { margin: 10px 0; }
ul { margin: 10px 0; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>