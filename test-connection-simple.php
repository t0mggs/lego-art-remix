<?php
// Test simple de conexión MySQL
try {
    $dsn = "mysql:host=localhost;dbname=visubloq_admin;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', 'admin', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ CONEXIÓN EXITOSA a visubloq_admin\n";
    
    // Verificar tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 TABLAS ENCONTRADAS (" . count($tables) . "):\n";
    foreach($tables as $table) {
        echo "  - $table\n";
    }
    
} catch(PDOException $e) {
    echo "❌ ERROR DE CONEXIÓN: " . $e->getMessage() . "\n";
}
?>
