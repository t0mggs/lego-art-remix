<?php
echo "<h2>🔧 Arreglar TODOS los campos obligatorios</h2>";

try {
    $pdo = new PDO(
        "mysql:host=localhost;port=3306;dbname=visubloq_db;charset=utf8mb4",
        'root',
        'admin'
    );
    
    echo "✅ Conexión exitosa<br>";
    
    echo "<h3>1. Viendo estructura actual completa:</h3>";
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $col) {
        $nullable = $col['Null'] == 'YES' ? '✅ NULL' : '❌ NOT NULL';
        $default = $col['Default'] ? "Default: {$col['Default']}" : 'Sin default';
        echo "- <strong>{$col['Field']}</strong> ({$col['Type']}) - $nullable - $default<br>";
    }
    
    echo "<h3>2. Haciendo que los campos problemáticos permitan NULL:</h3>";
    
    // Lista de campos que pueden causar problemas
    $fields_to_fix = [
        'shopify_order_id',
        'order_number', 
        'customer_name',
        'customer_email',
        'order_status',
        'order_value',
        'image_url'
    ];
    
    foreach ($fields_to_fix as $field) {
        try {
            // Verificar si el campo existe
            $field_exists = false;
            foreach ($columns as $col) {
                if ($col['Field'] == $field) {
                    $field_exists = true;
                    break;
                }
            }
            
            if ($field_exists) {
                $pdo->exec("ALTER TABLE orders MODIFY COLUMN `$field` VARCHAR(255) NULL");
                echo "✅ Campo '$field' ahora permite NULL<br>";
            } else {
                echo "ℹ️ Campo '$field' no existe<br>";
            }
        } catch (Exception $e) {
            echo "⚠️ No se pudo modificar '$field': " . $e->getMessage() . "<br>";
        }
    }
    
    // Caso especial para order_value (debe ser DECIMAL)
    try {
        $pdo->exec("ALTER TABLE orders MODIFY COLUMN `order_value` DECIMAL(10,2) NULL");
        echo "✅ Campo 'order_value' configurado como DECIMAL NULL<br>";
    } catch (Exception $e) {
        echo "⚠️ No se pudo modificar 'order_value': " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>3. Estructura después de las correcciones:</h3>";
    $stmt = $pdo->query("DESCRIBE orders");
    $updated_columns = $stmt->fetchAll();
    
    foreach ($updated_columns as $col) {
        $nullable = $col['Null'] == 'YES' ? '✅ NULL' : '❌ NOT NULL';
        echo "- {$col['Field']} - $nullable<br>";
    }
    
    echo "<br>✅ <strong>Todos los campos corregidos!</strong><br>";
    echo '<a href="insert_test_orders_v2.php">➡️ Continuar: Insertar datos (versión corregida)</a><br>';
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
