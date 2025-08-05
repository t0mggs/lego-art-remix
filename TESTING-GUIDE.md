# 🧪 GUÍA COMPLETA DE TESTING - SHOPIFY INTEGRATION

## ✅ ERRORES CORREGIDOS
- ✅ Error de sintaxis en `isValidShopifyOrder()` function → RESUELTO
- ✅ Estructura de funciones arreglada
- ✅ Sin errores de sintaxis en Visual Studio Code

## 🚀 CÓMO PROBAR EN CONSOLA

### Método 1: Archivo de Test Dedicado (RECOMENDADO)
1. Abre `test-shopify.html` en tu navegador
2. Abre la consola (F12 → Console)
3. Ejecuta: `quickTest()` para una prueba rápida
4. O ejecuta: `fullTest()` para prueba completa con API

### Método 2: Desde tu index.html principal
1. Abre `index.html` en tu navegador
2. Abre la consola (F12 → Console)
3. Carga las funciones de test:
   ```javascript
   // Primero cargar el archivo de test
   let script = document.createElement('script');
   script.src = 'js/test-functions.js';
   document.head.appendChild(script);
   
   // Luego ejecutar tests
   quickTest();
   ```

## 📋 FUNCIONES DE TEST DISPONIBLES

### 🔥 Tests Principales
```javascript
quickTest()           // Test rápido sin llamadas API
fullTest()            // Test completo con Shopify API real
testOrderValidation() // Solo validación de pedidos
testDownload('12345') // Descargar PDF de pedido específico
showHelp()            // Mostrar ayuda completa
```

### 🎯 Tests Específicos
```javascript
// Test manual con tus propios datos
const myOrder = {
    id: 123456789,
    order_number: "VB1001",
    email: "cliente@example.com",
    total_price: "29.99",
    financial_status: "paid",
    customer: {
        first_name: "Juan",
        last_name: "Pérez"
    },
    webhook_verified: true
};

// Validar pedido
isValidShopifyOrder(myOrder);

// Procesar pedido completo (con API real)
processShopifyOrder(myOrder);
```

## 🔧 CONFIGURACIÓN ACTUAL DE SHOPIFY

```javascript
// Tus credenciales configuradas:
SHOPIFY_CONFIG = {
    shop: 'VisuBloq.myshopify.com',
    accessToken: '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',
    apiVersion: '2023-04'
}
```

## 🎨 SIMULACIÓN DE MOSAICO

Los tests incluyen simulación automática de mosaicos:
- `simulateBasicMosaic()` → 16x16 (256 piezas)
- `simulateFullMosaic()` → 48x48 (2304 piezas)

## ⚠️ IMPORTANTE - TESTING SEGURO

### Tests que NO hacen llamadas API (seguros):
- `quickTest()`
- `testOrderValidation()`
- `simulateBasicMosaic()`
- `simulateFullMosaic()`

### Tests que SÍ hacen llamadas API (usar con cuidado):
- `fullTest()` → Crea metafields reales en Shopify
- `processShopifyOrder()` → Guarda PDFs reales
- `testDownload()` → Descarga PDFs reales

## 📝 EJEMPLO DE EJECUCIÓN PASO A PASO

```javascript
// 1. Verificar que todo está cargado
console.log('Verificando funciones...');
typeof savePDFToShopifyOrder; // should return 'function'

// 2. Test rápido
quickTest();

// 3. Si todo está bien, test completo
fullTest();

// 4. Verificar resultados en consola
```

## 🔍 QUE BUSCAR EN LOS RESULTADOS

### ✅ Resultados Exitosos:
- `✅ PDF guardado exitosamente en pedido: VB1234`
- `✅ Metafield creado con ID: 12345678`
- `✅ Pedido etiquetado como: pdf-generated`

### ❌ Errores Comunes:
- `❌ Error de autenticación` → Verificar access token
- `❌ Pedido no válido` → Verificar datos del pedido
- `❌ Error creando metafield` → Verificar permisos API

## 🚨 SOLUCIÓN DE PROBLEMAS

### Si no funcionan las funciones:
1. Verificar que `index.js` se cargó: `typeof processShopifyOrder`
2. Recargar la página completamente
3. Verificar errores en consola (F12)

### Si falla la API de Shopify:
1. Verificar token de acceso
2. Verificar permisos del token
3. Verificar que el shop name es correcto

### Si falla la generación de PDF:
1. Verificar que `currentMosaic` existe: `window.currentMosaic`
2. Ejecutar `simulateFullMosaic()` primero
3. Verificar librerías de PDF cargadas

## 📞 COMANDOS DE EMERGENCIA

```javascript
// Ver configuración actual
console.log(SHOPIFY_CONFIG);

// Ver mosaico actual
console.log(window.currentMosaic);

// Limpiar y recargar
location.reload();

// Test mínimo
isValidShopifyOrder({
    order_number: "VB123",
    email: "test@test.com",
    total_price: "10.00",
    financial_status: "paid",
    customer: { first_name: "Test" },
    webhook_verified: true
});
```

## 🎯 PRÓXIMOS PASOS

1. ✅ Ejecutar `quickTest()` para verificar básicos
2. ✅ Ejecutar `fullTest()` para test completo
3. ✅ Revisar logs en consola
4. ✅ Configurar webhook real en Shopify
5. ✅ Probar con pedido real

---
**🔧 Archivo creado:** test-shopify.html, js/test-functions.js
**✅ Estado:** Todo listo para testing
