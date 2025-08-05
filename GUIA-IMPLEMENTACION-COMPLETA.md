# 🚀 GUÍA COMPLETA DE IMPLEMENTACIÓN - GITHUB + SHOPIFY + VERCEL

## 📋 PASO A PASO PARA IMPLEMENTAR

### **🎯 PRERREQUISITOS**
- ✅ Cuenta de GitHub (ya la tienes)
- ✅ Tienda Shopify (ya la tienes)  
- ✅ Crear cuenta en [Vercel.com](https://vercel.com) (GRATIS)
- ✅ Crear cuenta en [PlanetScale.com](https://planetscale.com) (GRATIS)

---

## **🗄️ PASO 1: CONFIGURAR BASE DE DATOS (PlanetScale)**

### 1.1 Crear Base de Datos
1. Ve a [PlanetScale.com](https://planetscale.com)
2. Regístrate con GitHub
3. Click "Create database"
4. Nombre: `visubloq`
5. Región: `us-east` (más barata)
6. Click "Create database"

### 1.2 Configurar la Base de Datos
1. En tu database, click "Connect"
2. Selecciona "General" 
3. Copia los datos de conexión:
   ```
   Host: aws.connect.psdb.cloud
   Username: xxxxxxxxxxxx
   Password: pscale_pw_xxxxxxxxxxxx
   Database: visubloq
   ```
4. Ve a "Console" en tu database
5. Pega y ejecuta el contenido de `backend/database/setup.sql`

---

## **🚀 PASO 2: DESPLEGAR API EN VERCEL**

### 2.1 Conectar GitHub con Vercel
1. Ve a [Vercel.com](https://vercel.com)
2. Click "Sign up" → "Continue with GitHub"
3. Autoriza Vercel para acceder a tus repos
4. Click "Import Project"
5. Selecciona tu repo `VisuBloq`

### 2.2 Configurar Variables de Entorno
En Vercel Dashboard → Tu Proyecto → Settings → Environment Variables:

```bash
# Base de Datos
DB_HOST=aws.connect.psdb.cloud
DB_NAME=visubloq
DB_USER=tu_usuario_planetscale
DB_PASS=tu_password_planetscale

# Shopify
SHOPIFY_WEBHOOK_SECRET=tu_webhook_secret_aqui

# Admin
ADMIN_PASSWORD=tu_password_admin_super_seguro
```

### 2.3 Desplegar
1. Click "Deploy"
2. Espera 2-3 minutos
3. Tu API estará en: `https://tu-proyecto.vercel.app`

---

## **🔗 PASO 3: CONFIGURAR WEBHOOK EN SHOPIFY**

### 3.1 Crear Webhook
1. Shopify Admin → Settings → Notifications
2. Scroll hasta "Webhooks" 
3. Click "Create webhook"
4. **Event:** `Order creation`
5. **Format:** `JSON`
6. **URL:** `https://tu-proyecto.vercel.app/webhook/shopify-order`
7. Click "Save"

### 3.2 Configurar Webhook Secret
1. Copia el webhook secret que aparece
2. Ve a Vercel → Settings → Environment Variables
3. Actualiza `SHOPIFY_WEBHOOK_SECRET` con el valor real
4. Redeploy el proyecto

---

## **🎨 PASO 4: ACTUALIZAR TU CÓDIGO JAVASCRIPT**

### 4.1 Cambiar URLs en index.js
En `js/index.js`, encuentra esta línea:
```javascript
const apiUrl = 'https://tu-proyecto.vercel.app/api/save-pdf.php';
```

Cámbiala por tu URL real de Vercel:
```javascript
const apiUrl = 'https://visubloq-api.vercel.app/api/save-pdf.php';
```

### 4.2 Subir Cambios a GitHub
```bash
git add .
git commit -m "Configuración para Vercel"
git push origin main
```

---

## **🧪 PASO 5: PROBAR EL SISTEMA**

### 5.1 Probar API
1. Ve a `https://tu-proyecto.vercel.app/api/config.php`
2. Deberías ver información del entorno

### 5.2 Probar Webhook
1. Haz un pedido de prueba en Shopify
2. Ve a Vercel → Functions → Logs
3. Deberías ver logs del webhook

### 5.3 Probar Guardado de PDF
1. Usa tu app VisuBloq en Shopify
2. Genera un PDF
3. Verifica que se guarde en PlanetScale

---

## **🎛️ PASO 6: PANEL DE ADMINISTRACIÓN**

### 6.1 Crear Panel Admin
El panel estará en: `https://tu-proyecto.vercel.app/api/admin.php`

### 6.2 Crear admin.php
```php
<?php
// api/admin.php
require_once 'config.php';

// Login simple
session_start();
if (!isset($_SESSION['admin_logged'])) {
    if ($_POST['password'] ?? false) {
        if (password_verify($_POST['password'], ADMIN_PASSWORD_HASH)) {
            $_SESSION['admin_logged'] = true;
        }
    }
    
    if (!isset($_SESSION['admin_logged'])) {
        // Mostrar formulario de login
        ?>
        <!DOCTYPE html>
        <html>
        <head><title>VisuBloq Admin</title></head>
        <body>
            <h1>🧱 VisuBloq Admin</h1>
            <form method="post">
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit">Entrar</button>
            </form>
        </body>
        </html>
        <?php
        exit;
    }
}

// Panel principal
try {
    $pdo = getDBConnection();
    
    // Obtener estadísticas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM order_pdfs");
    $totalPDFs = $stmt->fetch()['total'];
    
    // Obtener pedidos recientes
    $stmt = $pdo->query("
        SELECT o.*, COUNT(pdf.id) as pdf_count 
        FROM orders o 
        LEFT JOIN order_pdfs pdf ON o.id = pdf.order_id 
        GROUP BY o.id 
        ORDER BY o.created_at DESC 
        LIMIT 20
    ");
    $recentOrders = $stmt->fetchAll();
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>VisuBloq Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .stats { background: #e7f3ff; padding: 15px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🧱 VisuBloq Admin Panel</h1>
    
    <div class="stats">
        <h3>📊 Estadísticas</h3>
        <p><strong>Total Pedidos:</strong> <?= $totalOrders ?></p>
        <p><strong>PDFs Generados:</strong> <?= $totalPDFs ?></p>
    </div>
    
    <h3>📋 Pedidos Recientes</h3>
    <table>
        <tr>
            <th>Pedido</th>
            <th>Cliente</th>
            <th>Email</th>
            <th>Valor</th>
            <th>PDFs</th>
            <th>Fecha</th>
        </tr>
        <?php foreach ($recentOrders as $order): ?>
        <tr>
            <td><?= htmlspecialchars($order['order_number']) ?></td>
            <td><?= htmlspecialchars($order['customer_name']) ?></td>
            <td><?= htmlspecialchars($order['customer_email']) ?></td>
            <td>€<?= $order['order_value'] ?></td>
            <td><?= $order['pdf_count'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <p><a href="?logout=1">Cerrar Sesión</a></p>
</body>
</html>
```

---

## **💰 COSTOS**

### Servicios Gratuitos:
- ✅ **Vercel:** Gratis (100GB bandwidth/mes)
- ✅ **PlanetScale:** Gratis (5GB storage, 1 billion reads/mes)
- ✅ **GitHub:** Gratis
- ✅ **Shopify:** Ya lo tienes

### **Total: €0/mes** 🎉

---

## **🚨 PROBLEMAS COMUNES Y SOLUCIONES**

### Error: "Database connection failed"
```bash
# Verifica variables de entorno en Vercel
# Ve a Settings → Environment Variables
# Asegúrate que todos los valores estén correctos
```

### Error: "Webhook not verified"
```bash
# El secret del webhook no coincide
# Ve a Shopify → Settings → Notifications → Webhooks
# Copia el secret exacto a Vercel Environment Variables
```

### Error: "PDF not saving"
```bash
# Revisa los logs en Vercel Dashboard → Functions
# Verifica que el JSON que envías desde JS sea correcto
```

### JavaScript no puede conectar a API
```javascript
// Asegúrate de cambiar la URL en index.js:
const apiUrl = 'https://TU-PROYECTO-REAL.vercel.app/api/save-pdf.php';
```

---

## **🔧 DESARROLLO LOCAL**

Para probar en tu computadora:

```bash
# 1. Instalar Vercel CLI
npm i -g vercel

# 2. En tu carpeta del proyecto
vercel dev

# 3. Tu API estará en http://localhost:3000
# Cambia la URL en index.js para testing local
```

---

## **✅ CHECKLIST FINAL**

- [ ] Base de datos creada en PlanetScale
- [ ] Tablas creadas con setup.sql
- [ ] Proyecto desplegado en Vercel
- [ ] Variables de entorno configuradas
- [ ] Webhook configurado en Shopify
- [ ] URL actualizada en index.js
- [ ] Cambios subidos a GitHub
- [ ] Pedido de prueba funciona
- [ ] PDF se guarda correctamente
- [ ] Panel admin accesible

**¡Listo! Tu sistema está funcionando completamente gratis en la nube.**
