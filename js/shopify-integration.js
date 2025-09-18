/**
 * VisuBloq - Integración con Shopify
 * Sistema de tracking de diseños para asociar con compras
 */

class VisuBloqShopifyIntegration {
    constructor() {
        this.trackingCode = null;
        this.customerEmail = null;
        this.initializeTracking();
    }

    initializeTracking() {
        // Generar código único de seguimiento
        this.trackingCode = this.generateTrackingCode();
        
        // Intentar obtener email del cliente de Shopify
        this.getCustomerInfo();
        
        // Mostrar código de seguimiento al usuario
        this.displayTrackingCode();
        
        // Interceptar cuando se genera un diseño
        this.setupDesignCapture();
    }

    generateTrackingCode() {
        const timestamp = Date.now();
        const random = Math.random().toString(36).substr(2, 8);
        return `VB-${timestamp}-${random}`.toUpperCase();
    }

    getCustomerInfo() {
        // Intentar obtener info del cliente desde Shopify
        if (window.Shopify && window.Shopify.shop) {
            this.customerEmail = window.Shopify.customer?.email || null;
        }
        
        // También intentar desde localStorage o cookies
        if (!this.customerEmail) {
            this.customerEmail = localStorage.getItem('customer_email') || 
                                sessionStorage.getItem('customer_email') || 
                                this.getCookie('customer_email');
        }
    }

    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    displayTrackingCode() {
        // Crear un elemento visible con el código de seguimiento
        const trackingDisplay = document.createElement('div');
        trackingDisplay.id = 'visubloq-tracking-display';
        trackingDisplay.style.cssText = `
            position: fixed;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            font-weight: bold;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: all 0.3s ease;
        `;
        
        trackingDisplay.innerHTML = `
            <div style="margin-bottom: 3px;">🧱 Tu código de seguimiento:</div>
            <div style="font-size: 14px; letter-spacing: 1px;">${this.trackingCode}</div>
            <div style="font-size: 10px; opacity: 0.8; margin-top: 3px;">Clic para copiar</div>
        `;
        
        // Funcionalidad para copiar al portapapeles
        trackingDisplay.addEventListener('click', () => {
            navigator.clipboard.writeText(this.trackingCode).then(() => {
                this.showNotification('✅ Código copiado al portapapeles', 'success');
            });
        });
        
        document.body.appendChild(trackingDisplay);
        
        // Auto-ocultar después de 10 segundos
        setTimeout(() => {
            trackingDisplay.style.opacity = '0.6';
            trackingDisplay.style.transform = 'scale(0.9)';
        }, 10000);
    }

    setupDesignCapture() {
        // Interceptar la generación de PDF
        const originalDownloadBtn = document.getElementById('download-instructions-button');
        if (originalDownloadBtn) {
            originalDownloadBtn.addEventListener('click', () => {
                setTimeout(() => {
                    this.captureDesignWithTracking();
                }, 1000);
            });
        }
    }

