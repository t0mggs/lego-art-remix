# 🚀 GUÍA DE IMPLEMENTACIÓN - VISUBLOQ BACKEND

## 📋 PASOS PARA IMPLEMENTAR (en orden)

### 1. 🛠️ PREPARAR EL SERVIDOR (HOSTING)

**Opciones de hosting recomendadas para principiantes:**
- **Hostinger** (€2-5/mes) - Incluye PHP, MySQL, SSL
- **SiteGround** (€3-7/mes) - Muy fácil de usar
- **DreamHost** (€3-6/mes) - Buen soporte

**Lo que necesitas:**
- PHP 7.4 o superior
- MySQL 5.7 o superior  
- SSL Certificate (HTTPS)
- Al menos 1GB de espacio

### 2. 🗄️ CONFIGURAR BASE DE DATOS

**Pasos:**
1. Accede al panel de control de tu hosting (cPanel)
2. Ve a "MySQL Databases"
3. Crea una nueva base de datos: `visubloq_db`
4. Crea un usuario: `visubloq_user` con contraseña segura
5. Asigna permisos completos al usuario sobre la base de datos
6. Ejecuta el archivo `backend/database/setup.sql` en phpMyAdmin

### 3. 📁 SUBIR ARCHIVOS

**Estructura en tu servidor:**
```
public_html/
├── index.html (tu frontend actual)
├── js/
├── assets/
├── backend/
│   ├── config.php
│   ├── api/
│   │   ├── shopify-webhook.php
│   │   └── save-pdf.php
│   ├── admin/
│   │   ├── index.php
│   │   ├── login.php
│   │   └── orders.php
│   └── database/
└── storage/
    └── pdfs/ (permisos 755)
```

### 4. ⚙️ CONFIGURAR VARIABLES

**Edita `backend/config.php`:**
```php
// Cambia estos valores por los reales
define('DB_HOST', 'localhost');
define('DB_NAME', 'tu_nombre_bd');
define('DB_USER', 'tu_usuario_bd');
define('DB_PASS', 'tu_password_bd');

define('BASE_URL', 'https://tu-dominio.com');
define('ADMIN_EMAIL', 'tu-email@gmail.com');

// Cambia la contraseña de admin
define('ADMIN_PASSWORD', password_hash('tu_password_admin_seguro', PASSWORD_DEFAULT));
```

### 5. 🔗 CONFIGURAR WEBHOOK EN SHOPIFY

**Pasos en Shopify Admin:**
1. Ve a Settings → Notifications
2. Scroll hasta "Webhooks"
3. Click "Create webhook"
4. **Event:** Order creation
5. **Format:** JSON
6. **URL:** `https://tu-dominio.com/backend/api/shopify-webhook.php`
7. Guarda y copia el webhook secret
8. Pega el secret en `config.php` en `SHOPIFY_WEBHOOK_SECRET`

### 6. 🧪 PROBAR EL SISTEMA

**Orden de pruebas:**
1. **Base de datos:** Ve a `https://tu-dominio.com/backend/admin/login.php`
2. **Login:** Usa tu contraseña de admin
3. **Panel:** Deberías ver el dashboard vacío
4. **Webhook:** Haz un pedido de prueba en Shopify
5. **Verificar:** El pedido debe aparecer en el panel admin

### 7. 🔧 INTEGRAR CON FRONTEND

**Modificar `js/index.js`:**
- Cambia las URLs de API para que apunten a tu dominio
- Actualiza la función `savePDFToDatabase()` con tu URL real

### 8. 🚀 PONER EN PRODUCCIÓN

**Checklist final:**
- [ ] Cambiar `DEBUG_MODE` a `false` en config.php
- [ ] Verificar permisos de carpeta `storage/pdfs` (755)
- [ ] Probar webhook con pedido real de Shopify
- [ ] Verificar que los PDFs se generan y guardan
- [ ] Probar acceso al panel admin desde dispositivo móvil

## 🆘 SOLUCIÓN DE PROBLEMAS COMUNES

### Error de conexión a BD
```php
// Verifica en config.php que los datos sean correctos
// Prueba la conexión directamente:
$pdo = new PDO("mysql:host=localhost;dbname=tu_bd", "usuario", "password");
```

### Webhook no recibe datos
- Verifica que la URL sea accesible públicamente
- Revisa los logs en `system_logs` tabla
- Comprueba que el secret del webhook coincida

### PDFs no se guardan
- Verifica permisos de la carpeta `storage/pdfs`
- Revisa que hay suficiente espacio en disco
- Comprueba los logs de errores de PHP

## 📧 CONFIGURACIÓN DE EMAILS (OPCIONAL)

Si quieres notificaciones por email:

1. **Gmail App Password:**
   - Ve a tu cuenta de Google
   - Seguridad → Verificación en 2 pasos
   - Contraseñas de aplicaciones
   - Genera una para "VisuBloq"

2. **Actualiza config.php:**
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'tu-email@gmail.com');
define('SMTP_PASS', 'tu-app-password');
```

## 📞 SOPORTE

Si tienes problemas:
1. Revisa los logs en la tabla `system_logs`
2. Activa `DEBUG_MODE = true` temporalmente
3. Revisa los error logs de PHP en tu hosting
4. Verifica que todas las URLs y tokens sean correctos

## 🔐 SEGURIDAD ADICIONAL

**Recomendaciones:**
- Cambia regularmente las contraseñas
- Usa HTTPS siempre
- Mantén backups regulares de la BD
- Limita acceso a `/backend/admin/` por IP si es posible
- Actualiza PHP regularmente

## 💰 COSTOS ESTIMADOS

**Mensual:**
- Hosting: €3-7/mes
- Dominio: €10-15/año
- SSL: Gratis (Let's Encrypt)
- **Total:** ~€5-10/mes

**¡La implementación debería costarte menos de €50 en total para empezar!**
