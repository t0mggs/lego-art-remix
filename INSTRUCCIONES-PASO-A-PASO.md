# 📋 INSTRUCCIONES PASO A PASO: Ver PDFs de Clientes en Admin de Pedidos

## 🎯 **OBJETIVO FINAL:**
- ✅ Cliente pega enlace PDF en checkout
- ✅ PDF aparece en **notas del pedido** en admin
- ✅ PDF aparece **destacado** en notificaciones de admin
- ✅ Fácil acceso para ver/descargar diseños de clientes

---

## 🚀 **IMPLEMENTACIÓN COMPLETA:**

### **PASO 1: Configurar Campo de Checkout**

**📍 Ubicación:** `Shopify Admin → Settings → Checkout → Additional Scripts`

**✂️ Acción:** COPIAR y PEGAR este código:

```html
<!-- COPIAR TODO ESTE CÓDIGO -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🧱 VisuBloq Checkout Script iniciado');
    
    const checkoutForm = document.querySelector('[data-step="contact_information"], .step__sections, .section--contact-information');
    
    if (!checkoutForm) {
        setTimeout(arguments.callee, 1000);
        return;
    }
    
    const fieldHTML = `
        <div class="field field--optional" style="background: linear-gradient(135deg, #f0f8ff, #e6f3ff); border: 1px solid #007bff; border-radius: 8px; padding: 15px; margin: 15px 0;">
            <div class="field__input-wrapper">
                <label class="field__label" for="visubloq_pdf" style="font-weight: bold; color: #007bff;">
                    🧱 Enlace PDF VisuBloq (opcional)
                </label>
                <input 
                    id="visubloq_pdf" 
                    name="attributes[VisuBloq PDF]" 
                    type="url" 
                    class="field__input"
                    placeholder="blob:https://... (pega aquí tu enlace de VisuBloq)"
                    style="font-family: monospace; font-size: 12px; border: 2px solid #007bff !important; border-radius: 6px !important;"
                />
                <div id="visubloq_validation" style="margin-top: 8px; font-size: 13px;"></div>
            </div>
            <div style="margin-top: 8px; font-size: 12px; color: #666;">
                💡 Si has creado un diseño LEGO en VisuBloq, pega aquí el enlace para que podamos acceder a tus instrucciones personalizadas
            </div>
        </div>
    `;
    
    checkoutForm.insertAdjacentHTML('beforeend', fieldHTML);
    
    // Configurar validación y notas
    const input = document.getElementById('visubloq_pdf');
    if (input) {
        input.addEventListener('input', function() {
            const value = this.value.trim();
            const validation = document.getElementById('visubloq_validation');
            
            if (!value) {
                validation.innerHTML = '';
                return;
            }
            
            if (value.startsWith('blob:') || value.startsWith('https://')) {
                validation.innerHTML = '<span style="color: #28a745; background: #d4edda; padding: 6px 10px; border-radius: 4px;">✅ Enlace PDF válido detectado</span>';
                
                // Agregar a notas del pedido
                let notesField = document.querySelector('textarea[name="note"]') || document.querySelector('input[name="note"]');
                
                if (!notesField) {
                    notesField = document.createElement('input');
                    notesField.type = 'hidden';
                    notesField.name = 'note';
                    document.querySelector('form').appendChild(notesField);
                }
                
                const noteText = `🧱 VisuBloq PDF: ${value}`;
                if (!notesField.value.includes('VisuBloq PDF:')) {
                    notesField.value = notesField.value ? `${notesField.value}\\n\\n${noteText}` : noteText;
                }
                
            } else {
                validation.innerHTML = '<span style="color: #dc3545; background: #f8d7da; padding: 6px 10px; border-radius: 4px;">❌ Enlace no válido. Debe empezar por "blob:" o "https://"</span>';
            }
        });
    }
});
</script>
<!-- FIN DEL CÓDIGO -->
```

---

### **PASO 2: Configurar Template de Admin**

**📍 Ubicación:** `Shopify Admin → Settings → Notifications → Order confirmation (for admin)`

**✂️ Acción:** REEMPLAZAR todo el contenido con:

