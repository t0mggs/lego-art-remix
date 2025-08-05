// 🛒 CONFIGURACIÓN DE SHOPIFY PARA METAFIELDS
// Añadir al inicio de tu index.js

const SHOPIFY_CONFIG = {
    shop: 'VisuBloq.myshopify.com',
    accessToken: 'AQUI_TU_TOKEN_REAL', // Cambiar por el token que copiaste
    apiVersion: '2024-01'
};

// 🔍 FUNCIÓN PARA BUSCAR PEDIDO POR NÚMERO
async function findShopifyOrderByNumber(orderNumber) {
    try {
        const response = await fetch(`https://${SHOPIFY_CONFIG.shop}/admin/api/${SHOPIFY_CONFIG.apiVersion}/orders.json?name=${orderNumber}&status=any`, {
            headers: {
                'X-Shopify-Access-Token': SHOPIFY_CONFIG.accessToken,
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`Error buscando pedido: ${response.status}`);
        }
        
        const data = await response.json();
        return data.orders.length > 0 ? data.orders[0] : null;
    } catch (error) {
        console.error('❌ Error buscando pedido:', error);
        return null;
    }
}

// 💾 FUNCIÓN PARA GUARDAR METAFIELDS EN SHOPIFY
async function saveVisuBloqDataToShopify(orderId, studMap, pdfBase64, additionalInfo = {}) {
    try {
        console.log('💾 Guardando datos en Shopify para pedido:', orderId);
        
        // 1. Guardar lista de piezas
        const piecesResponse = await fetch(`https://${SHOPIFY_CONFIG.shop}/admin/api/${SHOPIFY_CONFIG.apiVersion}/orders/${orderId}/metafields.json`, {
            method: 'POST',
            headers: {
                'X-Shopify-Access-Token': SHOPIFY_CONFIG.accessToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                metafield: {
                    namespace: 'visubloq',
                    key: 'pieces_by_color',
                    value: JSON.stringify(studMap),
                    type: 'json'
                }
            })
        });
        
        // 2. Guardar PDF
        const pdfResponse = await fetch(`https://${SHOPIFY_CONFIG.shop}/admin/api/${SHOPIFY_CONFIG.apiVersion}/orders/${orderId}/metafields.json`, {
            method: 'POST',
            headers: {
                'X-Shopify-Access-Token': SHOPIFY_CONFIG.accessToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                metafield: {
                    namespace: 'visubloq',
                    key: 'instructions_pdf',
                    value: pdfBase64,
                    type: 'single_line_text_field'
                }
            })
        });
        
        // 3. Guardar información adicional
        const infoResponse = await fetch(`https://${SHOPIFY_CONFIG.shop}/admin/api/${SHOPIFY_CONFIG.apiVersion}/orders/${orderId}/metafields.json`, {
            method: 'POST',
            headers: {
                'X-Shopify-Access-Token': SHOPIFY_CONFIG.accessToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                metafield: {
                    namespace: 'visubloq',
                    key: 'order_info',
                    value: JSON.stringify({
                        total_pieces: Object.values(studMap).reduce((sum, count) => sum + count, 0),
                        piece_types: Object.keys(studMap).length,
                        generated_at: new Date().toISOString(),
                        resolution: additionalInfo.resolution || 'unknown',
                        ...additionalInfo
                    }),
                    type: 'json'
                }
            })
        });
        
        // Verificar que todo salió bien
        if (piecesResponse.ok && pdfResponse.ok && infoResponse.ok) {
            console.log('✅ Datos guardados exitosamente en Shopify');
            return true;
        } else {
            console.error('❌ Error guardando algunos metafields');
            return false;
        }
        
    } catch (error) {
        console.error('❌ Error guardando en Shopify:', error);
        return false;
    }
}

// 🎯 FUNCIÓN PRINCIPAL PARA ASOCIAR A PEDIDO
async function associateWithShopifyOrder(orderNumber, studMap, pdfBase64, additionalInfo = {}) {
    try {
        console.log('🔍 Buscando pedido:', orderNumber);
        
        // Buscar el pedido
        const order = await findShopifyOrderByNumber(orderNumber);
        
        if (!order) {
            console.error('❌ No se encontró el pedido:', orderNumber);
            alert(`No se encontró el pedido ${orderNumber} en Shopify. Verifica el número de pedido.`);
            return false;
        }
        
        console.log('✅ Pedido encontrado:', order.name, 'ID:', order.id);
        
        // Guardar los datos
        const success = await saveVisuBloqDataToShopify(order.id, studMap, pdfBase64, additionalInfo);
        
        if (success) {
            alert(`✅ PDF e instrucciones guardados exitosamente en el pedido ${order.name}`);
            return true;
        } else {
            alert(`❌ Error guardando datos en el pedido ${order.name}`);
            return false;
        }
        
    } catch (error) {
        console.error('❌ Error en el proceso:', error);
        alert(`Error: ${error.message}`);
        return false;
    }
}

// 🧪 FUNCIÓN DE PRUEBA (para testing)
async function testShopifyConnection() {
    try {
        const response = await fetch(`https://${SHOPIFY_CONFIG.shop}/admin/api/${SHOPIFY_CONFIG.apiVersion}/shop.json`, {
            headers: {
                'X-Shopify-Access-Token': SHOPIFY_CONFIG.accessToken,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            console.log('✅ Conexión a Shopify OK:', data.shop.name);
            return true;
        } else {
            console.error('❌ Error de conexión:', response.status);
            return false;
        }
    } catch (error) {
        console.error('❌ Error probando conexión:', error);
        return false;
    }
}
