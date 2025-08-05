# 📋 PANEL ADMIN VISUBLOQ PARA SHOPIFY - MÚLTIPLES DISEÑOS

## 🎯 Código actualizado para múltiples diseños por pedido

### OPCIÓN 1: Ver metafields en pedidos individuales (ACTUALIZADO)

Añade este código a `templates/order.liquid`:

```liquid
<!-- VisuBloq Admin Panel - Múltiples Diseños -->
{% assign visubloq_pieces = order.metafields.visubloq.pieces_list %}
{% assign visubloq_summary = order.metafields.visubloq.order_summary %}
{% assign visubloq_pdf = order.metafields.visubloq.instructions_pdf %}

{% comment %} Verificar si hay diseños VisuBloq en las propiedades de los productos {% endcomment %}
{% assign has_visubloq_designs = false %}
{% for line_item in order.line_items %}
  {% if line_item.properties['Diseño VisuBloq'] %}
    {% assign has_visubloq_designs = true %}
    {% break %}
  {% endif %}
{% endfor %}

{% if visubloq_pieces or visubloq_summary or has_visubloq_designs %}
<div style="background: #f8f9fa; border: 3px solid #ff6b35; border-radius: 10px; padding: 25px; margin: 25px 0; box-shadow: 0 4px 12px rgba(255, 107, 53, 0.1);">
  <h3 style="color: #ff6b35; margin: 0 0 20px 0; display: flex; align-items: center; font-size: 1.3em;">
    �️ Pedido VisuBloq - Múltiples Diseños
  </h3>
  
  {% comment %} Mostrar información general del pedido {% endcomment %}
  <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <strong>📊 Resumen del pedido:</strong><br>
    • Total de productos VisuBloq: {{ order.line_items | where: 'properties', 'Diseño VisuBloq' | size }}<br>
    • Valor total del pedido: {{ order.total_price | money }}<br>
    • Cliente: {{ order.customer.first_name }} {{ order.customer.last_name }} ({{ order.customer.email }})
  </div>
  
  {% comment %} Mostrar cada diseño individual {% endcomment %}
  {% for line_item in order.line_items %}
    {% if line_item.properties['Diseño VisuBloq'] %}
      <div style="background: white; border: 2px solid #e9ecef; border-radius: 8px; padding: 20px; margin: 15px 0;">
        <h4 style="color: #495057; margin: 0 0 15px 0; display: flex; align-items: center;">
          🎨 Diseño #{{ line_item.properties['ID Único'] | default: forloop.index }}
        </h4>
        
        <div style="display: grid; grid-template-columns: 200px 1fr; gap: 20px; margin-bottom: 15px;">
          {% if line_item.properties['Imagen del diseño'] %}
            <div>
              <img src="{{ line_item.properties['Imagen del diseño'] }}" alt="Diseño LEGO" 
                   style="width: 100%; height: auto; border: 1px solid #ddd; border-radius: 5px;">
            </div>
          {% endif %}
          
          <div>
            <p><strong>📊 Resumen:</strong> {{ line_item.properties['Diseño VisuBloq'] }}</p>
            <p><strong>🧱 Total piezas:</strong> {{ line_item.properties['Total de piezas'] }}</p>
            <p><strong>🎨 Colores:</strong> {{ line_item.properties['Colores diferentes'] }}</p>
            <p><strong>📐 Resolución:</strong> {{ line_item.properties['Resolución'] }}</p>
            <p><strong>📅 Generado:</strong> {{ line_item.properties['Generado el'] }}</p>
            <p><strong>💰 Precio unitario:</strong> {{ line_item.price | money }}</p>
          </div>
        </div>
        
        {% if line_item.properties['Lista de piezas'] %}
          <details style="margin-top: 15px;">
            <summary style="cursor: pointer; font-weight: bold; color: #007bff;">📋 Ver lista detallada de piezas</summary>
            <div style="background: #f8f9fa; padding: 15px; margin-top: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto;">
              <pre style="white-space: pre-wrap; font-size: 0.85em;">{{ line_item.properties['Lista de piezas'] }}</pre>
            </div>
          </details>
        {% endif %}
        
        <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 10px; border-radius: 5px; margin-top: 15px;">
          <strong>📦 Instrucciones para preparación:</strong><br>
          1. Localizar todas las piezas según la lista de colores<br>
          2. Verificar cantidades exactas por color<br>
          3. Empaquetar separadamente si hay múltiples diseños<br>
          4. Incluir identificador del diseño: #{{ line_item.properties['ID Único'] | default: forloop.index }}
        </div>
      </div>
    {% endif %}
  {% endfor %}
  
  {% comment %} Metafields legacy (compatibilidad) {% endcomment %}
  {% if visubloq_pieces %}
    <div style="margin: 20px 0; padding: 15px; background: #e7f3ff; border-radius: 5px;">
      <strong>🧱 Metafields legacy:</strong>
      <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-top: 5px;">
        {% assign pieces_json = visubloq_pieces | json %}
        <pre style="white-space: pre-wrap;">{{ pieces_json }}</pre>
      </div>
    </div>
  {% endif %}
  
  {% if visubloq_pdf %}
    <div style="margin: 20px 0;">
      <strong>📄 PDF de instrucciones (legacy):</strong>
      <a href="{{ visubloq_pdf }}" download="instrucciones-{{ order.name }}.pdf" 
         style="background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; margin-left: 10px;">
        📥 Descargar PDF
      </a>
    </div>
  {% endif %}
  
  <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-top: 25px;">
    <strong>✅ CHECKLIST PARA ADMIN:</strong><br>
    📋 Revisar cada diseño individual<br>
    🔍 Verificar disponibilidad de todas las piezas<br>
    📦 Preparar embalaje para múltiples diseños<br>
    🏷️ Etiquetar cada diseño con su ID único<br>
    📧 Confirmar preparación al cliente<br>
    🚚 Proceder con el envío
  </div>
</div>
{% endif %}
```

### OPCIÓN 2: Dashboard completo (más avanzado)

Para crear un dashboard completo de todos los pedidos VisuBloq, necesitarías:

1. **Crear una página de admin personalizada en Shopify**
2. **Usar Shopify Admin API para filtrar pedidos con metafields VisuBloq**
3. **Mostrar tabla con todos los pedidos VisuBloq**

## 🛠️ IMPLEMENTACIÓN PASO A PASO:

### Paso 1: Modifica un template de pedido en Shopify
1. Ve a tu Shopify Admin → Tienda online → Temas → Acciones → Editar código
2. Busca `templates/order.liquid` (o créalo si no existe)
3. Añade el código de arriba donde quieras que aparezca la información

### Paso 2: Para ver todos los pedidos VisuBloq (Dashboard)
Esto requiere una app de Shopify más avanzada. Como alternativa:

1. **Filtrar en Shopify Admin:** Ve a Pedidos y busca por propiedades personalizadas
2. **Usar tags:** Modificar el código para añadir tags automáticamente a pedidos VisuBloq
3. **Crear app de Shopify:** Para un dashboard completo personalizado

## 🎯 LO MÁS FÁCIL PARA EMPEZAR:

**RECOMENDACIÓN:** Empezar con OPCIÓN 1 - modificar template de pedido individual.
Esto te permitirá ver toda la información de VisuBloq cuando abras un pedido específico.

¿Quieres que te ayude a implementar alguna de estas opciones?
