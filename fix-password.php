<?php
/**
 * Script para resetear la contraseña del administrador
 */

require_once 'backend/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Contraseña que quieres usar
    $username = 'admin';
    $password = 'visubloq2025';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Actualizar el usuario admin
    $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE username = ?");
    $result = $stmt->execute([$password_hash, $username]);
    
    if ($result) {
        echo "<h1>✅ Contraseña Actualizada</h1>";
        echo "<p><strong>Usuario:</strong> admin</p>";
        echo "<p><strong>Contraseña:</strong> visubloq2025</p>";
        echo "<p><a href='backend/admin/login.php'>🔑 Ir al Login</a></p>";
    } else {
        echo "<h1>❌ Error al actualizar</h1>";
    }
    
} catch (PDOException $e) {
    echo "<h1>❌ Error de Base de Datos</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
h1 { color: #333; }
p { margin: 15px 0; font-size: 18px; }
a { color: #007bff; text-decoration: none; font-weight: bold; }
a:hover { text-decoration: underline; }
</style>