```liquid
<!-- COPIAR TODO ESTE CÓDIGO LIQUID -->
<div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
  
  <!-- SECCIÓN PDF VISUBLOQ - MUY DESTACADA -->
  {% if order.attributes['VisuBloq PDF'] and order.attributes['VisuBloq PDF'] != blank %}
  <div style="background: linear-gradient(135deg, #ff6b35, #e55a2b); color: white; padding: 25px; margin: 0 0 20px 0; border-radius: 12px; box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);">
    <div style="display: flex; align-items: center; margin-bottom: 20px;">
      <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 50%; margin-right: 20px; font-size: 32px;">
        🧱
      </div>
      <div>
        <h2 style="margin: 0; font-size: 24px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">¡DISEÑO LEGO PERSONALIZADO!</h2>
        <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 16px;">El cliente ha creado un diseño único en VisuBloq</p>
      </div>
    </div>
    
    <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
      <p style="margin: 0 0 12px 0; font-weight: bold; font-size: 16px;">📎 ENLACE AL PDF DE INSTRUCCIONES:</p>
      <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 6px; font-family: monospace; font-size: 12px; word-break: break-all; line-height: 1.6; border: 2px dashed rgba(255,255,255,0.3);">
        {{ order.attributes['VisuBloq PDF'] }}
      </div>
    </div>
    
    <div style="text-align: center;">
      <a href="{{ order.attributes['VisuBloq PDF'] }}" 
         style="display: inline-block; background: rgba(255,255,255,0.9); color: #ff6b35; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 0 10px 10px 0; font-size: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);"
         target="_blank">
        📄 VER PDF DE INSTRUCCIONES
      </a>
      <a href="{{ order.attributes['VisuBloq PDF'] }}" 
         download="visubloq-cliente-{{ order.order_number }}.png"
         style="display: inline-block; background: rgba(255,255,255,0.2); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 0 10px 10px 0; font-size: 16px; border: 2px solid rgba(255,255,255,0.5);">
        📥 DESCARGAR PDF
      </a>
    </div>
    
    <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid rgba(255,255,255,0.3); text-align: center;">
      <p style="margin: 0; font-size: 14px; opacity: 0.9;">
        🎯 <strong>ACCIÓN REQUERIDA:</strong> Revisar diseño personalizado y procesar pedido según las instrucciones del PDF
      </p>
    </div>
  </div>
  {% endif %}
  
  <!-- Información Standard del Pedido -->
  <div style="background: white; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden;">
    
    <!-- Header del Pedido -->
    <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #dee2e6;">
      <h2 style="margin: 0; color: #333;">Nuevo Pedido #{{ order.order_number }}</h2>
      <p style="margin: 5px 0 0 0; color: #666;">{{ order.created_at | date: '%d de %B de %Y a las %H:%M' }}</p>
    </div>
    
    <!-- Cliente -->
    <div style="padding: 20px; border-bottom: 1px solid #f0f0f0;">
      <h3 style="margin: 0 0 15px 0; color: #333;">👤 Cliente</h3>
      <p style="margin: 0; font-weight: bold; font-size: 16px;">{{ order.customer.first_name }} {{ order.customer.last_name }}</p>
      <p style="margin: 0; color: #666;">{{ order.customer.email }}</p>
      <p style="margin: 10px 0 0 0; font-weight: bold; font-size: 18px; color: #28a745;">Total: {{ order.total_price | money_with_currency }}</p>
    </div>
    
    <!-- Productos -->
    <div style="padding: 20px;">
      <h3 style="margin: 0 0 15px 0; color: #333;">🛍️ Productos</h3>
      {% for line_item in order.line_items %}
      <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
        <div>
          <p style="margin: 0; font-weight: bold;">{{ line_item.title }}</p>
          {% if line_item.variant.title != 'Default Title' %}
          <p style="margin: 0; color: #666; font-size: 12px;">{{ line_item.variant.title }}</p>
          {% endif %}
        </div>
        <div style="text-align: right;">
          <p style="margin: 0;">x{{ line_item.quantity }}</p>
          <p style="margin: 0; font-weight: bold;">{{ line_item.final_line_price | money }}</p>
        </div>
      </div>
      {% endfor %}
    </div>
  </div>
  
  <!-- Notas del Pedido -->
  {% if order.note and order.note != blank %}
  <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 15px 0; color: #856404;">📝 NOTAS DEL PEDIDO</h3>
    <div style="background: white; padding: 15px; border-radius: 6px; color: #856404; font-family: monospace; white-space: pre-wrap;">{{ order.note }}</div>
  </div>
  {% endif %}
  
</div>
<!-- FIN DEL CÓDIGO LIQUID -->
```

---

### **PASO 3: Verificación y Pruebas**

#### **3.1 Test del Checkout:**
```
1. Ir a tu-tienda.myshopify.com
2. Agregar producto al carrito → Checkout
3. Verificar campo azul "🧱 Enlace PDF VisuBloq"
4. Pegar enlace de prueba: blob:https://ejemplo
5. Ver mensaje "✅ Enlace PDF válido detectado"
```

#### **3.2 Test de Admin:**
```
1. Completar compra con PDF
2. Revisar email de confirmación de admin
3. DEBE verse sección NARANJA muy destacada
4. Probar botones "VER PDF" y "DESCARGAR"
```

#### **3.3 Test de Notas:**
```
1. Admin → Orders → Abrir pedido
2. Scroll hasta "Notas"
3. DEBE aparecer: "🧱 VisuBloq PDF: [enlace]"
```

---

### **PASO 4: Configuración Avanzada (Opcional)**

#### **4.1 Auto-backup de PDFs:**
```javascript
// Agregar al checkout script
function backupPDF(url) {
  fetch('/admin/api/2023-10/metafields.json', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      metafield: {
        namespace: 'visubloq',
        key: 'pdf_backup',
        value: url
      }
    })
  });
}
```

#### **4.2 Webhooks para notificaciones:**
```php
// webhook-visubloq-notification.php
if (isset($_POST['attributes']['VisuBloq PDF'])) {
    $pdfUrl = $_POST['attributes']['VisuBloq PDF'];
    $orderNumber = $_POST['order_number'];
    
    // Enviar notificación especial
    sendVisuBloqNotification($orderNumber, $pdfUrl);
}
```

---

## ✅ **RESULTADO FINAL:**

### **En Checkout:**
- ✅ Campo azul destacado para PDF
- ✅ Validación visual inmediata
- ✅ Auto-guardado en notas

### **En Admin Email:**
- ✅ Sección NARANJA muy visible
- ✅ Enlace directo al PDF
- ✅ Botón de descarga
- ✅ Instrucciones claras

### **En Admin Dashboard:**
- ✅ PDF visible en notas del pedido
- ✅ Fácil copy/paste del enlace
- ✅ Acceso permanente al diseño

---

## 🚨 **IMPORTANTE:**

1. **Enlaces `blob:`** son temporales - considera implementar backup automático
2. **Permisos de admin** necesarios para algunas funciones avanzadas
3. **Test completo** antes de lanzar en producción

**¿Listo para implementar? ¡Copia y pega paso a paso!** 🚀