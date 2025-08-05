# 🎯 CONFIGURACIÓN SHOPIFY - ESTRUCTURA DE TEMA MODERNA

## 📁 ESTRUCTURA IDENTIFICADA:
```
templates/
├── product.json (configuración del template)
sections/
├── main-product.liquid (sección principal del producto)
```

## ✅ PASOS ACTUALIZADOS PARA TU TEMA:

### PASO 1: Crear el producto (igual que antes)
- Ir a: https://visubloq.myshopify.com/admin/products/new
- Title: `VisuBloq Personalizado`
- Price: `19.99`
- Handle: `visubloq-personalizado`

### PASO 2: Modificar main-product.liquid
📍 https://visubloq.myshopify.com/admin/themes → Acciones → Editar código

**Ubicación:** `sections/main-product.liquid`

**¿Dónde pegar el código?**
- Buscar la línea que dice `</div>` cerca del final de la sección
- O buscar `{% endfor %}` si hay bucles de productos
- Pegar el código JavaScript ANTES del cierre de la sección principal

### PASO 3: Verificar product.json
**Ubicación:** `templates/product.json`

El archivo debería tener algo como:
```json
{
  "sections": {
    "main": {
      "type": "main-product"
    }
  },
  "order": ["main"]
}
```

## 🔧 CÓDIGO PARA MAIN-PRODUCT.LIQUID:

El código completo de `shopify-multiple-designs.liquid` va en `sections/main-product.liquid`

**Ubicación exacta:** Al final del archivo, antes del último `</div>` o `{% endunless %}`

## ❓ NECESITO VERIFICAR:

1. ¿Puedes acceder a https://visubloq.myshopify.com/admin/themes?
2. ¿Al hacer click en "Acciones → Editar código" ves la carpeta `sections/`?
3. ¿Hay un archivo llamado `main-product.liquid` en sections/?

## 🎯 PRÓXIMO PASO:
Una vez confirmes que puedes ver `sections/main-product.liquid`, te doy las instrucciones exactas de dónde pegar el código.
