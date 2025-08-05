# 🛒 GUÍA DE CONFIGURACIÓN DE SHOPIFY PARA VISUBLOQ

## ❗ PASOS OBLIGATORIOS EN SHOPIFY

### 1. 🔧 **CREAR APLICACIÓN PRIVADA**

1. **Ve a tu Shopify Admin:** 
   ```
   https://visubloq.myshopify.com/admin/settings/apps
   ```

2. **Hacer clic en "Desarrollar aplicaciones"** (al final de la página)

3. **"Crear una aplicación":**
   - **Nombre:** `VisuBloq PDF Generator`
   - **Desarrollador de aplicaciones:** Tu nombre/email

4. **Configurar Admin API access:**
   - Hacer clic en **"Configurar Admin API scopes"**
   - Seleccionar estos permisos:

### 🔐 **PERMISOS REQUERIDOS:**
```
✅ Orders
   • read_orders
   • write_orders
   
✅ Order editing  
   • write_order_edits
   
✅ Metafields
   • read_metafields
   • write_metafields
   
✅ Files (opcional)
   • read_files  
   • write_files
```

5. **Guardar** la configuración

6. **Instalar aplicación:**
   - Hacer clic en **"Instalar aplicación"**
   - **Generar token de acceso**
   - **COPIAR EL TOKEN** (solo se muestra una vez)

### 2. 🔑 **ACTUALIZAR TOKEN EN EL CÓDIGO**

Una vez que tengas el token, actualiza en `index.js`:

```javascript
// Línea ~3080 en index.js
const shopifyConfig = {
    shop: 'VisuBloq.myshopify.com', // ✅ Ya está correcto
    accessToken: 'TU_NUEVO_TOKEN_AQUI', // 🔄 CAMBIAR ESTO
    apiVersion: '2024-01'
};
```

### 3. 🧪 **PROBAR LA CONFIGURACIÓN**

1. **Abre** `test-shopify.html` en tu navegador
2. **Abre la consola** (F12)
3. **Ejecuta:** `verifyShopifyConfig()`
4. **Si todo está OK, ejecuta:** `quickTest()`

### 4. 📋 **EJEMPLO DE EJECUCIÓN:**

```javascript
// 1. Verificar configuración
verifyShopifyConfig()

// 2. Test rápido sin API
quickTest()

// 3. Test completo con API real
fullTest()
```

## ⚠️ **PROBLEMAS COMUNES Y SOLUCIONES**

### Error: "401 Unauthorized"
- **Problema:** Token inválido o sin permisos
- **Solución:** Regenerar token con permisos correctos

### Error: "403 Forbidden" 
- **Problema:** Faltan permisos específicos
- **Solución:** Añadir permisos faltantes en la app

### Error: "404 Not Found"
- **Problema:** URL de tienda incorrecta
- **Solución:** Verificar que sea `VisuBloq.myshopify.com`

### Error: CORS
- **Problema:** Llamadas desde navegador bloqueadas
- **Solución:** Normal en testing local, funciona en producción

## 🔄 **FLUJO COMPLETO DE TESTING:**

```javascript
// Paso 1: Verificar configuración
await verifyShopifyConfig()
// ✅ Debería mostrar: "Acceso básico: OK"

// Paso 2: Test sin API
quickTest()  
// ✅ Debería mostrar: "QUICK TEST COMPLETADO"

// Paso 3: Test con API real (crea metafields reales)
fullTest()
// ✅ Debería mostrar: "PDF guardado exitosamente"
```

## 📝 **VERIFICAR EN SHOPIFY ADMIN:**

Después del `fullTest()`, ve a:
1. **Pedidos** → Buscar el pedido de prueba
2. **Ver pedido** → Scroll al final  
3. **Metafields** → Deberías ver:
   - `visubloq.piece_list_pdf` (PDF en base64)
   - `visubloq.piece_info` (información JSON)

## 🎯 **SIGUIENTE PASO: WEBHOOKS (OPCIONAL)**

Para automatización completa, configurar webhook:
- **URL:** `https://tu-servidor.com/webhook/shopify`
- **Evento:** Order creation
- **Formato:** JSON

Pero esto requiere un servidor backend. Por ahora puedes usar el sistema manualmente.

---

## 🚨 **IMPORTANTE:**

- El token actual en el código puede ser de prueba
- DEBES generar tu propio token con permisos correctos
- El sistema funciona pero necesita la configuración completa
- Los tests `quickTest()` y `verifyShopifyConfig()` te ayudarán a diagnosticar problemas

## 🎯 **ORDEN DE EJECUCIÓN:**

1. ✅ Configurar app en Shopify (esta guía)
2. ✅ Actualizar token en código  
3. ✅ Ejecutar `verifyShopifyConfig()`
4. ✅ Ejecutar `quickTest()`
5. ✅ Ejecutar `fullTest()` 
6. ✅ Verificar resultado en Shopify Admin
