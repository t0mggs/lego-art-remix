<?php
require_once 'backend/config.php';

$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);

// Añadir diseño para Carlos López
$stmt = $pdo->prepare("
    INSERT INTO design_images 
    (design_id, session_id, order_id, width, height, pieces_data, pdf_blob, visubloq_config, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    'VB-1758000004-CARLOS', 
    'SESS-004', 
    3, 
    40, 
    40, 
    '{"totalPieces": 60}', 
    'PDF_DATA_CARLOS', 
    '{"width": 40}', 
    'purchased'
]);

echo "<h3>✅ Diseño añadido para Carlos López</h3>";
echo "<p><a href='backend/admin/orders-dashboard.php' target='_blank'>📊 Ver Dashboard</a></p>";
?>