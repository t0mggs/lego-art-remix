<?php
/**
 * VisuBloq System Health Check
 * Verifica que todos los componentes del sistema estén funcionando correctamente
 */

// No mostrar errores en producción
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VisuBloq - System Health Check</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .content {
            padding: 30px;
        }
        
        .check-section {
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .check-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            font-weight: bold;
            font-size: 1.1em;
            color: #333;
        }
        
        .check-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .check-item:last-child {
            border-bottom: none;
        }
        
        .status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status.ok {
            background: #d4edda;
            color: #155724;
        }
        
        .status.warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .detail {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
        
        .summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            text-align: center;
        }
        
        .summary.healthy {
            background: #d4edda;
            color: #155724;
        }
        
        .summary.issues {
            background: #fff3cd;
            color: #856404;
        }
        
        .summary.critical {
            background: #f8d7da;
            color: #721c24;
        }
        
        .icon {
            font-size: 1.2em;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧱 VisuBloq System Health Check</h1>
            <p>Verificación del estado del sistema de administración</p>
        </div>
        
        <div class="content">
            <?php
            $checks = [];
            $totalChecks = 0;
            $passedChecks = 0;
            $warningChecks = 0;
            $failedChecks = 0;
            
            // ============================================
            // VERIFICACIÓN DE ARCHIVOS PRINCIPALES
            // ============================================
            
            echo '<div class="check-section">';
            echo '<div class="check-header">📁 Archivos del Sistema</div>';
            
            $requiredFiles = [
                'backend/config.php' => 'Archivo de configuración principal',
                'backend/database_structure.sql' => 'Estructura de base de datos',
                'backend/admin/dashboard.php' => 'Panel de administración',
                'backend/admin/orders.php' => 'API de gestión de pedidos',
                'backend/api/shopify-webhook.php' => 'Receptor de webhooks de Shopify',
                'backend/api/save-design-data.php' => 'API para guardar datos de diseño',
                'backend/api/generate-pdf.php' => 'Generador de PDFs',
                'js/shopify-metafields.js' => 'Integración con Shopify',
                'js/index.js' => 'Script principal del frontend'
            ];
            
            foreach ($requiredFiles as $file => $description) {
                $totalChecks++;
                $exists = file_exists($file);
                if ($exists) {
                    $passedChecks++;
                    $status = '<span class="status ok">✓ OK</span>';
                } else {
                    $failedChecks++;
                    $status = '<span class="status error">✗ FALTA</span>';
                }
                
                echo '<div class="check-item">';
                echo '<div>';
                echo '<strong>' . $file . '</strong>';
                echo '<div class="detail">' . $description . '</div>';
                echo '</div>';
                echo $status;
                echo '</div>';
            }
            
            echo '</div>';
            
            // ============================================
            // VERIFICACIÓN DE DIRECTORIOS
            // ============================================
            
            echo '<div class="check-section">';
            echo '<div class="check-header">📂 Estructura de Directorios</div>';
            
            $requiredDirs = [
                'storage' => 'Directorio de almacenamiento',
                'storage/pdfs' => 'Almacenamiento de PDFs',
                'backend' => 'Directorio del backend',
                'backend/admin' => 'Panel de administración',
                'backend/api' => 'APIs del sistema',
                'js' => 'Scripts JavaScript'
            ];
            
            foreach ($requiredDirs as $dir => $description) {
                $totalChecks++;
                $exists = is_dir($dir);
                $writable = is_writable($dir);
                
                if ($exists && $writable) {
                    $passedChecks++;
                    $status = '<span class="status ok">✓ OK</span>';
                    $detail = 'Existe y es escribible';
                } elseif ($exists) {
                    $warningChecks++;
                    $status = '<span class="status warning">⚠ WARNING</span>';
                    $detail = 'Existe pero no es escribible';
                } else {
                    $failedChecks++;
                    $status = '<span class="status error">✗ FALTA</span>';
                    $detail = 'No existe';
                }
                
                echo '<div class="check-item">';
                echo '<div>';
                echo '<strong>' . $dir . '/</strong>';
                echo '<div class="detail">' . $description . ' - ' . $detail . '</div>';
                echo '</div>';
                echo $status;
                echo '</div>';
            }
            
            echo '</div>';
            
            // ============================================
            // VERIFICACIÓN DE PHP
            // ============================================
            
            echo '<div class="check-section">';
            echo '<div class="check-header">🐘 Configuración PHP</div>';
            
            // Versión de PHP
            $totalChecks++;
            $phpVersion = PHP_VERSION;
            $phpVersionOk = version_compare($phpVersion, '7.4.0', '>=');
            
            if ($phpVersionOk) {
                $passedChecks++;
                $status = '<span class="status ok">✓ OK</span>';
            } else {
                $failedChecks++;
                $status = '<span class="status error">✗ OBSOLETO</span>';
            }
            
            echo '<div class="check-item">';
            echo '<div>';
            echo '<strong>Versión PHP</strong>';
            echo '<div class="detail">Actual: ' . $phpVersion . ' (Requerido: 7.4+)</div>';
            echo '</div>';
            echo $status;
            echo '</div>';
            
            // Extensiones PHP
            $requiredExtensions = [
                'pdo' => 'PDO (acceso a base de datos)',
                'pdo_mysql' => 'PDO MySQL',
                'json' => 'JSON',
                'curl' => 'cURL (para APIs)',
                'mbstring' => 'Multibyte String',
                'openssl' => 'OpenSSL (para HTTPS)'
            ];
            
            foreach ($requiredExtensions as $ext => $description) {
                $totalChecks++;
                $loaded = extension_loaded($ext);
                
                if ($loaded) {
                    $passedChecks++;
                    $status = '<span class="status ok">✓ OK</span>';
                } else {
                    $failedChecks++;
                    $status = '<span class="status error">✗ FALTA</span>';
                }
                
                echo '<div class="check-item">';
                echo '<div>';
                echo '<strong>Extensión: ' . $ext . '</strong>';
                echo '<div class="detail">' . $description . '</div>';
                echo '</div>';
                echo $status;
                echo '</div>';
            }
            
            echo '</div>';
            
            // ============================================
            // VERIFICACIÓN DE CONFIGURACIÓN
            // ============================================
            
            echo '<div class="check-section">';
            echo '<div class="check-header">⚙️ Configuración del Sistema</div>';
            
            // Verificar archivo de configuración
            $totalChecks++;
            if (file_exists('backend/config.php')) {
                include_once 'backend/config.php';
                $passedChecks++;
                $status = '<span class="status ok">✓ OK</span>';
                $detail = 'Archivo cargado correctamente';
            } else {
                $failedChecks++;
                $status = '<span class="status error">✗ FALTA</span>';
                $detail = 'Archivo de configuración no encontrado';
            }
            
            echo '<div class="check-item">';
            echo '<div>';
            echo '<strong>Archivo de configuración</strong>';
            echo '<div class="detail">' . $detail . '</div>';
            echo '</div>';
            echo $status;
            echo '</div>';
            
            // Verificar constantes de configuración
            if (file_exists('backend/config.php')) {
                $requiredConstants = [
                    'DB_HOST' => 'Host de base de datos',
                    'DB_NAME' => 'Nombre de base de datos',
                    'DB_USER' => 'Usuario de base de datos',
                    'SHOPIFY_SHOP' => 'Tienda de Shopify',
                    'SHOPIFY_ACCESS_TOKEN' => 'Token de acceso Shopify',
                    'BASE_URL' => 'URL base del sitio'
                ];
                
                foreach ($requiredConstants as $constant => $description) {
                    $totalChecks++;
                    if (defined($constant) && !empty(constant($constant))) {
                        $passedChecks++;
                        $status = '<span class="status ok">✓ OK</span>';
                        $value = constant($constant);
                        if ($constant === 'SHOPIFY_ACCESS_TOKEN') {
                            $value = substr($value, 0, 10) . '...';
                        }
                        $detail = $description . ': ' . $value;
                    } else {
                        $failedChecks++;
                        $status = '<span class="status error">✗ FALTA</span>';
                        $detail = $description . ': No configurado';
                    }
                    
                    echo '<div class="check-item">';
                    echo '<div>';
                    echo '<strong>' . $constant . '</strong>';
                    echo '<div class="detail">' . $detail . '</div>';
                    echo '</div>';
                    echo $status;
                    echo '</div>';
                }
            }
            
            echo '</div>';
            
            // ============================================
            // VERIFICACIÓN DE BASE DE DATOS
            // ============================================
            
            echo '<div class="check-section">';
            echo '<div class="check-header">🗄️ Base de Datos</div>';
            
            if (file_exists('backend/config.php') && function_exists('getDatabase')) {
                try {
                    $totalChecks++;
                    $pdo = getDatabase();
                    $passedChecks++;
                    $status = '<span class="status ok">✓ OK</span>';
                    $detail = 'Conexión establecida correctamente';
                    
                    echo '<div class="check-item">';
                    echo '<div>';
                    echo '<strong>Conexión a base de datos</strong>';
                    echo '<div class="detail">' . $detail . '</div>';
                    echo '</div>';
                    echo $status;
                    echo '</div>';
                    
                    // Verificar tablas
                    $requiredTables = [
                        'orders',
                        'order_pieces', 
                        'order_pdfs',
                        'admin_users',
                        'system_logs',
                        'usage_stats'
                    ];
                    
                    foreach ($requiredTables as $table) {
                        $totalChecks++;
                        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                        $exists = $stmt->rowCount() > 0;
                        
                        if ($exists) {
                            $passedChecks++;
                            $status = '<span class="status ok">✓ OK</span>';
                            
                            // Contar registros
                            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                            $count = $countStmt->fetch()['count'];
                            $detail = "Tabla existe ($count registros)";
                        } else {
                            $failedChecks++;
                            $status = '<span class="status error">✗ FALTA</span>';
                            $detail = 'Tabla no existe - ejecutar database_structure.sql';
                        }
                        
                        echo '<div class="check-item">';
                        echo '<div>';
                        echo '<strong>Tabla: ' . $table . '</strong>';
                        echo '<div class="detail">' . $detail . '</div>';
                        echo '</div>';
                        echo $status;
                        echo '</div>';
                    }
                    
                } catch (Exception $e) {
                    $totalChecks++;
                    $failedChecks++;
                    $status = '<span class="status error">✗ ERROR</span>';
                    $detail = 'Error: ' . $e->getMessage();
                    
                    echo '<div class="check-item">';
                    echo '<div>';
                    echo '<strong>Conexión a base de datos</strong>';
                    echo '<div class="detail">' . $detail . '</div>';
                    echo '</div>';
                    echo $status;
                    echo '</div>';
                }
            } else {
                $totalChecks++;
                $failedChecks++;
                $status = '<span class="status error">✗ ERROR</span>';
                $detail = 'No se puede verificar - configuración faltante';
                
                echo '<div class="check-item">';
                echo '<div>';
                echo '<strong>Conexión a base de datos</strong>';
                echo '<div class="detail">' . $detail . '</div>';
                echo '</div>';
                echo $status;
                echo '</div>';
            }
            
            echo '</div>';
            
            // ============================================
            // VERIFICACIÓN DE URLs
            // ============================================
            
            echo '<div class="check-section">';
            echo '<div class="check-header">🌐 URLs y Endpoints</div>';
            
            if (defined('BASE_URL')) {
                $endpoints = [
                    '/backend/admin/dashboard.php' => 'Panel de administración',
                    '/backend/api/shopify-webhook.php' => 'Webhook de Shopify',
                    '/backend/api/save-design-data.php' => 'API de guardado de diseños',
                    '/backend/api/generate-pdf.php' => 'Generador de PDFs'
                ];
                
                foreach ($endpoints as $endpoint => $description) {
                    $totalChecks++;
                    $file = ltrim($endpoint, '/');
                    
                    if (file_exists($file)) {
                        $passedChecks++;
                        $status = '<span class="status ok">✓ OK</span>';
                        $detail = $description . ' - Archivo disponible';
                    } else {
                        $failedChecks++;
                        $status = '<span class="status error">✗ FALTA</span>';
                        $detail = $description . ' - Archivo no encontrado';
                    }
                    
                    echo '<div class="check-item">';
                    echo '<div>';
                    echo '<strong>' . BASE_URL . $endpoint . '</strong>';
                    echo '<div class="detail">' . $detail . '</div>';
                    echo '</div>';
                    echo $status;
                    echo '</div>';
                }
            }
            
            echo '</div>';
            
            // ============================================
            // RESUMEN FINAL
            // ============================================
            
            $healthPercentage = round(($passedChecks / $totalChecks) * 100);
            
            if ($failedChecks === 0 && $warningChecks === 0) {
                $summaryClass = 'healthy';
                $summaryIcon = '✅';
                $summaryTitle = '¡Sistema Completamente Funcional!';
                $summaryMessage = 'Todos los componentes están funcionando correctamente. El sistema está listo para producción.';
            } elseif ($failedChecks === 0) {
                $summaryClass = 'issues';
                $summaryIcon = '⚠️';
                $summaryTitle = 'Sistema Funcional con Advertencias';
                $summaryMessage = 'El sistema funciona pero hay algunas advertencias que deberías revisar.';
            } else {
                $summaryClass = 'critical';
                $summaryIcon = '❌';
                $summaryTitle = 'Problemas Críticos Detectados';
                $summaryMessage = 'Hay problemas importantes que impiden el funcionamiento correcto del sistema.';
            }
            
            echo '<div class="summary ' . $summaryClass . '">';
            echo '<h2>' . $summaryIcon . ' ' . $summaryTitle . '</h2>';
            echo '<p><strong>Estado del Sistema: ' . $healthPercentage . '% Funcional</strong></p>';
            echo '<p>Verificaciones completadas: ' . $passedChecks . '/' . $totalChecks . '</p>';
            if ($warningChecks > 0) {
                echo '<p>Advertencias: ' . $warningChecks . '</p>';
            }
            if ($failedChecks > 0) {
                echo '<p>Errores críticos: ' . $failedChecks . '</p>';
            }
            echo '<p>' . $summaryMessage . '</p>';
            
            if (defined('BASE_URL')) {
                echo '<p><strong>Panel de Admin:</strong> <a href="' . BASE_URL . '/backend/admin/dashboard.php" target="_blank">' . BASE_URL . '/backend/admin/dashboard.php</a></p>';
            }
            
            echo '</div>';
            ?>
        </div>
    </div>
</body>
</html>
