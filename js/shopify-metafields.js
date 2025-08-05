// 🎯 INTEGRACIÓN VISUBLOQ CON SHOPIFY
// =================================
// FLUJO CORRECTO: Cliente crea diseño → Compra → Datos se asocian automáticamente al pedido

// Configuración
const SHOPIFY_CONFIG = {
    shop: 'VisuBloq.myshopify.com',
    accessToken: window.SHOPIFY_TOKEN || 'DEMO_MODE', // Token se carga desde shopify-config.js
    apiVersion: '2024-01'
};

// 💳 Función principal: Comprar diseño actual
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
        
        // Crear resumen del diseño para enviar a Shopify
        const designData = {
            total_pieces: totalPieces,
            piece_types: pieceTypes,
            resolution: `${targetResolution[0]}x${targetResolution[1]}`,
            pieces_detail: studMap,
            generated_at: new Date().toISOString()
        };
        
        // Codificar datos para URL
        const encodedData = encodeURIComponent(JSON.stringify(designData));
        
        // Construir URL del producto con datos del diseño
        const productUrl = `https://visubloq.com/products/visubloq-personalizado?design_data=${encodedData}`;
        
        // Mostrar información antes de redirigir
        const confirmMessage = `🎯 Tu diseño LEGO está listo:\n\n📊 ${totalPieces} piezas totales\n🎨 ${pieceTypes} colores diferentes\n📐 Resolución: ${targetResolution[0]}x${targetResolution[1]}\n\n🛒 Al comprar recibirás:\n• Todas las piezas LEGO necesarias\n• Instrucciones PDF detalladas\n• Envío a tu casa\n\nPrecio: 19,99€\n\n¿Proceder a la compra?`;
        
        if (confirm(confirmMessage)) {
            // Guardar diseño en localStorage para recuperación
            localStorage.setItem('visubloq_last_design', JSON.stringify({
                designData,
                timestamp: Date.now()
            }));
            
            console.log('🛒 Redirigiendo a Shopify con datos del diseño');
            
            // Abrir en nueva ventana para no perder el diseño actual
            window.open(productUrl, '_blank');
        }
        
    } catch (error) {
        console.error('❌ Error preparando compra:', error);
        alert(`❌ Error: ${error.message}`);
    }
}

// ℹ️ Mostrar información sobre el proceso
function showVisuBloqInfo() {
    alert(`🎯 ¿Cómo funciona VisuBloq?\n\n1️⃣ Creas tu diseño LEGO personalizado aquí\n2️⃣ Haces clic en "Comprar piezas LEGO"\n3️⃣ Te redirige a nuestra tienda online\n4️⃣ Completas la compra (19,99€)\n5️⃣ Procesamos tu pedido automáticamente:\n   • Lista exacta de piezas por color\n   • Instrucciones PDF para construir\n   • Tu diseño queda asociado al pedido\n\n📦 Te enviamos las piezas exactas a casa\n🏗️ Construyes tu obra maestra LEGO\n📋 El admin ve toda la información en Shopify\n\n¡Es así de fácil!`);
}

// 🔧 Añadir botones para el flujo de VisuBloq
function addShopifyButton() {
    // Buscar el botón de descargar instrucciones
    const downloadButton = document.getElementById('download-instructions-button');
    
    if (downloadButton && !document.getElementById('visubloq-buy-button')) {
        // 1. BOTÓN PRINCIPAL: Comprar piezas LEGO
        const buyButton = document.createElement('button');
        buyButton.id = 'visubloq-buy-button';
        buyButton.className = downloadButton.className;
        buyButton.textContent = '🛒 Comprar piezas LEGO de este diseño (19,99€)';
        buyButton.style.marginLeft = '10px';
        buyButton.style.backgroundColor = '#28a745';
        buyButton.style.color = 'white';
        buyButton.style.fontWeight = 'bold';
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

console.log('🛒 VisuBloq integrado correctamente. Flujo: Diseñar → Comprar → Datos automáticos en pedido');
