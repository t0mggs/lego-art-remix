<?php
// 🔒 SISTEMA DE LOGIN SIMPLE PARA ADMIN
// backend/admin/login.php

session_start();
require_once '../config.php';

// Manejar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Verificar si ya está logueado
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Manejar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    // Verificar password (en producción usar hash más seguro)
    if (password_verify($password, ADMIN_PASSWORD)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        
        logMessage('LOGIN', 'Admin login exitoso', ['ip' => $_SERVER['REMOTE_ADDR']]);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Contraseña incorrecta';
        logMessage('LOGIN_FAIL', 'Intento de login fallido', [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'password_length' => strlen($password)
        ]);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔒 VisuBloq Admin - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 2rem;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            background: #667eea;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5a6fd8;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border: 1px solid #f5c6cb;
        }
        
        .footer {
            margin-top: 2rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        .security-note {
            background: #e7f3ff;
            color: #0c5460;
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            border: 1px solid #b6e3ff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">🧱</div>
        <h1 class="title">VisuBloq Admin</h1>
        
        <?php if (isset($error)): ?>
            <div class="error">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="password">Contraseña de Administrador:</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="Introduce tu contraseña"
                    autocomplete="current-password"
                >
            </div>
            
            <button type="submit" class="btn">
                🔓 Acceder al Panel
            </button>
        </form>
        
        <div class="security-note">
            🔒 <strong>Área restringida</strong><br>
            Solo el administrador puede acceder a este panel.
        </div>
        
        <div class="footer">
            VisuBloq Admin Panel v1.0<br>
            <small>Última actualización: <?php echo date('Y-m-d'); ?></small>
        </div>
    </div>
    
    <script>
        // Auto-focus en el campo de contraseña
        document.getElementById('password').focus();
        
        // Animación simple del logo
        const logo = document.querySelector('.logo');
        setInterval(() => {
            logo.style.transform = 'scale(1.1)';
            setTimeout(() => {
                logo.style.transform = 'scale(1)';
            }, 200);
        }, 3000);
    </script>
</body>
</html>
