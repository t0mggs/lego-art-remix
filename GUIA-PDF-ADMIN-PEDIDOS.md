# 📋 GUÍA: Ver PDFs de Clientes en Admin de Pedidos

## 🎯 **OBJETIVO:**
Mostrar el enlace PDF de VisuBloq en la sección "Notas" de cada pedido en Shopify Admin para que puedas acceder a los diseños de tus clientes.

---

## ✅ **PASO 1: Modificar el Campo de Checkout**

### En Shopify Admin → Settings → Checkout → Additional Scripts:

**REEMPLAZAR** el código anterior con este nuevo:

```html
<script>
// Campo VisuBloq PDF mejorado con validación
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
            placeholder="blob:https://... (pega aquí tu enlace de VisuBloq)"
            onpaste="validateVisuBloqPDF(this)"
            onchange="validateVisuBloqPDF(this)"
          />
          <div id="visubloq_validation" style="margin-top: 5px; font-size: 12px;"></div>
        </div>
        <p style="font-size: 12px; color: #666; margin-top: 5px;">
          💡 Si has creado un diseño LEGO en VisuBloq, pega aquí el enlace para acceder a tus instrucciones
        </p>
      </div>
    `;
    
    checkoutForm.insertAdjacentHTML('beforeend', pdfField);
  }
});

function validateVisuBloqPDF(input) {
  const validation = document.getElementById('visubloq_validation');
  const value = input.value.trim();
  
  if (!value) {
    validation.innerHTML = '';
    return;
  }
  
  if (value.startsWith('blob:') || value.startsWith('https://')) {
    validation.innerHTML = '<span style="color: #28a745;">✅ Enlace PDF válido</span>';
    
    // Agregar también a notas del pedido
    addToOrderNotes(value);
  } else {
    validation.innerHTML = '<span style="color: #dc3545;">❌ Enlace no válido. Debe empezar por "blob:" o "https://"</span>';
  }
}

function addToOrderNotes(pdfUrl) {
  // Buscar campo de notas si existe
  const notesField = document.querySelector('textarea[name="note"]');
  if (notesField) {
    const noteText = `VisuBloq PDF: ${pdfUrl}`;
    
    if (!notesField.value.includes('VisuBloq PDF:')) {
      notesField.value = notesField.value ? 
        `${notesField.value}\n\n${noteText}` : 
        noteText;
    }
  } else {
    // Si no hay campo de notas, crear uno oculto
    const hiddenNote = document.createElement('input');
    hiddenNote.type = 'hidden';
    hiddenNote.name = 'note';
    hiddenNote.value = `VisuBloq PDF: ${pdfUrl}`;
    document.querySelector('form').appendChild(hiddenNote);
  }
}
</script>
```

---

## ✅ **PASO 2: Personalizar Admin de Pedidos**

### Opción A: Via Additional Scripts (MÁS FÁCIL)

**En Shopify Admin → Settings → Checkout → Order status page:**

```html
<script>
// Script para mostrar PDF en admin de pedidos
if (window.location.href.includes('/admin/orders/')) {
  document.addEventListener('DOMContentLoaded', function() {
    // Buscar el atributo VisuBloq PDF
    const orderAttributes = document.querySelectorAll('[data-order-attribute]');
    let pdfUrl = null;
    
    orderAttributes.forEach(attr => {
      if (attr.textContent.includes('VisuBloq PDF')) {
        pdfUrl = attr.textContent.split(':')[1]?.trim();
      }
    });
    
    if (pdfUrl) {
      // Crear sección para mostrar PDF
      const pdfSection = document.createElement('div');
      pdfSection.style.cssText = `
        background: #f0f8ff;
        border: 2px solid #007bff;
        border-radius: 8px;
        padding: 15px;
        margin: 20px 0;
      `;
      
      pdfSection.innerHTML = `
        <h3 style="color: #007bff; margin: 0 0 10px 0;">🧱 PDF VisuBloq del Cliente</h3>
        <div style="background: white; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
          <strong>Enlace:</strong><br>
          <code style="word-break: break-all; font-size: 11px;">${pdfUrl}</code>
        </div>
        <a href="${pdfUrl}" target="_blank" 
           style="padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
          📄 Ver PDF del Cliente
        </a>
      `;
      
      // Insertar en la página de pedido
      const orderDetails = document.querySelector('.order-details, .main-content');
      if (orderDetails) {
        orderDetails.insertBefore(pdfSection, orderDetails.firstChild);
      }
    }
  });
}
</script>
```

### Opción B: Via Webhook (MÁS AVANZADO)

**Crear archivo:** `shopify-admin-pdf-integration.php`

```php
<?php
// Webhook para mostrar PDFs en admin
header('Content-Type: application/json');

