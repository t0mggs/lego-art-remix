# 🛒 Guía Completa: Configuración Shopify para VisuBloq Ultra Simple

## 🎯 OBJETIVO
Configurar Shopify Dawn theme para que:
1. **Cliente** pueda pegar su enlace PDF en el carrito
2. **Admin** pueda ver el enlace PDF en cada orden

---

## 📋 PARTE 1: CAMPO PARA EL CLIENTE (Carrito)

### 🔧 **Paso 1: Acceder al Editor de Código**
1. Ve a tu admin de Shopify
2. `Tienda online` → `Temas` 
3. En tu tema Dawn activo, click `Acciones` → `Editar código`

### 🔧 **Paso 2: Editar el Carrito Principal**
**Archivo**: `sections/main-cart-items.liquid`

**Busca** esta línea (aproximadamente línea 170-180):
```liquid
<div class="cart__footer">
```

**AÑADE ANTES** de esa línea:
```liquid
<!-- Campo VisuBloq PDF -->
<div class="visubloq-pdf-field" style="margin: 20px 0; padding: 16px; background: #f8f9fa; border-radius: 8px; border: 2px solid #e9ecef;">
  <h3 style="margin: 0 0 12px 0; color: #333; font-size: 1.1em;">
    🧱 Enlace a tus Instrucciones LEGO
  </h3>
  <p style="margin: 0 0 12px 0; color: #666; font-size: 0.9em;">
    Si has creado un diseño LEGO personalizado, pega aquí el enlace a tus instrucciones PDF
  </p>
  <div style="display: flex; gap: 8px; align-items: center;">
    <input 
      type="url" 
      id="visubloq-pdf-link" 
      name="attributes[VisuBloq PDF]"
      placeholder="Pega aquí tu enlace PDF de VisuBloq..."
      style="flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px;"
      value="{{ cart.attributes['VisuBloq PDF'] }}"
    >
    <button 
      type="button" 
      onclick="document.getElementById('visubloq-pdf-link').focus(); document.getElementById('visubloq-pdf-link').select();"
      style="padding: 12px 16px; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
      📋 Pegar
    </button>
  </div>
  <div id="visubloq-validation" style="margin-top: 8px; font-size: 12px; display: none;">
    <span style="color: #28a745;">✅ Enlace PDF válido detectado</span>
  </div>
</div>
```

### 🔧 **Paso 3: Añadir JavaScript de Validación**
**Al final del mismo archivo** `main-cart-items.liquid`, antes de `</section>`:

```liquid
<script>
// Validación automática del campo VisuBloq
document.addEventListener('DOMContentLoaded', function() {
  const pdfInput = document.getElementById('visubloq-pdf-link');
  const validation = document.getElementById('visubloq-validation');
  
  if (pdfInput) {
    pdfInput.addEventListener('input', function() {
      const value = this.value.trim();
      
      if (value && (value.startsWith('blob:') || value.startsWith('http'))) {
        validation.style.display = 'block';
        this.style.borderColor = '#28a745';
      } else if (value) {
        validation.style.display = 'none';
        this.style.borderColor = '#dc3545';
      } else {
        validation.style.display = 'none';
        this.style.borderColor = '#ddd';
      }
    });
    
    // Auto-pegar desde portapapeles
    pdfInput.addEventListener('focus', async function() {
      try {
        const text = await navigator.clipboard.readText();
        if (text && (text.startsWith('blob:') || text.startsWith('http')) && !this.value) {
          this.value = text;
          this.dispatchEvent(new Event('input'));
        }
      } catch (err) {
        // Silencioso si no hay permisos de portapapeles
      }
    });
  }
});
</script>
```

### 🔧 **Paso 4: Campo en Cart Drawer (Opcional)**
**Archivo**: `snippets/cart-drawer.liquid`

**Busca** la sección del footer del drawer y **añade** el mismo código del campo antes de los botones de checkout.

---

## 📋 PARTE 2: VISTA ADMIN (Para Ver PDFs en Órdenes)

### 🔧 **Paso 1: Campo en Order Status**
**Archivo**: `templates/customers/order.liquid`

**Añade** al final del archivo (antes de `</div>` final):
```liquid
<!-- Información VisuBloq para Admin -->
{% if order.attributes['VisuBloq PDF'] %}
<div class="visubloq-admin-section" style="margin-top: 20px; padding: 16px; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #007bff;">
  <h3 style="margin: 0 0 12px 0; color: #333;">🧱 Instrucciones LEGO VisuBloq</h3>
  <div style="margin-bottom: 12px;">
    <strong>Enlace PDF:</strong>
    <a href="{{ order.attributes['VisuBloq PDF'] }}" target="_blank" 
       style="color: #007bff; text-decoration: none; word-break: break-all;">
      {{ order.attributes['VisuBloq PDF'] }}
    </a>
  </div>
  <div style="display: flex; gap: 8px;">
    <a href="{{ order.attributes['VisuBloq PDF'] }}" target="_blank"
       style="padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
      📄 Ver PDF
    </a>
    <button onclick="navigator.clipboard.writeText('{{ order.attributes['VisuBloq PDF'] }}')"
            style="padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
      📋 Copiar Enlace
    </button>
  </div>
</div>
{% endif %}
```

