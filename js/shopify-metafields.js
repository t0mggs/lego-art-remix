// 🎯 AÑADIR ESTE CÓDIGO AL FINAL DE TU index.js

// =================================
// INTEGRACIÓN CON SHOPIFY METAFIELDS
// =================================

// Configuración (CAMBIAR POR TUS DATOS REALES)
const SHOPIFY_CONFIG = {
    shop: 'VisuBloq.myshopify.com',
    accessToken: window.SHOPIFY_TOKEN || prompt('🔑 Introduce tu token de Shopify:'), // Token seguro
    apiVersion: '2024-01'
};

// 🔍 Buscar pedido por número
async function findShopifyOrderByNumber(orderNumber) {
    try {
        console.log('🔍 Buscando pedido:', orderNumber);
        
        // En GitHub Pages, simular pedido encontrado para demo
        if (window.location.hostname.includes('github.io')) {
            console.log('🎭 Modo demo: Simulando pedido encontrado');
            return {
                id: 'demo_12345',
                name: orderNumber,
                customer: {
                    first_name: 'Cliente',
                    last_name: 'Demo'
                }
            };
        }
        
        // Solo hacer petición real en localhost
        const response = await fetch(`https://${SHOPIFY_CONFIG.shop}/admin/api/${SHOPIFY_CONFIG.apiVersion}/orders.json?name=${encodeURIComponent(orderNumber)}&status=any&limit=1`, {
            headers: {
                'X-Shopify-Access-Token': SHOPIFY_CONFIG.accessToken,
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Error ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📋 Pedidos encontrados:', data.orders.length);
        
        return data.orders.length > 0 ? data.orders[0] : null;
        
    } catch (error) {
        console.error('❌ Error buscando pedido:', error);
        return null;
    }
}

// 💾 Guardar datos en Shopify
async function saveToShopifyMetafields(orderId, studMap, pdfBase64) {
    try {
        console.log('💾 Guardando metafields para orden:', orderId);
        
        // En GitHub Pages, simular guardado exitoso
        if (window.location.hostname.includes('github.io')) {
            console.log('🎭 Modo demo: Simulando guardado de metafields');
            // Simular delay para hacer realista
            await new Promise(resolve => setTimeout(resolve, 2000));
            return true;
        }
        
        // Solo hacer peticiones reales en localhost
        // 1. Guardar lista de piezas
        const piecesData = {
            metafield: {
                namespace: 'visubloq',
                key: 'pieces_list',
                value: JSON.stringify(studMap),
                type: 'json'
            }
        };
        
        const piecesResponse = await fetch(`https://${SHOPIFY_CONFIG.shop}/admin/api/${SHOPIFY_CONFIG.apiVersion}/orders/${orderId}/metafields.json`, {
            method: 'POST',
            headers: {
                'X-Shopify-Access-Token': SHOPIFY_CONFIG.accessToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(piecesData)
        });
        
        // 2. Guardar PDF
        const pdfData = {
            metafield: {
                namespace: 'visubloq',
                key: 'instructions_pdf',
                value: pdfBase64,
                type: 'single_line_text_field'
            }
        };
        
        const pdfResponse = await fetch(`https://${SHOPIFY_CONFIG.shop}/admin/api/${SHOPIFY_CONFIG.apiVersion}/orders/${orderId}/metafields.json`, {
            method: 'POST',
            headers: {
                'X-Shopify-Access-Token': SHOPIFY_CONFIG.accessToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(pdfData)
        });
        
        // 3. Guardar resumen
        const totalPieces = Object.values(studMap).reduce((sum, count) => sum + count, 0);
        const summaryData = {
            metafield: {
                namespace: 'visubloq',
                key: 'order_summary',
                value: JSON.stringify({
                    total_pieces: totalPieces,
                    piece_types: Object.keys(studMap).length,
                    generated_at: new Date().toISOString(),
                    resolution: `${targetResolution[0]}x${targetResolution[1]}`
                }),
                type: 'json'
            }
        };
        
        const summaryResponse = await fetch(`https://${SHOPIFY_CONFIG.shop}/admin/api/${SHOPIFY_CONFIG.apiVersion}/orders/${orderId}/metafields.json`, {
            method: 'POST',
            headers: {
                'X-Shopify-Access-Token': SHOPIFY_CONFIG.accessToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(summaryData)
        });
        
        // Verificar resultados
        if (piecesResponse.ok && pdfResponse.ok && summaryResponse.ok) {
            console.log('✅ Todos los metafields guardados correctamente');
            return true;
        } else {
            console.error('❌ Error en algunos metafields:', {
                pieces: piecesResponse.status,
                pdf: pdfResponse.status,
                summary: summaryResponse.status
            });
            return false;
        }
        
    } catch (error) {
        console.error('❌ Error guardando metafields:', error);
        return false;
    }
}

// 💳 Función principal: Comprar diseño actual
async function buyCurrentDesign() {
    try {
        // Verificar que hay un diseño cargado
        if (!step4Canvas || step4Canvas.width === 0) {
            alert('❌ No hay ningún diseño cargado. Carga una imagen y procésala primero.');
            return;
        }
        
        // Generar datos del diseño
        const step4PixelArray = getPixelArrayFromCanvas(step4Canvas);
        const resultImage = isBleedthroughEnabled()
            ? revertDarkenedImage(step4PixelArray, getDarkenedStudsToStuds(ALL_BRICKLINK_SOLID_COLORS.map((color) => color.hex)))
            : step4PixelArray;
        
        const studMap = getUsedPixelsStudMap(resultImage);
        const totalPieces = Object.values(studMap).reduce((sum, count) => sum + count, 0);
        const pieceTypes = Object.keys(studMap).length;
        
        // Crear resumen del diseño
        const designData = {
            total_pieces: totalPieces,
            piece_types: pieceTypes,
            resolution: `${targetResolution[0]}x${targetResolution[1]}`,
            pieces_detail: studMap,
            generated_at: new Date().toISOString()
        };
        
        // Codificar datos para URL
        const encodedData = encodeURIComponent(JSON.stringify(designData));
        
        // Construir URL del producto con datos
        const productUrl = `https://visubloq.com/products/visubloq-personalizado?design_data=${encodedData}`;
        
        // Mostrar información antes de redirigir
        const confirmMessage = `🎯 Tu diseño LEGO está listo:\n\n• ${totalPieces} piezas totales\n• ${pieceTypes} colores diferentes\n• Resolución: ${targetResolution[0]}x${targetResolution[1]}\n\n¿Quieres proceder a comprarlo por 19,99€?`;
        
        if (confirm(confirmMessage)) {
            // Abrir en nueva ventana para no perder el diseño actual
            window.open(productUrl, '_blank');
            
            // Opcional: Guardar diseño en localStorage para recuperación
            localStorage.setItem('visubloq_last_design', JSON.stringify({
                designData,
                timestamp: Date.now()
            }));
            
            console.log('🛒 Redirigiendo a producto con datos del diseño');
        }
        
    } catch (error) {
        console.error('❌ Error preparando compra:', error);
        alert(`❌ Error: ${error.message}`);
    }
}

// 🎯 Función principal para el usuario
async function saveCurrentDesignToShopify() {
    try {
        // Verificar que hay un diseño cargado
        if (!step4Canvas || step4Canvas.width === 0) {
            alert('❌ No hay ningún diseño cargado. Carga una imagen y procésala primero.');
            return;
        }
        
        // Pedir número de pedido
        const orderNumber = prompt('🛒 Introduce el número de pedido de Shopify:\n\nEjemplo: #1001, VB-001, etc.');
        
        if (!orderNumber) {
            alert('⚠️ Número de pedido requerido');
            return;
        }
        
        // Mostrar mensaje de carga
        const originalText = document.getElementById('download-instructions-button').textContent;
        document.getElementById('download-instructions-button').textContent = '🔄 Buscando pedido...';
        document.getElementById('download-instructions-button').disabled = true;
        
        // Buscar pedido
        const order = await findShopifyOrderByNumber(orderNumber);
        
        if (!order) {
            alert(`❌ No se encontró el pedido "${orderNumber}" en Shopify.\n\nVerifica:\n• El número está correcto\n• El pedido existe en tu tienda\n• Has configurado bien el token`);
            return;
        }
        
        console.log('✅ Pedido encontrado:', order.name, '- Cliente:', order.customer?.first_name || 'Sin nombre');
        document.getElementById('download-instructions-button').textContent = '📄 Generando PDF...';
        
        // Generar datos
        const step4PixelArray = getPixelArrayFromCanvas(step4Canvas);
        const resultImage = isBleedthroughEnabled()
            ? revertDarkenedImage(step4PixelArray, getDarkenedStudsToStuds(ALL_BRICKLINK_SOLID_COLORS.map((color) => color.hex)))
            : step4PixelArray;
        
        const studMap = getUsedPixelsStudMap(resultImage);
        
        // Generar PDF simple
        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        });
        
        // Título
        pdf.setFontSize(18);
        pdf.text('VisuBloq - Instrucciones LEGO', 20, 20);
        
        // Info del pedido
        pdf.setFontSize(12);
        pdf.text(`Pedido: ${order.name}`, 20, 35);
        pdf.text(`Cliente: ${order.customer?.first_name || ''} ${order.customer?.last_name || ''}`, 20, 45);
        pdf.text(`Generado: ${new Date().toLocaleString()}`, 20, 55);
        
        // Estadísticas
        const totalPieces = Object.values(studMap).reduce((sum, count) => sum + count, 0);
        pdf.text(`Total piezas: ${totalPieces}`, 20, 70);
        pdf.text(`Tipos de colores: ${Object.keys(studMap).length}`, 20, 80);
        
        // Lista de piezas
        pdf.setFontSize(14);
        pdf.text('Lista de piezas por color:', 20, 100);
        
        let yPos = 115;
        pdf.setFontSize(10);
        
        Object.entries(studMap).forEach(([hexColor, count]) => {
            const brickLinkColor = ALL_BRICKLINK_SOLID_COLORS.find(c => c.hex === hexColor);
            const colorName = brickLinkColor ? brickLinkColor.name : hexColor;
            
            pdf.text(`• ${colorName}: ${count} piezas`, 25, yPos);
            yPos += 6;
            
            if (yPos > 270) {
                pdf.addPage();
                yPos = 30;
            }
        });
        
        // Convertir a base64
        const pdfBase64 = pdf.output('datauristring');
        
        document.getElementById('download-instructions-button').textContent = '💾 Guardando en Shopify...';
        
        // Guardar en Shopify
        const success = await saveToShopifyMetafields(order.id, studMap, pdfBase64);
        
        if (success) {
            alert(`✅ ¡Perfecto!\n\nDatos guardados en el pedido ${order.name}:\n• ${totalPieces} piezas totales\n• ${Object.keys(studMap).length} colores diferentes\n• PDF de instrucciones\n\nYa puedes verlo en Shopify Admin.`);
            
            // También descargar el PDF
            const link = document.createElement('a');
            link.href = pdfBase64;
            link.download = `visubloq_${order.name.replace('#', '')}_${Date.now()}.pdf`;
            link.click();
        } else {
            alert('❌ Error guardando en Shopify. Revisa la consola para más detalles.');
        }
        
    } catch (error) {
        console.error('❌ Error completo:', error);
        alert(`❌ Error: ${error.message}`);
    } finally {
        // Restaurar botón
        document.getElementById('download-instructions-button').textContent = originalText;
        document.getElementById('download-instructions-button').disabled = false;
    }
}

// 🧪 Función de prueba de conexión
async function testShopifyConnection() {
    try {
        console.log('🧪 Probando conexión a Shopify...');
        
        // Para GitHub Pages, simulamos la conexión exitosa
        if (window.location.hostname.includes('github.io')) {
            console.log('✅ Modo demo: Simulando conexión exitosa');
            alert(`✅ ¡Conexión simulada exitosa!\n\nModo Demo - GitHub Pages\nTienda: VisuBloq.myshopify.com\n\nEn producción usarías un servidor backend.`);
            return true;
        }
        
        // Solo hacer petición real si estamos en localhost
        const response = await fetch(`https://${SHOPIFY_CONFIG.shop}/admin/api/${SHOPIFY_CONFIG.apiVersion}/shop.json`, {
            headers: {
                'X-Shopify-Access-Token': SHOPIFY_CONFIG.accessToken,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            console.log('✅ Conexión exitosa a:', data.shop.name);
            alert(`✅ Conexión exitosa a Shopify!\n\nTienda: ${data.shop.name}\nDominio: ${data.shop.domain}`);
            return true;
        } else {
            console.error('❌ Error de conexión:', response.status, response.statusText);
            alert(`❌ Error de conexión: ${response.status}\n\nVerifica tu token de acceso.`);
            return false;
        }
    } catch (error) {
        console.error('❌ Error de red:', error);
        
        // Si es error CORS en GitHub Pages, explicar
        if (window.location.hostname.includes('github.io')) {
            alert(`ℹ️ Error esperado en GitHub Pages\n\nMotivo: CORS (seguridad del navegador)\nSolución: En producción usarías un servidor backend\n\nPara pruebas, usa la función "Admin: Asociar a pedido"`);
        } else {
            alert(`❌ Error de conexión: ${error.message}`);
        }
        return false;
    }
}

// 🔧 Añadir botones para el flujo de VisuBloq
function addShopifyButton() {
    // Buscar el botón de descargar instrucciones
    const downloadButton = document.getElementById('download-instructions-button');
    
    if (downloadButton && !document.getElementById('visubloq-buy-button')) {
        // 1. BOTÓN PRINCIPAL: Comprar diseño
        const buyButton = document.createElement('button');
        buyButton.id = 'visubloq-buy-button';
        buyButton.className = downloadButton.className;
        buyButton.textContent = '� Comprar este diseño LEGO (19,99€)';
        buyButton.style.marginLeft = '10px';
        buyButton.style.backgroundColor = '#28a745';
        buyButton.style.color = 'white';
        buyButton.style.fontWeight = 'bold';
        buyButton.onclick = buyCurrentDesign;
        
        // 2. BOTÓN ADMIN: Asociar a pedido (solo para testing)
        const adminButton = document.createElement('button');
        adminButton.id = 'admin-associate-button';
        adminButton.className = downloadButton.className;
        adminButton.textContent = '🔧 Admin: Asociar a pedido';
        adminButton.style.marginLeft = '10px';
        adminButton.style.backgroundColor = '#17a2b8';
        adminButton.style.color = 'white';
        adminButton.style.fontSize = '0.8em';
        adminButton.onclick = saveCurrentDesignToShopify;
        
        // 3. BOTÓN TEST: Conexión Shopify
        const testButton = document.createElement('button');
        testButton.textContent = '🧪 Test Conexión';
        testButton.className = downloadButton.className;
        testButton.style.marginLeft = '10px';
        testButton.style.fontSize = '0.8em';
        testButton.onclick = testShopifyConnection;
        
        // Añadir en orden
        downloadButton.parentNode.insertBefore(buyButton, downloadButton.nextSibling);
        buyButton.parentNode.insertBefore(adminButton, buyButton.nextSibling);
        adminButton.parentNode.insertBefore(testButton, adminButton.nextSibling);
        
        console.log('✅ Botones de VisuBloq añadidos');
    }
}

// Ejecutar cuando la página esté lista
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', addShopifyButton);
} else {
    addShopifyButton();
}

console.log('🛒 Integración VisuBloq cargada. Funciones disponibles:\n- buyCurrentDesign(): Comprar diseño actual\n- testShopifyConnection(): Probar conexión admin\n- saveCurrentDesignToShopify(): Asociar a pedido (admin)');
