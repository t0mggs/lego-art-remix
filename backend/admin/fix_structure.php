<?php
echo "<h2>🔧 Arreglar Estructura de Base de Datos</h2>";

try {
    $pdo = new PDO(
        "mysql:host=localhost;port=3306;dbname=visubloq_db;charset=utf8mb4",
        'root',
        'admin'
    );
    
    echo "✅ Conexión exitosa<br>";
    
    echo "<h3>1. Arreglando columna shopify_order_id:</h3>";
    
    // Hacer que shopify_order_id permita NULL o tenga un valor por defecto
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN shopify_order_id VARCHAR(255) NULL");
    echo "✅ shopify_order_id ahora permite NULL<br>";
    
    echo "<h3>2. Verificando estructura actual:</h3>";
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll();
    
    $existing_columns = [];
    foreach ($columns as $col) {
        $existing_columns[] = $col['Field'];
        echo "- {$col['Field']} ({$col['Type']}) " . ($col['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . "<br>";
    }
    
    echo "<h3>3. Agregando columnas faltantes si es necesario:</h3>";
    
    // Columnas que necesitamos
    $required_columns = [
        'order_id' => 'VARCHAR(100)',
        'customer_name' => 'VARCHAR(255)',
        'customer_email' => 'VARCHAR(255)', 
        'order_status' => 'VARCHAR(50)',
        'order_value' => 'DECIMAL(10,2)',
        'image_url' => 'TEXT'
    ];
    
    foreach ($required_columns as $col_name => $col_type) {
        if (!in_array($col_name, $existing_columns)) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN $col_name $col_type");
            echo "✅ Agregada columna: $col_name<br>";
        } else {
            echo "ℹ️ Columna $col_name ya existe<br>";
        }
    }
    
    echo "<h3>4. Estructura final:</h3>";
    $stmt = $pdo->query("DESCRIBE orders");
    $final_columns = $stmt->fetchAll();
    
    foreach ($final_columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})<br>";
    }
    
    echo "<br>✅ <strong>Estructura de tabla corregida!</strong><br>";
    echo '<a href="insert_test_orders.php">➡️ Continuar: Insertar datos de prueba</a><br>';
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
