// ============================================================================
// 🧪 FUNCIONES DE TEST PARA INTEGRACIÓN SHOPIFY - VisuBloq
// ============================================================================
// 
// Instrucciones de uso:
// 1. Carga index.html o test-shopify.html en tu navegador
// 2. Abre la consola del navegador (F12 → Console)
// 3. Ejecuta cualquiera de estas funciones:
//    - quickTest()           → Test rápido básico
//    - fullTest()            → Test completo con PDF
//    - testOrderValidation() → Solo validación de pedidos
//    - testDownload(orderId) → Descargar PDF existente
//
// ============================================================================

// 🚀 TEST RÁPIDO - Ejecuta esto para una prueba básica
async function quickTest() {
    console.log('🚀 === QUICK TEST INICIADO ===');
    console.log('');
    
    try {
        // 1. Verificar que las funciones existen
        console.log('🔍 Verificando funciones de Shopify...');
        
        if (typeof savePDFToShopifyOrder !== 'function') {
            throw new Error('❌ Función savePDFToShopifyOrder no encontrada');
        }
        if (typeof processShopifyOrder !== 'function') {
            throw new Error('❌ Función processShopifyOrder no encontrada');
        }
        if (typeof isValidShopifyOrder !== 'function') {
            throw new Error('❌ Función isValidShopifyOrder no encontrada');
        }
        if (typeof verifyShopifyConfig !== 'function') {
            throw new Error('❌ Función verifyShopifyConfig no encontrada');
        }
        
        console.log('✅ Todas las funciones de Shopify están disponibles');
        console.log('');
        
        // 2. Test de validación
        console.log('🔍 Probando validación de pedidos...');
        testOrderValidation();
        
        // 3. Verificar configuración de Shopify
        console.log('� Verificando configuración de Shopify...');
        const configOK = await verifyShopifyConfig();
        
        if (configOK) {
            console.log('✅ QUICK TEST COMPLETADO EXITOSAMENTE');
            console.log('');
            console.log('💡 Todo listo. Para test completo ejecuta: fullTest()');
        } else {
            console.log('⚠️ QUICK TEST COMPLETADO CON ADVERTENCIAS');
            console.log('');
            console.log('� Necesitas configurar Shopify antes del test completo');
        }
        
    } catch (error) {
        console.error('❌ Error en quick test:', error);
    }
}

// 🚀 TEST COMPLETO - Incluye llamadas reales a Shopify API
async function fullTest() {
    console.log('🚀 === FULL TEST CON SHOPIFY API ===');
    console.log('⚠️  ATENCIÓN: Este test hace llamadas REALES a la API de Shopify');
    console.log('');
    
    try {
        // 1. Simular mosaico completo
        console.log('🎨 Generando mosaico de prueba...');
        simulateFullMosaic();
        
        // 2. Crear pedido de prueba
        const testOrder = {
            id: Date.now(),
            order_number: "VB-TEST-" + Math.floor(Math.random() * 999),
            email: "admin@visubloq.com",
            total_price: "29.99",
            financial_status: "paid",
            customer: {
                first_name: "Admin",
                last_name: "VisuBloq",
                email: "admin@visubloq.com"
            },
            line_items: [{
                title: "Mosaico LEGO Personalizado",
                quantity: 1,
                price: "29.99"
            }],
            webhook_verified: true,
            created_at: new Date().toISOString(),
            tags: ""
        };
        
        console.log('📦 Pedido de prueba:', testOrder);
        console.log('');
        
        // 3. Ejecutar proceso completo
        console.log('⚡ Ejecutando proceso completo...');
        await processShopifyOrder(testOrder);
        
        console.log('✅ FULL TEST COMPLETADO');
        
    } catch (error) {
        console.error('❌ Error en full test:', error);
    }
}

