<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧱 VisuBloq - Test de Base de Datos</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f0f0f0; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        h1 { text-align: center; color: #333; }
        .test-result { padding: 10px; margin: 5px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧱 VisuBloq - Test de Base de Datos</h1>

        <?php
        $testsPassed = 0;
        $totalTests = 0;

        // TEST 1: Verificar archivo de configuración
        $totalTests++;
        echo '<h2>📝 Test 1: Archivo de Configuración</h2>';
        if (file_exists('backend/config.php')) {
            require_once 'backend/config.php';
            echo '<div class="success">✅ Archivo config.php encontrado</div>';
            $testsPassed++;
            
            // Mostrar configuración (sin datos sensibles)
            echo '<div class="info">';
            echo '<strong>Configuración actual:</strong><br>';
            echo '• Base de datos: ' . DB_NAME . '<br>';
            echo '• Host: ' . DB_HOST . '<br>';
            echo '• Usuario: ' . DB_USER . '<br>';
            echo '• URL base: ' . BASE_URL . '<br>';
            echo '</div>';
        } else {
            echo '<div class="error">❌ Archivo config.php no encontrado</div>';
        }

        // TEST 2: Conexión a base de datos
        $totalTests++;
        echo '<h2>🔌 Test 2: Conexión a Base de Datos</h2>';
        try {
            $pdo = getDatabase();
            echo '<div class="success">✅ Conexión exitosa a MySQL</div>';
            $testsPassed++;
            
            // Obtener información de la base de datos
            $stmt = $pdo->query("SELECT DATABASE() as current_db, VERSION() as mysql_version");
            $info = $stmt->fetch();
            echo '<div class="info">';
            echo '<strong>Información de la base de datos:</strong><br>';
            echo '• Base de datos activa: ' . $info['current_db'] . '<br>';
            echo '• Versión de MySQL: ' . $info['mysql_version'] . '<br>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">❌ Error de conexión: ' . $e->getMessage() . '</div>';
            echo '<div class="info">Verifica la contraseña en config.php</div>';
        }

        // TEST 3: Verificar tablas
        if (isset($pdo)) {
            $totalTests++;
            echo '<h2>📊 Test 3: Estructura de Tablas</h2>';
            
            $requiredTables = ['orders', 'order_pieces', 'order_pdfs', 'admin_users', 'system_logs', 'usage_stats'];
            $existingTables = [];
            
            foreach ($requiredTables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    $existingTables[] = $table;
                }
            }
            
            if (count($existingTables) === count($requiredTables)) {
                echo '<div class="success">✅ Todas las tablas necesarias están creadas</div>';
                $testsPassed++;
                
                // Mostrar información de las tablas
                echo '<table>';
                echo '<tr><th>Tabla</th><th>Registros</th><th>Estado</th></tr>';
                foreach ($existingTables as $table) {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                    $count = $stmt->fetch()['count'];
                    echo '<tr>';
                    echo '<td>' . $table . '</td>';
                    echo '<td>' . $count . '</td>';
                    echo '<td>✅ OK</td>';
                    echo '</tr>';
                }
                echo '</table>';
                
            } else {
                echo '<div class="error">❌ Faltan algunas tablas</div>';
                echo '<div class="info">Tablas encontradas: ' . implode(', ', $existingTables) . '</div>';
                echo '<div class="info">Ejecuta el archivo database_structure.sql en MySQL Workbench</div>';
            }
        }

        // TEST 4: Test básico de inserción
        if (isset($pdo) && count($existingTables) === count($requiredTables)) {
            $totalTests++;
            echo '<h2>🧪 Test 4: Test de Funcionalidad</h2>';
            
            try {
                // Insertar log de prueba
                $stmt = $pdo->prepare("INSERT INTO system_logs (log_type, message, data) VALUES (?, ?, ?)");
                $testData = json_encode(['test' => 'connection_test', 'timestamp' => time()]);
                $stmt->execute(['TEST', 'Sistema probado desde test-db.php', $testData]);
                
                echo '<div class="success">✅ Test de inserción exitoso</div>';
                $testsPassed++;
                
                // Leer el log insertado
                $stmt = $pdo->query("SELECT * FROM system_logs WHERE log_type = 'TEST' ORDER BY created_at DESC LIMIT 1");
                $log = $stmt->fetch();
                
                echo '<div class="info">';
                echo '<strong>Log de prueba insertado:</strong><br>';
                echo '• ID: ' . $log['id'] . '<br>';
                echo '• Tipo: ' . $log['log_type'] . '<br>';
                echo '• Mensaje: ' . $log['message'] . '<br>';
                echo '• Fecha: ' . $log['created_at'] . '<br>';
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<div class="error">❌ Error en test de inserción: ' . $e->getMessage() . '</div>';
            }
        }

        // RESUMEN FINAL
        echo '<h2>📋 Resumen de Tests</h2>';
        $percentage = round(($testsPassed / $totalTests) * 100);
        
        if ($testsPassed === $totalTests) {
            echo '<div class="success">';
            echo '<h3>🎉 ¡TODOS LOS TESTS PASARON! (' . $testsPassed . '/' . $totalTests . ')</h3>';
            echo '<p><strong>Tu sistema está listo para funcionar al 100%</strong></p>';
            echo '</div>';
            
            echo '<div class="info">';
            echo '<h3>🚀 Próximos Pasos:</h3>';
            echo '<ol>';
            echo '<li><strong>Configurar Shopify:</strong> Crear webhook en tu admin de Shopify</li>';
            echo '<li><strong>Acceder al panel:</strong> <a href="backend/admin/dashboard.php">Ir al Panel de Admin</a></li>';
            echo '<li><strong>Hacer una prueba:</strong> Crear un diseño y realizar una compra de prueba</li>';
            echo '</ol>';
            echo '</div>';
            
        } else {
            echo '<div class="error">';
            echo '<h3>⚠️ ALGUNOS TESTS FALLARON (' . $testsPassed . '/' . $totalTests . ')</h3>';
            echo '<p>Porcentaje completado: ' . $percentage . '%</p>';
            echo '</div>';
            
            echo '<div class="info">';
            echo '<h3>🔧 Soluciones:</h3>';
            echo '<ul>';
            if (!file_exists('backend/config.php')) {
                echo '<li>Copia config.example.php como config.php</li>';
            }
            if (!isset($pdo)) {
                echo '<li>Verifica la contraseña de MySQL en config.php</li>';
            }
            if (isset($existingTables) && count($existingTables) < count($requiredTables)) {
                echo '<li>Ejecuta database_structure.sql en MySQL Workbench</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        ?>

        <div class="info">
            <h3>📋 Instrucciones para MySQL Workbench:</h3>
            <div class="code">
1. Abre MySQL Workbench 8.0 CE<br>
2. Conecta a tu instancia local<br>
3. File → Open SQL Script<br>
4. Selecciona: backend/database_structure.sql<br>
5. Ejecuta todo con Ctrl+Shift+Enter<br>
6. Recarga esta página
            </div>
        </div>

        <div class="info">
            <h3>⚙️ Para cambiar la contraseña de MySQL:</h3>
            <div class="code">
Edita backend/config.php, línea:<br>
define('DB_PASS', 'TU_PASSWORD_MYSQL_REAL');
            </div>
        </div>
    </div>
</body>
</html>
