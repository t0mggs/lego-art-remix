# 🚀 TESTING DE VISUBLOQ CON NGROK

## ⚠️ IMPORTANTE
**ngrok es SOLO para testing de desarrollo. Para producción necesitas hosting real.**

## 📋 PASOS PARA PROBAR

### 1. Preparar XAMPP
- ✅ Inicia Apache y MySQL en XAMPP
- ✅ Ve a http://localhost/VisuBloq/app/
- ✅ Verifica que el sistema funciona local

### 2. Instalar ngrok (temporal)
- 📥 Descarga: https://ngrok.com/download
- 📁 Extrae a `C:\ngrok\`
- 🆓 Registra cuenta gratuita en ngrok.com

### 3. Iniciar testing
- ▶️ Ejecuta `start-testing.bat`
- 📝 Copia la URL que aparece (ej: `https://abc123.ngrok-free.app`)

### 4. Configurar Shopify webhook
- 🔧 Ve a Shopify Admin → Settings → Notifications
- ➕ Create webhook:
  - **Event**: Order payment
  - **URL**: `TU-URL-NGROK/VisuBloq/app/backend/api/shopify-webhook.php`
  - **Format**: JSON

### 5. Actualizar config.php
```php
define('BASE_URL', 'https://TU-URL-NGROK/VisuBloq/app');
```

### 6. Probar compra
- 🛒 Haz una compra de prueba en tu tienda
- 📄 Verifica que el PDF se capture automáticamente
- 💾 Revisa en phpMyAdmin que se guardó en la BD

## 🏠 DESPUÉS DEL TESTING

Una vez que compruebes que funciona, necesitas:

1. **Elegir hosting real** (Hostinger, SiteGround, etc.)
2. **Subir archivos** al hosting
3. **Configurar BD** en el hosting  
4. **Cambiar webhook** con URL real
5. **Actualizar config.php** con URL definitiva

## 🎯 URL FINAL DE PRODUCCIÓN
```
https://tudominio.com/visubloq/backend/api/shopify-webhook.php
```

**¡No olvides que ngrok es temporal! Los clientes necesitan URL permanente.**