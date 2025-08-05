📋 GUÍA PASO A PASO: CONFIGURAR SHOPIFY PARA VISUBLOQ
======================================================

## 🎯 ORDEN CORRECTO DE IMPLEMENTACIÓN:

### ✅ PASO 1: CREAR PRODUCTO (5 minutos)
📍 https://visubloq.myshopify.com/admin/products/new

**Campos obligatorios:**
- Title: `VisuBloq Personalizado`
- Price: `19.99`
- Handle: `visubloq-personalizado` (automático)
- Description: `Diseño LEGO personalizado con todas las piezas incluidas`

⚠️ **IMPORTANTE:** El handle debe ser exactamente `visubloq-personalizado`

### ✅ PASO 2: ACTUALIZAR MAIN-PRODUCT.LIQUID (10 minutos)
📍 https://visubloq.myshopify.com/admin/themes → Acciones → Editar código

1. **Buscar carpeta:** `sections/`
2. **Abrir:** `main-product.liquid`
3. **Ir al final del archivo** (buscar la última línea con `</div>` o `{% endunless %}`)
4. **Copiar TODO el contenido de:** `shopify-multiple-designs.liquid`
5. **Pegar ANTES del último cierre** (`</div>` o `{% endunless %}`)
6. **Guardar**

⚠️ **IMPORTANTE:** NO toques `templates/product.json` - solo edita `sections/main-product.liquid`

**¿Qué hace este código?**
- Detecta cuando llegan datos de diseño desde VisuBloq
- Muestra la imagen del diseño en la página del producto
- Añade información del diseño al carrito automáticamente
- Permite múltiples diseños en el mismo carrito

### ✅ PASO 3: ACTUALIZAR ORDER.LIQUID (10 minutos)
📍 Mismo editor de temas

**Si `templates/order.liquid` existe:**
1. Abrir el archivo
2. Reemplazar TODO el contenido con el código del panel admin

**Si NO existe:**
1. Click en "Add a new template"
2. Seleccionar "order"
3. Pegar el código del panel admin
4. Guardar

**¿Qué hace este código?**
- Muestra panel especial para pedidos con diseños VisuBloq
- Visualiza cada diseño individual con su imagen
- Lista las piezas necesarias por color
- Da instrucciones de preparación al admin

### ✅ PASO 4: PROBAR EL FLUJO COMPLETO (15 minutos)

**4.1 Probar la aplicación VisuBloq:**
1. Ir a: https://t0mggs.github.io/lego-art-remix/
2. Subir imagen y generar diseño
3. Click en "🏗️ CONSTRUIR"
4. ¿Se abre la página del producto correctamente?

**4.2 Probar la página del producto:**
1. ¿Aparece la imagen del diseño?
2. ¿Se muestran las especificaciones?
3. ¿El botón dice "Añadir este diseño al carrito"?

**4.3 Probar múltiples diseños:**
1. Añadir primer diseño al carrito
2. Crear segundo diseño en VisuBloq
3. Añadir segundo diseño al carrito
4. ¿El carrito muestra ambos como productos separados?

**4.4 Probar compra y panel admin:**
1. Finalizar compra de prueba (usar modo test)
2. Ir a: https://visubloq.myshopify.com/admin/orders
3. Abrir el pedido recién creado
4. ¿Aparece el panel VisuBloq con todas las imágenes?

## 🔧 CÓDIGOS COMPLETOS PARA COPIAR:

### CÓDIGO PARA MAIN-PRODUCT.LIQUID:
```
Ver archivo: shopify-multiple-designs.liquid
(Copiar TODO el contenido tal como está)
```

### CÓDIGO PARA ORDER.LIQUID:
```
Ver archivo: ADMIN-PANEL-SHOPIFY.md
(Desde la línea que dice "<!-- VisuBloq Admin Panel"
hasta la línea que dice "{% endif %}")
```

## ❗ ERRORES COMUNES Y SOLUCIONES:

### Error: "No puede acceder al sitio web"
**Causa:** El producto no existe o el handle es incorrecto
**Solución:** Verificar que el producto existe con handle `visubloq-personalizado`

### Error: No aparece información del diseño
**Causa:** El código no está en main-product.liquid o hay error de sintaxis
**Solución:** Verificar que el código está pegado correctamente en `sections/main-product.liquid`

### Error: Panel admin no aparece
**Causa:** order.liquid no actualizado o sin datos de VisuBloq
**Solución:** Verificar que order.liquid tiene el código correcto

### Error: Múltiples diseños se sobrescriben
**Causa:** Falta el unique_id en el sistema
**Solución:** Verificar que el código de shopify-metafields.js está actualizado

## 🎯 CHECKLIST FINAL:

- [ ] Producto "VisuBloq Personalizado" creado
- [ ] Handle es exactamente "visubloq-personalizado"
- [ ] main-product.liquid actualizado con código completo
- [ ] order.liquid actualizado con panel admin
- [ ] Probado: crear diseño → botón CONSTRUIR
- [ ] Probado: página del producto muestra diseño
- [ ] Probado: añadir múltiples diseños al carrito
- [ ] Probado: panel admin muestra información completa

## 🚀 PRÓXIMOS PASOS DESPUÉS DE LA CONFIGURACIÓN:

1. **Configurar modo de prueba en pagos**
2. **Hacer compras de prueba completas**
3. **Configurar inventario y envíos**
4. **Personalizar diseño visual del producto**
5. **Configurar notificaciones automáticas**

======================================================
📞 Si tienes algún problema en cualquier paso, dímelo 
y te ayudo a solucionarlo específicamente.
======================================================