// 🔍 TEST DE VALIDACIÓN - Solo prueba las validaciones
function testOrderValidation() {
    console.log('🔍 === TEST DE VALIDACIÓN DE PEDIDOS ===');
    console.log('');
    
    // Test 1: Pedido válido
    console.log('Test 1: Pedido VÁLIDO');
    const validOrder = {
        order_number: "VB12345",
        email: "customer@example.com",
        total_price: "25.00",
        financial_status: "paid",
        customer: {
            first_name: "Juan",
            last_name: "Pérez",
            email: "customer@example.com"
        },
        webhook_verified: true
    };
    
    const result1 = isValidShopifyOrder(validOrder);
    console.log('✅ Resultado:', result1 ? 'VÁLIDO' : 'INVÁLIDO');
    console.log('');
    
    // Test 2: Pedido sin pago
    console.log('Test 2: Pedido SIN PAGO');
    const unpaidOrder = {
        order_number: "VB00000",
        email: "test@test.com",
        total_price: "0.00",
        financial_status: "pending",
        customer: { first_name: "Test" },
        webhook_verified: false
    };
    
    const result2 = isValidShopifyOrder(unpaidOrder);
    console.log('❌ Resultado:', result2 ? 'VÁLIDO' : 'INVÁLIDO');
    console.log('');
    
    // Test 3: Pedido incompleto
    console.log('Test 3: Pedido INCOMPLETO');
    const incompleteOrder = {
        order_number: "VB",
        total_price: "15.00"
        // Faltan datos del cliente
    };
    
    const result3 = isValidShopifyOrder(incompleteOrder);
    console.log('❌ Resultado:', result3 ? 'VÁLIDO' : 'INVÁLIDO');
    console.log('');
    
    console.log('✅ Tests de validación completados');
}

// 📥 TEST DE DESCARGA - Descarga un PDF existente
async function testDownload(orderId) {
    console.log('📥 === TEST DE DESCARGA DE PDF ===');
    
    if (!orderId) {
        orderId = prompt('Ingresa el ID del pedido con PDF:', '');
        if (!orderId) {
            console.log('❌ ID de pedido requerido');
            return;
        }
    }
    
    console.log('🔍 Descargando PDF del pedido:', orderId);
    
    try {
        if (typeof testDownloadPDF === 'function') {
            await testDownloadPDF(orderId);
        } else {
            console.error('❌ Función testDownloadPDF no disponible');
        }
    } catch (error) {
        console.error('❌ Error en descarga:', error);
    }
}

// 🎨 SIMULAR MOSAICO BÁSICO
function simulateBasicMosaic() {
    window.currentMosaic = {
        width: 16,
        height: 16,
        brickData: new Array(256).fill(0).map(() => Math.floor(Math.random() * 5)),
        pieceCount: 256,
        colors: ['White', 'Black', 'Red', 'Blue', 'Yellow'],
        generatedAt: new Date().toISOString()
    };
    
    console.log('✅ Mosaico básico simulado (16x16, 256 piezas)');
}

// 🎨 SIMULAR MOSAICO COMPLETO
function simulateFullMosaic() {
    window.currentMosaic = {
        width: 48,
        height: 48,
        brickData: new Array(2304).fill(0).map(() => Math.floor(Math.random() * 15)),
        pieceCount: 2304,
        colors: [
            'White', 'Black', 'Red', 'Blue', 'Yellow', 'Green',
            'Orange', 'Purple', 'Gray', 'Brown', 'Pink', 'Lime',
            'Cyan', 'Magenta', 'Dark Gray'
        ],
        image: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
        generatedAt: new Date().toISOString(),
        settings: {
            quality: 'high',
            size: 'large'
        }
    };
    
    console.log('✅ Mosaico completo simulado (48x48, 2304 piezas)');
}

// 📋 MOSTRAR AYUDA
function showHelp() {
    console.log('🆘 === AYUDA DE TESTING ===');
    console.log('');
    console.log('Funciones disponibles:');
    console.log('📌 quickTest()           → Test rápido sin API calls');
    console.log('📌 fullTest()            → Test completo con Shopify API');
    console.log('📌 testOrderValidation() → Solo pruebas de validación');
    console.log('📌 testDownload(id)      → Descargar PDF de pedido');
    console.log('📌 showHelp()            → Mostrar esta ayuda');
    console.log('');
    console.log('🔧 Para comenzar, ejecuta: quickTest()');
}

// 🎯 FUNCIÓN DE INICIO AUTOMÁTICO
function autoStart() {
    console.log('🎯 === TESTING SHOPIFY INTEGRATION LOADED ===');
    console.log('');
    console.log('💡 Ejecuta quickTest() para empezar');
    console.log('💡 O showHelp() para ver todas las opciones');
    console.log('');
}

// Auto-ejecutar al cargar
if (typeof window !== 'undefined') {
    window.addEventListener('load', autoStart);
} else {
    autoStart();
}

// Exportar funciones para uso en consola
if (typeof window !== 'undefined') {
    window.quickTest = quickTest;
    window.fullTest = fullTest;
    window.testOrderValidation = testOrderValidation;
    window.testDownload = testDownload;
    window.showHelp = showHelp;
    window.simulateBasicMosaic = simulateBasicMosaic;
    window.simulateFullMosaic = simulateFullMosaic;
}
