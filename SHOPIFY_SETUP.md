# 🛒 CONFIGURACIÓN DE SHOPIFY PARA VISUBLOQ

## 📋 PASOS PARA CONFIGURAR SHOPIFY

### **1. Configurar Webhook de Pedidos**

1. **Ve a tu panel de Shopify Admin**
2. **Navegación**: Settings → Notifications
3. **Scroll hasta "Webhooks"**
4. **Clic en "Create webhook"**
5. **Configurar**:
   - **Event**: `Order payment`
   - **Format**: `JSON`
   - **URL**: `https://tu-dominio.com/backend/api/shopify-webhook.php`
   - **API version**: Latest
6. **Guardar**

### **2. Añadir Campo de Código de Seguimiento**

**Opción A: Notas del Pedido (Más fácil)**
1. **Ve a tu tema en Shopify**
2. **Edit code → Templates → cart.liquid**
3. **Añadir antes del botón de checkout**:
```html
<div class="cart-note">
  <label for="cart-note">Código de seguimiento VisuBloq (opcional):</label>
  <textarea name="note" id="cart-note" placeholder="Si tienes un código VB-XXXX-XXXX escríbelo aquí"></textarea>
</div>
```

**Opción B: Campo Personalizado (Más profesional)**
1. **Settings → Checkout**
2. **Scroll hasta "Order processing"**
3. **Additional scripts → Add to "Order status page"**:
```javascript
<script>
// Capturar código de seguimiento si existe
if (typeof Shopify !== 'undefined' && Shopify.checkout) {
  var note = Shopify.checkout.note;
  if (note && note.includes('VB-')) {
    console.log('Código VisuBloq detectado:', note);
  }
}
</script>
```

### **3. Configurar Metafields (Avanzado)**

1. **Settings → Metafields**
2. **Orders → Add definition**:
   - **Namespace**: `visubloq`
   - **Key**: `tracking_code`
   - **Name**: `VisuBloq Tracking Code`
   - **Type**: `Single line text`
3. **Guardar**

### **4. Template para Email de Confirmación**

Añadir en el email template (`Settings → Notifications → Order confirmation`):

```html
{% if order.note contains 'VB-' %}
<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
  <h3>🧱 Tu Diseño VisuBloq</h3>
  <p>Hemos detectado tu código de seguimiento en el pedido.</p>
  <p>Tu paquete personalizado se preparará con las piezas exactas de tu diseño.</p>
</div>
{% endif %}
```

## 🔧 CONFIGURACIÓN TÉCNICA

### **Variables de Entorno (config.php)**

```php
// Shopify Configuration
define('SHOPIFY_SHOP', 'tu-tienda.myshopify.com');
define('SHOPIFY_ACCESS_TOKEN', 'shpat_xxxxxxxxxxxxxxxxxxxxx');
define('SHOPIFY_WEBHOOK_SECRET', 'tu_secreto_webhook_muy_seguro');
```

### **Webhooks Requeridos**

1. **Order Payment**: Para pedidos pagados
   - URL: `/backend/api/shopify-webhook.php`
   - Events: `orders/paid`

2. **Order Update**: Para cambios en pedidos
   - URL: `/backend/api/shopify-webhook.php`
   - Events: `orders/updated`

## 🎯 FLUJO COMPLETO

```
1. Cliente genera diseño en VisuBloq
   ↓
2. Sistema genera código VB-TIMESTAMP-RANDOM
   ↓
3. Cliente copia código y va a Shopify
   ↓
4. Cliente añade producto al carrito
   ↓
5. Cliente pega código en "Notas del pedido"
   ↓
6. Cliente completa compra y paga
   ↓
7. Shopify envía webhook de pago
   ↓
8. Sistema busca código en las notas
   ↓
9. Sistema asocia pedido con diseño
   ↓
10. TÚ ves en dashboard: pedido + PDF + lista de piezas
```

## 🚨 PUNTOS IMPORTANTES

### **Seguridad**
- ✅ Verificar webhooks con HMAC
- ✅ Usar HTTPS en producción
- ✅ Validar códigos de seguimiento

### **UX del Cliente**
- ✅ Instrucciones claras sobre dónde poner el código
- ✅ Validación del formato del código
- ✅ Mensaje de confirmación

### **Backup Plan**
- ✅ Si no hay código: buscar por email + fecha
- ✅ Logs detallados para debugging
- ✅ Panel manual para asociar pedidos

## 📱 SCRIPT PARA SHOPIFY CHECKOUT

Añadir en `checkout.liquid` o en Additional Scripts:

```javascript
// VisuBloq Integration
(function() {
  // Detectar si hay código de seguimiento
  var noteField = document.querySelector('[name="note"]');
  if (noteField) {
    noteField.addEventListener('input', function() {
      var value = this.value;
      var codeMatch = value.match(/VB-\d+-[A-Z0-9]+/);
      
      if (codeMatch) {
        // Mostrar confirmación
        var confirmation = document.createElement('div');
        confirmation.style.cssText = 'background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-top: 10px;';
        confirmation.innerHTML = '✅ Código VisuBloq detectado: ' + codeMatch[0];
        
        // Remover confirmación anterior
        var existing = document.querySelector('.visubloq-confirmation');
        if (existing) existing.remove();
        
        confirmation.className = 'visubloq-confirmation';
        this.parentNode.appendChild(confirmation);
      }
    });
  }
})();
```

## 🎯 RESULTADO FINAL

Con esta configuración:
1. ✅ Cada diseño tiene código único
2. ✅ Cliente incluye código en pedido
3. ✅ Sistema asocia automáticamente
4. ✅ Dashboard muestra pedido + PDF + piezas
5. ✅ Preparas paquete con lista exacta