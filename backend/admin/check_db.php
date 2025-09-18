<?php
try {
    require_once '../config.php';
    $pdo = getDatabase();
    
    echo "<h2>Verificación de Estructura de BD</h2>";
    
    // Verificar estructura de system_logs
    $stmt = $pdo->query("DESCRIBE system_logs");
    $columns = $stmt->fetchAll();
    
    echo "<h3>Columnas en system_logs:</h3>";
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})<br>";
    }
    
    // Verificar función logMessage
    echo "<h3>Test de logMessage:</h3>";
    echo "Intentando logear un mensaje...<br>";
    
    // Test simple sin usar logMessage
    $stmt = $pdo->prepare("INSERT INTO system_logs (message, created_at) VALUES (?, NOW())");
    $stmt->execute(['Test message']);
    echo "✅ Log insertado directamente<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