    async captureDesignWithTracking() {
        try {
            const designData = this.extractDesignData();
            
            // Añadir información de tracking
            designData.tracking_code = this.trackingCode;
            designData.customer_email = this.customerEmail;
            designData.shopify_shop = window.Shopify?.shop || null;
            designData.timestamp = new Date().toISOString();
            
            // NUEVO: Capturar también el PDF
            designData.pdf_data = await this.capturePDFData();
            
            // Determinar URL del servidor (local vs producción)
            const serverUrl = this.getServerUrl();
            
            // Enviar al backend
            const response = await fetch(`${serverUrl}/backend/api/save-design-data.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(designData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showTrackingSuccess(result);
            } else {
                console.error('Error guardando diseño:', result.error);
                // Intentar guardar localmente como fallback
                this.saveLocalBackup(designData);
            }
            
        } catch (error) {
            console.error('Error en captura de diseño:', error);
            // Guardar localmente si falla el servidor
            this.saveLocalBackup({ tracking_code: this.trackingCode, timestamp: new Date().toISOString() });
        }
    }

    extractDesignData() {
        // Extraer datos de piezas de la tabla "Pieces Used"
        const piecesData = {};
        const piecesTable = document.querySelector('#studs-used-table-body');
        
        if (piecesTable) {
            const rows = piecesTable.querySelectorAll('tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 2) {
                    const colorName = cells[0].textContent.trim();
                    const count = parseInt(cells[cells.length - 1].textContent) || 0;
                    if (count > 0) {
                        piecesData[colorName] = count;
                    }
                }
            });
        }

        // Extraer configuración
        const config = {
            width: this.getSliderValue('width-slider') || 50,
            height: this.getSliderValue('height-slider') || 50,
            saturation: this.getSliderValue('saturation-slider') || 0,
            brightness: this.getSliderValue('brightness-slider') || 0,
            contrast: this.getSliderValue('contrast-slider') || 0
        };

        return {
            piece_colors: piecesData,
            visubloq_config: config,
            total_pieces: Object.values(piecesData).reduce((sum, count) => sum + count, 0)
        };
    }

    getSliderValue(sliderId) {
        const slider = document.getElementById(sliderId);
        return slider ? parseInt(slider.value) : null;
    }

    showTrackingSuccess(result) {
        this.showNotification(`✅ Diseño guardado con código: ${this.trackingCode}`, 'success');
        
        // Mostrar instrucciones al usuario
        this.showPurchaseInstructions();
    }

    showPurchaseInstructions() {
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        `;
        
        modal.innerHTML = `
            <div style="background: white; border-radius: 15px; padding: 30px; max-width: 500px; margin: 20px;">
                <h2 style="color: #333; margin-bottom: 20px;">🎯 ¡Diseño Listo!</h2>
                <p style="margin-bottom: 15px;"><strong>Tu código de seguimiento:</strong></p>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 20px; border: 2px dashed #667eea;">
                    ${this.trackingCode}
                </div>
                <p style="margin-bottom: 15px;"><strong>📋 Para completar tu pedido:</strong></p>
                <ol style="margin-bottom: 20px; padding-left: 20px;">
                    <li>Ve a la tienda de Shopify</li>
                    <li>Añade el producto al carrito</li>
                    <li><strong>En "Notas del pedido" o "Comentarios especiales":</strong><br>
                        Escribe tu código: <code>${this.trackingCode}</code></li>
                    <li>Completa tu compra</li>
                </ol>
                <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                    ⚠️ <strong>Importante:</strong> Sin este código no podremos asociar tu diseño con tu pedido.
                </p>
                <div style="text-align: center;">
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" style="background: #667eea; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; margin-right: 10px;">
                        Entendido
                    </button>
                    <button onclick="navigator.clipboard.writeText('${this.trackingCode}'); this.textContent='¡Copiado!'" style="background: #28a745; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer;">
                        Copiar Código
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            z-index: 10001;
            background: ${type === 'success' ? '#28a745' : '#007bff'};
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Método para que Shopify lo use cuando se complete una compra
    static notifyPurchase(orderData) {
        // Este método será llamado desde Shopify cuando se complete una compra
        if (orderData.note && orderData.note.includes('VB-')) {
            const trackingMatch = orderData.note.match(/VB-\d+-[A-Z0-9]+/);
            if (trackingMatch) {
                const trackingCode = trackingMatch[0];
                
                // Enviar al webhook
                fetch('backend/api/shopify-webhook.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        ...orderData,
                        visubloq_tracking_code: trackingCode
                    })
                });
            }
        }
    }

    // NUEVAS FUNCIONES PARA CAPTURAR PDF
    async capturePDFData() {
        try {
            // Generar PDF igual que el botón original
            const pdfBlob = await this.generatePDFBlob();
            
            if (pdfBlob) {
                // Convertir blob a base64 para envío
                return await this.blobToBase64(pdfBlob);
            }
            return null;
        } catch (error) {
            console.error('Error capturando PDF:', error);
            return null;
        }
    }

    async generatePDFBlob() {
        // Intentar usar la misma función que usa el botón de descarga
        if (typeof window.generateInstructionsPDF === 'function') {
            return await window.generateInstructionsPDF();
        }
        
        // Si no existe, intentar encontrar el elemento canvas y convertirlo
        const canvas = document.querySelector('#main-canvas') || document.querySelector('canvas');
        if (canvas) {
            return new Promise((resolve) => {
                canvas.toBlob(resolve, 'image/png');
            });
        }
        
        return null;
    }

    blobToBase64(blob) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(blob);
        });
    }

    getServerUrl() {
        // Si estamos en GitHub Pages, usar ngrok
        if (window.location.hostname.includes('github.io')) {
            // Aquí pondremos la URL de ngrok cuando esté funcionando
            return 'https://TU-URL-NGROK.ngrok-free.app';
        }
        
        // Si estamos en local, usar localhost
        return window.location.origin;
    }

    saveLocalBackup(data) {
        try {
            const backups = JSON.parse(localStorage.getItem('visubloq_backups') || '[]');
            backups.push({
                ...data,
                saved_at: new Date().toISOString(),
                status: 'backup'
            });
            localStorage.setItem('visubloq_backups', JSON.stringify(backups.slice(-10))); // Solo últimos 10
            console.log('Datos guardados localmente como backup');
        } catch (error) {
            console.error('Error guardando backup local:', error);
        }
    }
}

// Inicializar cuando la página esté lista
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window !== 'undefined') {
        window.visubloqShopify = new VisuBloqShopifyIntegration();
        console.log('🛒 Integración Shopify VisuBloq inicializada');
    }
});

// Exportar para uso en Shopify
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VisuBloqShopifyIntegration;
}
