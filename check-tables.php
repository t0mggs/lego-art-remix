<?php
require_once 'backend/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    
    echo "📋 ESTRUCTURA DE TABLAS:\n\n";
    
    // Ver estructura de design_images
    echo "design_images:\n";
    $stmt = $pdo->query('DESCRIBE design_images');
    while ($row = $stmt->fetch()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\nimage_pieces:\n";
    $stmt = $pdo->query('DESCRIBE image_pieces');
    while ($row = $stmt->fetch()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>