<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VisuBloq - Test de Sistema</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border-color: #b6d4d9; color: #0c5460; }
        h1 { color: #333; text-align: center; }
        h2 { color: #666; }
        .step { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧱 VisuBloq - Verificación del Sistema</h1>
        
        <div class="test-section info">
            <h2>📋 Estado Actual del Sistema</h2>
            <p><strong>¡El sistema backend está completo y listo!</strong></p>
            <p>Todos los archivos necesarios han sido creados:</p>
            <ul>
                <li>✅ Panel de administración: <code>backend/admin/dashboard.php</code></li>
                <li>✅ API de pedidos: <code>backend/admin/orders.php</code></li>
                <li>✅ Webhook de Shopify: <code>backend/api/shopify-webhook.php</code></li>
                <li>✅ Guardado de datos: <code>backend/api/save-design-data.php</code></li>
                <li>✅ Generación de PDFs: <code>backend/api/generate-pdf.php</code></li>
                <li>✅ Base de datos: <code>backend/database_structure.sql</code></li>
            </ul>
        </div>

        <?php
        // Verificar conexión a base de datos
        try {
            if (file_exists('backend/config.php')) {
                require_once 'backend/config.php';
                $pdo = getDatabase();
                echo '<div class="test-section success">';
                echo '<h2>✅ Conexión a Base de Datos</h2>';
                echo '<p>Conexión exitosa a la base de datos: ' . DB_NAME . '</p>';
                echo '</div>';
                
                // Verificar tablas
                $tables = ['orders', 'order_pieces', 'order_pdfs', 'admin_users', 'system_logs'];
                $existingTables = [];
                foreach ($tables as $table) {
                    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() > 0) {
                        $existingTables[] = $table;
                    }
                }
                
                if (count($existingTables) === count($tables)) {
                    echo '<div class="test-section success">';
                    echo '<h2>✅ Estructura de Base de Datos</h2>';
                    echo '<p>Todas las tablas necesarias están creadas:</p>';
                    echo '<ul>';
                    foreach ($existingTables as $table) {
                        echo '<li>✅ ' . $table . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                } else {
                    echo '<div class="test-section error">';
                    echo '<h2>❌ Estructura de Base de Datos</h2>';
                    echo '<p>Faltan algunas tablas. Ejecuta el archivo database_structure.sql</p>';
                    echo '</div>';
                }
                
            } else {
                echo '<div class="test-section error">';
                echo '<h2>❌ Configuración</h2>';
                echo '<p>Archivo config.php no encontrado. Copia config.example.php como config.php</p>';
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<div class="test-section error">';
            echo '<h2>❌ Error de Conexión</h2>';
            echo '<p>Error: ' . $e->getMessage() . '</p>';
            echo '<p>Verifica la configuración de la base de datos en config.php</p>';
            echo '</div>';
        }
        ?>

        <div class="test-section info">
            <h2>🚀 Próximos Pasos</h2>
            <div class="step">
                <strong>1. Configurar Base de Datos:</strong>
                <code>mysql -u root -p visubloq_db < backend/database_structure.sql</code>
            </div>
            <div class="step">
                <strong>2. Personalizar config.php:</strong>
                Edita <code>backend/config.php</code> con tus datos reales
            </div>
            <div class="step">
                <strong>3. Configurar Webhook en Shopify:</strong>
                URL: <code>https://visubloq.com/backend/api/shopify-webhook.php</code>
            </div>
            <div class="step">
                <strong>4. Acceder al Panel:</strong>
                <a href="backend/admin/dashboard.php" target="_blank">https://visubloq.com/backend/admin/dashboard.php</a>
            </div>
        </div>

        <div class="test-section success">
            <h2>🎯 ¿Qué conseguirás?</h2>
            <p><strong>Panel de admin que muestra para cada pedido:</strong></p>
            <ul>
                <li>📊 Total de piezas necesarias</li>
                <li>🎨 Número de colores diferentes</li>
                <li>🧩 Lista detallada: color → cantidad</li>
                <li>📈 Porcentajes de cada color</li>
                <li>📄 PDFs de instrucciones descargables</li>
            </ul>
            <p><em>¡Todo automático! Solo necesitas configurar 1 webhook en Shopify.</em></p>
        </div>
    </div>
</body>
</html>