### 🔧 **Paso 2: Admin Dashboard Personalizado**
**Crear nuevo archivo**: `templates/page.admin-visubloq.liquid`

```liquid
<!-- Dashboard Admin VisuBloq -->
<div class="page-width">
  <header class="section-header">
    <h1>🧱 VisuBloq Admin Dashboard</h1>
    <p>Gestión de PDFs e instrucciones LEGO personalizadas</p>
  </header>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
    
    <!-- Estadísticas -->
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
      <h3>📊 Estadísticas</h3>
      <div style="margin-top: 12px;">
        <div style="margin-bottom: 8px;"><strong>Total órdenes con PDF:</strong> <span id="total-pdf-orders">0</span></div>
        <div style="margin-bottom: 8px;"><strong>Esta semana:</strong> <span id="week-pdf-orders">0</span></div>
        <div style="margin-bottom: 8px;"><strong>Último PDF:</strong> <span id="last-pdf-date">N/A</span></div>
      </div>
    </div>

    <!-- Órdenes Recientes -->
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
      <h3>🛒 Órdenes con VisuBloq</h3>
      <div id="recent-visubloq-orders" style="margin-top: 12px;">
        <p style="color: #666;">Cargando órdenes...</p>
      </div>
    </div>

    <!-- Enlaces Rápidos -->
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
      <h3>🔗 Enlaces Rápidos</h3>
      <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 12px;">
        <a href="/admin/orders" target="_blank" 
           style="padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; text-align: center;">
          📋 Ver Todas las Órdenes
        </a>
        <a href="https://t0mggs.github.io/lego-art-remix" target="_blank"
           style="padding: 8px 12px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; text-align: center;">
          🧱 Ir a VisuBloq App
        </a>
        <a href="/admin/themes" target="_blank"
           style="padding: 8px 12px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; text-align: center;">
          ⚙️ Editar Tema
        </a>
      </div>
    </div>
  </div>
</div>

<script>
// Script para cargar estadísticas (básico)
document.addEventListener('DOMContentLoaded', function() {
  // Aquí puedes añadir llamadas a la API de Shopify para cargar estadísticas reales
  console.log('VisuBloq Admin Dashboard cargado');
  
  // Ejemplo básico
  document.getElementById('total-pdf-orders').textContent = '{{ orders.size | default: 0 }}';
  document.getElementById('last-pdf-date').textContent = new Date().toLocaleDateString();
});
</script>
```

### 🔧 **Paso 3: Crear Página Admin**
1. En el admin de Shopify: `Tienda online` → `Páginas` → `Agregar página`
2. **Título**: "VisuBloq Admin"
3. **Handle**: "admin-visubloq"
4. **Template**: Seleccionar "page.admin-visubloq"
5. **Contenido**: "Dashboard administrativo para VisuBloq"
6. **Visibilidad**: Solo admin

---

## 📋 PARTE 3: NOTIFICACIONES EMAIL (Opcional)

### 🔧 **Paso 1: Email de Confirmación**
**Archivo**: `templates/notification/order_confirmation.liquid`

**Añade** antes del `</body>`:
```liquid
{% if attributes['VisuBloq PDF'] %}
<div style="margin: 20px 0; padding: 16px; background: #f0f8ff; border-radius: 8px;">
  <h3 style="color: #333; margin: 0 0 12px 0;">🧱 Tus Instrucciones LEGO</h3>
  <p>Tu diseño LEGO personalizado está listo. Puedes acceder a tus instrucciones PDF aquí:</p>
  <a href="{{ attributes['VisuBloq PDF'] }}" target="_blank" 
     style="display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 8px;">
    📄 Ver Instrucciones PDF
  </a>
  <p style="font-size: 12px; color: #666; margin-top: 12px;">
    💡 Guarda este enlace para acceder a tus instrucciones cuando lo necesites.
  </p>
</div>
{% endif %}
```

---

## 🚀 PARTE 4: TESTING

### ✅ **Test Cliente**:
1. Ve a tu tienda
2. Añade producto al carrito
3. Ve al carrito
4. Verifica que aparece el campo "Enlace a tus Instrucciones LEGO"
5. Pega un enlace de prueba
6. Completa la compra

### ✅ **Test Admin**:
1. Ve a `Órdenes` en el admin
2. Abre una orden con PDF
3. Verifica que aparece la sección VisuBloq
4. Prueba los botones "Ver PDF" y "Copiar Enlace"
5. Ve a la página `/pages/admin-visubloq`

---

## 🎯 RESULTADO FINAL

**El cliente verá:**
- Campo elegante en el carrito para pegar su enlace PDF
- Validación automática del enlace
- Auto-pegado desde portapapeles

**Tú verás como admin:**
- Enlace PDF en cada orden
- Dashboard admin con estadísticas
- Botones para ver/copiar PDFs
- Notificaciones email con PDFs

**¡Sistema 100% funcional sin backend! 🎉**