# 🛒 CONFIGURAR CAMPO PERSONALIZADO EN SHOPIFY

## OPCIÓN 1: Campo de notas del pedido (MÁS FÁCIL)

### A. En tu tema de Shopify:
1. Ve a **Online Store** → **Themes** → **Customize**
2. Busca la página **Cart** o **Checkout**
3. Añade un campo de texto personalizado

### B. Código Liquid para añadir:
```liquid
<div class="cart-note">
  <label for="cart-note">Código VisuBloq (si tienes uno):</label>
  <textarea id="cart-note" name="note" placeholder="Pega aquí tu código VB-XXXXX-XXXX">{{ cart.note }}</textarea>
</div>
```

---

## OPCIÓN 2: Campo personalizado avanzado

### A. En Shopify Admin:
1. **Settings** → **Metafields**
2. **Orders** → **Add definition**
3. Crear campo: `visubloq_code`

### B. En el tema, buscar `cart-form` y añadir:
```liquid
<div class="visubloq-code-field">
  <label for="visubloq-code">¿Tienes un código VisuBloq?</label>
  <input type="text" name="attributes[visubloq_code]" id="visubloq-code" 
         placeholder="VB-XXXXX-XXXX" 
         style="text-transform: uppercase;">
  <small>Si generaste un diseño personalizado, pega aquí tu código</small>
</div>
```

---

## OPCIÓN 3: Auto-rellenar desde URL (AUTOMÁTICO)

Si el cliente viene desde VisuBloq con código en URL:
`https://visubloq.com/products/visubloq-personalizado?visubloq_code=VB-123456`

### JavaScript para auto-rellenar:
```javascript
// Auto-rellenar código desde URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const visubloqCode = urlParams.get('visubloq_code');
    
    if (visubloqCode) {
        // Rellenar campo de notas
        const noteField = document.querySelector('[name="note"]');
        if (noteField) {
            noteField.value = 'Código VisuBloq: ' + visubloqCode;
        }
        
        // Rellenar campo personalizado
        const codeField = document.querySelector('[name="attributes[visubloq_code]"]');
        if (codeField) {
            codeField.value = visubloqCode;
        }
    }
});
```

---

## ✅ RECOMENDACIÓN: Empezar con OPCIÓN 1

La más fácil es usar el campo de **notas del pedido** que ya existe en Shopify.

### Instrucciones para el cliente:
> "Si generaste un diseño personalizado en VisuBloq, pega tu código VB-XXXXX-XXXX en las notas del pedido"