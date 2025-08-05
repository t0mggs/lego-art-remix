🔍 VERIFICACIÓN DE PRODUCTO SHOPIFY - VISUBLOQ PERSONALIZADO
================================================================

## 🎯 PROBLEMA IDENTIFICADO:
El botón "CONSTRUIR" intenta abrir: https://visubloq.com/products/visubloq-personalizado
Pero esta URL no existe porque:

1. El dominio real de tu tienda es: visubloq.myshopify.com
2. Puede que el producto "visubloq-personalizado" no esté creado

## 🔧 SOLUCIONES:

### OPCIÓN 1: Crear el producto en Shopify
1. Ve a Shopify Admin: https://visubloq.myshopify.com/admin/products
2. Clic en "Add product"
3. Configurar:
   - Title: "VisuBloq Personalizado"
   - Handle: "visubloq-personalizado" (automático)
   - Price: 19,99€
   - Description: "Diseño LEGO personalizado con todas las piezas incluidas"
   - Images: Subir alguna imagen de LEGO

### OPCIÓN 2: Usar producto existente
Si ya tienes un producto, podemos usar su handle real.

### OPCIÓN 3: Crear página de prueba temporal
Para testing inmediato, podemos redirigir a la tienda principal.

## 🧪 COMANDOS DE PRUEBA:

Abre la consola del navegador (F12) y ejecuta:

```javascript
// Test 1: Verificar si el producto existe
fetch('https://visubloq.myshopify.com/products/visubloq-personalizado.json')
  .then(r => r.json())
  .then(data => console.log('✅ Producto encontrado:', data))
  .catch(e => console.log('❌ Producto NO encontrado:', e));

// Test 2: Listar productos existentes
fetch('https://visubloq.myshopify.com/products.json?limit=10')
  .then(r => r.json())
  .then(data => {
    console.log('📋 Productos existentes:');
    data.products.forEach(p => console.log(`- ${p.title} (${p.handle})`));
  });
```

## 🎯 CONFIGURACIÓN TEMPORAL:

Por ahora he cambiado la URL a: visubloq.myshopify.com
Esto debería funcionar mejor, pero necesitas crear el producto.

¿Quieres que:
1. Te ayude a crear el producto en Shopify?
2. Usemos un producto existente?
3. Hagamos una redirección temporal para probar?
