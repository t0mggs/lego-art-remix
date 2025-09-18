# 🚀 GUÍA COMPLETA: Implementar VisuBloq en Shopify Dawn

## ✅ **PASO 1: Implementar template order.liquid**

### En Shopify Admin:
1. **Acceder al Editor de Código:**
   ```
   Shopify Admin → Online Store → Themes → Actions → Edit Code
   ```

2. **Localizar el archivo:**
   ```
   templates → order.liquid
   ```

3. **REEMPLAZAR TODO el contenido** con el código de `order-sin-errores.liquid`
   - ⚠️ **IMPORTANTE:** Hacer backup del archivo original primero
   - Copiar todo el contenido del archivo `order-sin-errores.liquid`
   - Pegar y reemplazar completamente

4. **Guardar cambios**

---

## ✅ **PASO 2: Agregar campo de checkout**

### En Shopify Admin → Settings → Checkout:
1. **Customer information → Additional scripts**
2. **Agregar este código:**

```html
<script>
// Agregar campo para PDF de VisuBloq
document.addEventListener('DOMContentLoaded', function() {
  const checkoutForm = document.querySelector('[data-step="contact_information"], .step__sections');
  
  if (checkoutForm) {
    const pdfField = `
      <div class="field field--optional">
        <div class="field__input-wrapper">
          <label class="field__label" for="visubloq_pdf">
            🧱 Enlace PDF VisuBloq (opcional)
          </label>
          <input 
            id="visubloq_pdf" 
            name="attributes[VisuBloq PDF]" 
            type="url" 
            class="field__input"
            placeholder="https://... (pega aquí tu enlace de VisuBloq)"
          />
        </div>
        <p style="font-size: 12px; color: #666; margin-top: 5px;">
          💡 Si has creado un diseño LEGO en VisuBloq, pega aquí el enlace para acceder a tus instrucciones
        </p>
      </div>
    `;
    
    checkoutForm.insertAdjacentHTML('beforeend', pdfField);
  }
});
</script>
```

---

## ✅ **PASO 3: Verificar que VisuBloq funciona**

### Comprobar VisuBloq Ultra Simple:
1. **Ir a:** https://t0mggs.github.io/lego-art-remix
2. **Generar un PDF** con cualquier imagen
3. **Verificar que aparece el modal** con enlace directo
4. **Copiar el enlace** que aparece

---

## ✅ **PASO 4: Prueba completa del flujo**

### Simular compra completa:
1. **En tu tienda Shopify:**
   - Agregar cualquier producto al carrito
   - Ir al checkout

2. **En el campo VisuBloq PDF:**
   - Pegar el enlace que copiaste de VisuBloq
   - Completar la compra

3. **Verificar order page:**
   - Ir a `tu-tienda.myshopify.com/account/orders`
   - Abrir el pedido recién creado
   - **DEBE aparecer:** Sección azul con "🧱 Instrucciones LEGO VisuBloq"

---

## 🔍 **CÓMO PROBAR SIN COMPRAR**

### Método 1: Usar Development Store
```
Si tienes Shopify Partners → crear tienda de prueba → seguir pasos normales
```

### Método 2: Simular con URL directa
```
https://TU-TIENDA.myshopify.com/account/orders/NUMERO-PEDIDO
```

### Método 3: Inspector de código
```
F12 → Console → ejecutar:
document.body.innerHTML += '<div style="background:#f0f8ff;padding:20px;margin:20px;border:2px solid #007bff;border-radius:8px;"><h3 style="color:#007bff;">🧱 Test VisuBloq</h3><p>Campo funcionando correctamente</p></div>';
```

---

## ❌ **TROUBLESHOOTING**

### Si no aparece la sección PDF:
1. **Verificar que el campo se llama exactamente:** `VisuBloq PDF`
2. **Comprobar que hay contenido** en el atributo
3. **Revisar que el template se guardó** correctamente

### Si hay errores de traducción:
- ✅ **Ya solucionados** - la nueva versión no usa traducciones

### Si el enlace no funciona:
1. **Verificar que el enlace** empieza por `blob:` o `https://`
2. **Comprobar que VisuBloq Ultra Simple** está activo

---

## 🎯 **RESULTADO ESPERADO**

### En la página de pedido deberías ver:
```
┌─────────────────────────────────────┐
│ 🧱 Instrucciones LEGO VisuBloq      │
│                                     │
│ Tu enlace PDF:                      │
│ [blob:https://...]                  │
│                                     │
│ [📄 Ver PDF] [📋 Copiar Enlace]    │
└─────────────────────────────────────┘
```

**¿Todo listo para implementar?** 🚀