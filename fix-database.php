<?php
/**
 * Script para arreglar y crear las tablas necesarias
 */

require_once 'backend/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🔧 Arreglando Estructura de Base de Datos</h1>";
    
    // Crear tabla design_images si no existe
    $sql_design_images = "
    CREATE TABLE IF NOT EXISTS design_images (
        id INT PRIMARY KEY AUTO_INCREMENT,
        session_id VARCHAR(100) UNIQUE NOT NULL,
        image_config JSON,
        pdf_blob LONGTEXT,
        pdf_filename VARCHAR(255),
        pieces_data JSON,
        total_pieces INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        shopify_order_id BIGINT NULL,
        customer_info JSON NULL
    )";
    
    $pdo->exec($sql_design_images);
    echo "<p>✅ <strong>Tabla 'design_images':</strong> Creada/Actualizada</p>";
    
    // Crear tabla image_pieces si no existe
    $sql_image_pieces = "
    CREATE TABLE IF NOT EXISTS image_pieces (
        id INT PRIMARY KEY AUTO_INCREMENT,
        design_id INT NOT NULL,
        color_name VARCHAR(50) NOT NULL,
        color_code VARCHAR(20) NOT NULL,
        piece_count INT NOT NULL,
        piece_positions JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (design_id) REFERENCES design_images(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql_image_pieces);
    echo "<p>✅ <strong>Tabla 'image_pieces':</strong> Creada/Actualizada</p>";
    
    // Insertar datos de prueba
    echo "<h3>📊 Insertando datos de prueba...</h3>";
    
    $test_session = 'VB-' . time() . '-TEST';
    $test_config = json_encode([
        'imageWidth' => 32,
        'imageHeight' => 32,
        'studSize' => 8,
        'testMode' => true,
        'source' => 'local testing'
    ]);
    $test_blob = 'data:application/pdf;base64,JVBERi0xLjQKMSAwIG9iag==' . base64_encode('TEST PDF DATA ' . time());
    
    $stmt = $pdo->prepare("INSERT INTO design_images (session_id, image_config, pdf_blob, total_pieces) VALUES (?, ?, ?, ?)");
    $stmt->execute([$test_session, $test_config, $test_blob, 42]);
    
    $design_id = $pdo->lastInsertId();
    echo "<p>✅ <strong>Diseño de prueba insertado:</strong> ID $design_id</p>";
    echo "<p>🔢 <strong>Código de tracking:</strong> $test_session</p>";
    
    // Insertar piezas de prueba
    $colors = [
        ['Red', '#FF0000', 15],
        ['Blue', '#0000FF', 12],
        ['Green', '#00FF00', 8],
        ['Yellow', '#FFFF00', 7]
    ];
    
    foreach ($colors as $color) {
        $stmt = $pdo->prepare("INSERT INTO image_pieces (design_id, color_name, color_code, piece_count) VALUES (?, ?, ?, ?)");
        $stmt->execute([$design_id, $color[0], $color[1], $color[2]]);
    }
    
    echo "<p>✅ <strong>Piezas de colores insertadas:</strong> " . count($colors) . " colores</p>";
    
    // Verificar datos
    $stmt = $pdo->query("SELECT COUNT(*) FROM design_images");
    $design_count = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM image_pieces");
    $pieces_count = $stmt->fetchColumn();
    
    echo "<h3>📈 Resumen Final</h3>";
    echo "<p>📱 <strong>Diseños totales:</strong> $design_count</p>";
    echo "<p>🧩 <strong>Registros de piezas:</strong> $pieces_count</p>";
    
    echo "<h3>🎯 Siguiente Paso</h3>";
    echo "<p><a href='backend/admin/orders-dashboard.php' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 VER DASHBOARD</a></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
h1, h3 { color: #333; }
p { margin: 10px 0; }
a { color: #007bff; text-decoration: none; }
</style>