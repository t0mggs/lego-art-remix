# 🚀 GUÍA PASO A PASO - VISUBLOQ + SHOPIFY

## PASO 1: CONFIGURAR NGROK

### A. Verificar ubicación de ngrok
```bash
# Busca donde tienes ngrok.exe
where ngrok
```

### B. Si no lo encuentras:
1. Ve a: https://ngrok.com/download
2. Descarga el ZIP para Windows
3. Extrae `ngrok.exe` a cualquier carpeta (ej: `C:\tools\ngrok\`)
4. Añade esa carpeta al PATH o usa ruta completa

### C. Iniciar ngrok:
```bash
# Si está en PATH:
ngrok http 80

# Si usas ruta completa:
C:\tools\ngrok\ngrok.exe http 80
```

### D. Obtener URL:
- Copia la URL `https://` que aparece (ej: `https://abc123.ngrok-free.app`)

---

## PASO 2: CONFIGURAR WEBHOOK SHOPIFY

### A. Ir a Shopify Admin:
1. Settings → Notifications
2. Scroll down → Webhooks
3. Create webhook

### B. Configuración del webhook:
- **Event**: `Order payment` (solo pedidos pagados)
- **Format**: `JSON`
- **URL**: `TU-URL-NGROK/VisuBloq/app/backend/api/shopify-webhook.php`
- **API version**: Última disponible

---

## PASO 3: CONFIGURAR FILTRO POR PRODUCTO

Necesitamos el **Product ID** de tu producto específico en Shopify:

### A. Encontrar Product ID:
1. Ve a Products en Shopify Admin
2. Abre tu producto específico
3. Mira la URL: `https://admin.shopify.com/.../products/AQUI_ESTA_EL_ID`

### B. Configurar filtro:
- Solo procesar pedidos que contengan ese producto específico

---

## PASO 4: FLUJO COMPLETO

```
Cliente genera PDF → Código VB-123456-ABC
                           ↓
Cliente compra producto → Menciona código en notas
                           ↓
Webhook captura pedido → Busca código en notas
                           ↓
SI encuentra código → PDF se marca como "COMPRADO"
                           ↓
Dashboard muestra → Solo PDFs comprados
```

---

## ¿DÓNDE ESTÁ TU NGROK?

Dime la ruta exacta donde tienes `ngrok.exe` y continuamos.