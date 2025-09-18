<?php
/**
 * Insertar datos de prueba con estructura correcta
 */

require_once 'backend/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>📊 Insertando Datos de Prueba</h1>";
    
    // Insertar diseño de prueba
    $test_design_id = 'VB-' . time() . '-TEST';
    $test_session_id = 'SESSION-' . time();
    $test_config = json_encode([
        'imageWidth' => 32,
        'imageHeight' => 32,
        'studSize' => 8,
        'testMode' => true,
        'source' => 'local testing'
    ]);
    $test_pieces_data = json_encode([
        'totalPieces' => 42,
        'uniqueColors' => 4,
        'studMap' => array_fill(0, 32, array_fill(0, 32, 'red'))
    ]);
    $test_blob = base64_encode('TEST PDF DATA ' . time());
    
    $stmt = $pdo->prepare("
        INSERT INTO design_images 
        (design_id, session_id, width, height, pieces_data, pdf_blob, visubloq_config, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $test_design_id, 
        $test_session_id, 
        32, 
        32, 
        $test_pieces_data, 
        $test_blob, 
        $test_config, 
        'generated'
    ]);
    
    echo "<p>✅ <strong>Diseño insertado:</strong> $test_design_id</p>";
    
    // Insertar piezas de colores
    $colors = [
        ['Red', '#FF0000', 15],
        ['Blue', '#0000FF', 12],
        ['Green', '#00FF00', 8],
        ['Yellow', '#FFFF00', 7]
    ];
    
    foreach ($colors as $color) {
        $stmt = $pdo->prepare("
            INSERT INTO image_pieces 
            (design_id, color_name, color_code, piece_count) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$test_design_id, $color[0], $color[1], $color[2]]);
    }
    
    echo "<p>✅ <strong>Piezas insertadas:</strong> " . count($colors) . " colores</p>";
    
    // Verificar datos
    $stmt = $pdo->query("SELECT COUNT(*) FROM design_images");
    $design_count = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM image_pieces");
    $pieces_count = $stmt->fetchColumn();
    
    echo "<h3>📈 Resumen</h3>";
    echo "<p>🎨 <strong>Diseños:</strong> $design_count</p>";
    echo "<p>🧩 <strong>Piezas:</strong> $pieces_count</p>";
    echo "<p>🔢 <strong>ID de prueba:</strong> $test_design_id</p>";
    
    echo "<h3>🎯 Ahora ve al Dashboard</h3>";
    echo "<p><a href='backend/admin/orders-dashboard.php' target='_blank' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 18px;'>📊 VER DASHBOARD CON DATOS</a></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
h1, h3 { color: #333; }
p { margin: 15px 0; }
a { text-decoration: none; }
</style>