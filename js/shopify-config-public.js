// 🔐 CONFIGURACIÓN PÚBLICA - SEGURA PARA GITHUB
// Este archivo SÍ se puede subir a GitHub

// Para GitHub Pages, usar variables de entorno o configuración externa
if (typeof window !== 'undefined') {
    // En producción, el token se cargaría de forma segura
    // Por ahora, modo demo
    console.log('🔧 Configuración de Shopify cargada en modo seguro');
    
    // En un entorno real, esto vendría de variables de entorno
    // o se cargaría dinámicamente desde una API segura
    window.SHOPIFY_TOKEN = null; // Se configura externamente
}
