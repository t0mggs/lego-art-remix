# 📋 PANEL ADMIN VISUBLOQ PARA SHOPIFY

## 🎯 Código para añadir a tu Shopify Admin

### OPCIÓN 1: Ver metafields en pedidos individuales

Añade este código a `templates/order.liquid` (si existe) o crea una página de admin personalizada:

```liquid
<!-- VisuBloq Admin Panel -->
{% assign visubloq_pieces = order.metafields.visubloq.pieces_list %}
{% assign visubloq_summary = order.metafields.visubloq.order_summary %}
{% assign visubloq_pdf = order.metafields.visubloq.instructions_pdf %}

{% if visubloq_pieces or order.properties['Diseño VisuBloq'] %}
<div style="background: #f8f9fa; border: 2px solid #28a745; border-radius: 8px; padding: 20px; margin: 20px 0;">
  <h3 style="color: #28a745; margin: 0 0 15px 0;">🎯 Pedido VisuBloq</h3>
  
  {% if order.properties['Diseño VisuBloq'] %}
    <div style="margin-bottom: 15px;">
      <strong>📊 Resumen:</strong> {{ order.properties['Diseño VisuBloq'] }}
    </div>
    <div style="margin-bottom: 15px;">
      <strong>📅 Generado:</strong> {{ order.properties['Generado en'] }}
    </div>
  {% endif %}
  
  {% if visubloq_pieces %}
    <div style="margin-bottom: 15px;">
      <strong>🧱 Lista de piezas por color:</strong>
      <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-top: 5px;">
        {% assign pieces_json = visubloq_pieces | json %}
        <!-- Aquí se mostrarían las piezas desde el JSON -->
        <pre style="white-space: pre-wrap;">{{ pieces_json }}</pre>
      </div>
    </div>
  {% endif %}
  
  {% if visubloq_pdf %}
    <div style="margin-bottom: 15px;">
      <strong>📄 Instrucciones PDF:</strong>
      <a href="{{ visubloq_pdf }}" download="instrucciones-{{ order.name }}.pdf" 
         style="background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; margin-left: 10px;">
        📥 Descargar PDF
      </a>
    </div>
  {% endif %}
  
  <div style="background: #e8f5e8; padding: 10px; border-radius: 5px; margin-top: 15px;">
    <strong>✅ PARA ADMIN:</strong> Este pedido contiene un diseño personalizado de VisuBloq. 
    Revisar la lista de piezas antes del envío.
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
