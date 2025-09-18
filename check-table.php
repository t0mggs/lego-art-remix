<?php
$pdo = new PDO('mysql:host=localhost;dbname=visubloq_db', 'root', 'admin');
$stmt = $pdo->query('DESCRIBE order_pieces');
echo "ESTRUCTURA order_pieces:\n";
while($row = $stmt->fetch()) { 
    echo $row['Field'] . " - " . $row['Type'] . "\n"; 
}
?>
