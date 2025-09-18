<?php
/**
 * Script de Configuración Inicial
 * Ejecutar una sola vez para configurar el sistema
 */

require_once 'config.php';

echo "<h1>🚀 Configuración Inicial de VisuBloq</h1>";

try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Conexión a base de datos exitosa</p>";
    
    // Crear tabla de administradores si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "<p>✅ Tabla de administradores creada</p>";
    
    // Crear usuario admin por defecto
    $username = 'admin';
    $password = 'visubloq2025';  // Cámbiala después
    $email = 'admin@visubloq.com';
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Verificar si ya existe
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash, email) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password_hash, $email]);
        
        echo "<p>✅ Usuario administrador creado:</p>";
        echo "<ul>";
        echo "<li><strong>Usuario:</strong> $username</li>";
        echo "<li><strong>Contraseña:</strong> $password</li>";
        echo "<li><strong>Email:</strong> $email</li>";
        echo "</ul>";
        echo "<p><strong>⚠️ IMPORTANTE:</strong> Cambia la contraseña después del primer login</p>";
    } else {
        echo "<p>ℹ️ Usuario administrador ya existe</p>";
    }
    
    // Verificar tablas principales
    $tables = ['orders', 'design_images', 'order_pieces', 'image_pieces'];
    echo "<h3>📋 Verificación de Tablas</h3>";
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Tabla '$table' existe</p>";
        } else {
            echo "<p>❌ Tabla '$table' NO existe - revisa database_structure.sql</p>";
        }
    }
    
    echo "<h3>🎯 URLs para Acceder al Sistema</h3>";
    echo "<ul>";
    echo "<li><strong>Aplicación Principal:</strong> <a href='http://localhost/VisuBloq/app/index.html' target='_blank'>http://localhost/VisuBloq/app/index.html</a></li>";
    echo "<li><strong>Login Admin:</strong> <a href='http://localhost/VisuBloq/app/backend/admin/login.php' target='_blank'>http://localhost/VisuBloq/app/backend/admin/login.php</a></li>";
    echo "<li><strong>Dashboard Admin:</strong> <a href='http://localhost/VisuBloq/app/backend/admin/orders-dashboard.php' target='_blank'>http://localhost/VisuBloq/app/backend/admin/orders-dashboard.php</a></li>";
    echo "<li><strong>phpMyAdmin:</strong> <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    echo "</ul>";
    
    echo "<h3>✅ Configuración Completada</h3>";
    echo "<p><strong>Siguiente paso:</strong> Inicia sesión en el panel de administración</p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Error de base de datos: " . $e->getMessage() . "</p>";
    echo "<p><strong>Soluciones:</strong></p>";
    echo "<ul>";
    echo "<li>Verificar que MySQL esté corriendo en XAMPP</li>";
    echo "<li>Verificar que la base de datos 'visubloq_db' existe</li>";
    echo "<li>Verificar credenciales en config.php</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { color: #333; }
h3 { color: #666; margin-top: 30px; }
p { margin: 10px 0; }
ul { margin: 10px 0; padding-left: 20px; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>