if ($_POST) {
    $order = json_decode(file_get_contents('php://input'), true);
    
    // Buscar atributo VisuBloq PDF
    $pdfUrl = null;
    if (isset($order['attributes'])) {
        foreach ($order['attributes'] as $attr) {
            if ($attr['name'] === 'VisuBloq PDF') {
                $pdfUrl = $attr['value'];
                break;
            }
        }
    }
    
    if ($pdfUrl) {
        // Agregar nota al pedido con el PDF
        $note = "🧱 VisuBloq PDF: " . $pdfUrl;
        
        // Aquí puedes usar Shopify API para actualizar las notas
        updateOrderNote($order['id'], $note);
    }
}

function updateOrderNote($orderId, $note) {
    // Implementar llamada a Shopify Admin API
    $shopifyUrl = 'https://YOUR-SHOP.myshopify.com/admin/api/2023-10/orders/' . $orderId . '.json';
    
    $data = [
        'order' => [
            'id' => $orderId,
            'note' => $note
        ]
    ];
    
    // Hacer PUT request a Shopify API
    // (necesitas token de acceso admin)
}
?>
```

---

## ✅ **PASO 3: Verificar Implementación**

### 1. **Test del Campo de Checkout:**
```
1. Ir a tu tienda/checkout
2. Agregar producto al carrito
3. Verificar que aparece el campo "🧱 Enlace PDF VisuBloq"
4. Pegar un enlace de prueba
5. Verificar validación
```

### 2. **Test del Admin:**
```
1. Completar una compra con PDF
2. Ir a Admin → Orders
3. Abrir el pedido
4. Verificar que aparece la sección azul con el PDF
```

---

## ✅ **PASO 4: Personalización Avanzada**

### Agregar Vista Previa del PDF:

```javascript
// Función para mostrar vista previa
function showPDFPreview(pdfUrl) {
  const preview = document.createElement('div');
  preview.innerHTML = `
    <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; margin-top: 10px;">
      <h4>Vista Previa:</h4>
      <img src="${pdfUrl}" style="max-width: 200px; height: auto; border-radius: 4px;">
    </div>
  `;
  return preview;
}
```

### Agregar Botón de Descarga:

```javascript
// Botón para descargar PDF
function createDownloadButton(pdfUrl) {
  const button = document.createElement('a');
  button.href = pdfUrl;
  button.download = 'visubloq-client-design.png';
  button.style.cssText = `
    display: inline-block;
    padding: 8px 16px;
    background: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    margin-left: 10px;
    font-weight: bold;
  `;
  button.textContent = '📥 Descargar PDF';
  return button;
}
```

---

## 🎯 **RESULTADO FINAL:**

### En Checkout:
- ✅ Campo validado para enlace PDF
- ✅ Mensaje de confirmación visual
- ✅ Enlace guardado en atributos del pedido

### En Admin de Pedidos:
- ✅ Sección azul destacada con PDF del cliente
- ✅ Enlace directo para ver PDF
- ✅ Opción de descarga
- ✅ Vista previa (opcional)

### En Notas del Pedido:
- ✅ Enlace PDF guardado automáticamente
- ✅ Fácil acceso desde cualquier lugar del admin

---

## 🚨 **IMPORTANTE:**

1. **Los enlaces `blob:`** son temporales - considera implementar sistema de guardado permanente
2. **Backup regular** de los PDFs importantes
3. **Permisos de admin** necesarios para algunas funciones

**¿Quieres que implemente alguna de estas opciones específicamente?** 🚀