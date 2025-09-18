<?php
// Verificar tablas en visubloq_admin
try {
    $dsn = "mysql:host=localhost;dbname=visubloq_admin;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', 'admin', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ CONECTADO a visubloq_admin\n\n";
    
    // Mostrar todas las tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 TABLAS ENCONTRADAS: " . count($tables) . "\n";
    if (count($tables) > 0) {
        foreach($tables as $table) {
            echo "  - $table\n";
        }
    } else {
        echo "❌ NO HAY TABLAS en visubloq_admin\n\n";
        
        // Verificar si están en otra base de datos
        echo "🔍 BUSCANDO EN OTRAS BASES DE DATOS...\n";
        $stmt = $pdo->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Bases de datos disponibles:\n";
        foreach($databases as $db) {
            echo "  - $db\n";
            
            // Buscar tablas que empiecen con 'order' o contengan 'admin'
            try {
                $pdo->exec("USE $db");
                $stmt = $pdo->query("SHOW TABLES LIKE '%order%'");
                $orderTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                if (count($orderTables) > 0) {
                    echo "    └─ Tablas relacionadas: " . implode(', ', $orderTables) . "\n";
                }
            } catch(Exception $e) {
                // Ignorar errores de acceso
            }
        }
    }
    
} catch(PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
