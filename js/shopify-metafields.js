// 🎯 INTEGRACIÓN VISUBLOQ CON SHOPIFY
// =================================
// FLUJO CORRECTO: Cliente crea diseño → Compra → Datos se asocian automáticamente al pedido

// Configuración
const SHOPIFY_CONFIG = {
    shop: 'VisuBloq.myshopify.com',
    accessToken: window.SHOPIFY_TOKEN || 'DEMO_MODE', // Token se carga desde shopify-config.js
    apiVersion: '2024-01'
};

// 💳 Función principal: Construir diseño actual
async function buyCurrentDesign() {
    try {
        // Verificar que hay un diseño cargado
        if (!step4Canvas || step4Canvas.width === 0) {
            alert('❌ No hay ningún diseño cargado. Carga una imagen y procésala primero.');
            return;
        }
        
        // Verificar modo demo
        if (SHOPIFY_CONFIG.accessToken === 'DEMO_MODE' || !SHOPIFY_CONFIG.accessToken) {
            console.log('🧪 Modo demo: redirigiendo directamente a Shopify sin metafields');
        }
        
        // Generar datos del diseño
        const step4PixelArray = getPixelArrayFromCanvas(step4Canvas);
        const resultImage = isBleedthroughEnabled()
            ? revertDarkenedImage(step4PixelArray, getDarkenedStudsToStuds(ALL_BRICKLINK_SOLID_COLORS.map((color) => color.hex)))
            : step4PixelArray;
        
        const studMap = getUsedPixelsStudMap(resultImage);
        const totalPieces = Object.values(studMap).reduce((sum, count) => sum + count, 0);
        const pieceTypes = Object.keys(studMap).length;
        
        // Crear imagen del diseño para enviar a Shopify
        const designImageDataURL = step4CanvasUpscaled.toDataURL('image/png', 0.8);
        
        // Crear resumen del diseño para enviar a Shopify
        const designData = {
            total_pieces: totalPieces,
            piece_types: pieceTypes,
            resolution: `${targetResolution[0]}x${targetResolution[1]}`,
            pieces_detail: studMap,
            design_image: designImageDataURL,
            generated_at: new Date().toISOString(),
            unique_id: Date.now() // ID único para diferenciar múltiples diseños
        };
        
        // Codificar datos para URL
        const encodedData = encodeURIComponent(JSON.stringify(designData));
        
        // Construir URL del producto con datos del diseño
        const productUrl = `https://visubloq.com/products/visubloq-personalizado?design_data=${encodedData}`;
        
        // Guardar diseño en localStorage para recuperación
        localStorage.setItem('visubloq_last_design', JSON.stringify({
            designData,
            timestamp: Date.now()
        }));
        
        console.log('🏗️ Redirigiendo a Shopify para construir el diseño');
        
        // Redirigir directamente sin popup molesto
        window.open(productUrl, '_blank');
        
    } catch (error) {
        console.error('❌ Error preparando construcción:', error);
        alert(`❌ Error: ${error.message}`);
    }
}

// ℹ️ Mostrar información sobre el proceso
function showVisuBloqInfo() {
    alert(`�️ ¿Cómo funciona VisuBloq?\n\n1️⃣ Creas tu diseño LEGO personalizado aquí\n2️⃣ Haces clic en "CONSTRUIR"\n3️⃣ Te redirige a nuestra tienda online\n4️⃣ Añades al carrito (puedes añadir varios diseños)\n5️⃣ Completas la compra\n6️⃣ Te enviamos las piezas exactas a casa\n\n📋 Para obtener las instrucciones PDF:\n• Usa el botón "Generate Instructions PDF"\n• El PDF se descarga automáticamente\n\n💰 Cada diseño: 19,99€\n� Envío incluido\n🧱 Piezas LEGO originales\n\n¡Construye tu obra maestra!`);
}

// 🔧 Añadir botones para el flujo de VisuBloq
function addShopifyButton() {
    // Buscar el botón de descargar instrucciones
    const downloadButton = document.getElementById('download-instructions-button');
    
    if (downloadButton && !document.getElementById('visubloq-buy-button')) {
        // 1. BOTÓN PRINCIPAL: Construir diseño LEGO
        const buyButton = document.createElement('button');
        buyButton.id = 'visubloq-buy-button';
        buyButton.className = downloadButton.className;
        buyButton.textContent = '🏗️ CONSTRUIR';
        buyButton.style.marginLeft = '10px';
        buyButton.style.backgroundColor = '#ff6b35';
        buyButton.style.color = 'white';
        buyButton.style.fontWeight = 'bold';
        buyButton.style.fontSize = '1.1em';
        buyButton.style.padding = '12px 20px';
        buyButton.onclick = buyCurrentDesign;
        
        // 2. BOTÓN INFORMACIÓN: Explicar el proceso
        const infoButton = document.createElement('button');
        infoButton.id = 'visubloq-info-button';
        infoButton.className = downloadButton.className;
        infoButton.textContent = 'ℹ️ ¿Cómo funciona?';
        infoButton.style.marginLeft = '10px';
        infoButton.style.backgroundColor = '#17a2b8';
        infoButton.style.color = 'white';
        infoButton.style.fontSize = '0.9em';
        infoButton.style.padding = '8px 15px';
        infoButton.onclick = showVisuBloqInfo;
        
        // Añadir botones
        downloadButton.parentNode.insertBefore(buyButton, downloadButton.nextSibling);
        buyButton.parentNode.insertBefore(infoButton, buyButton.nextSibling);
        
        console.log('✅ Botones de VisuBloq añadidos - Flujo correcto implementado');
    }
}

// Ejecutar cuando la página esté lista
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', addShopifyButton);
} else {
    addShopifyButton();
}

console.log('🏗️ VisuBloq integrado correctamente. Flujo: Diseñar → CONSTRUIR → Múltiples productos en carrito');
