/**
 * VisuBloq Ultra Simple - 100% Frontend, Sin Backend
 * PDF generado en navegador + Blob URL + Auto-copy
 */

class VisuBloqUltraSimple {
    constructor() {
        this.shopifyProductUrl = 'https://visubloq.com/products/visubloq-personalizado';
        this.currentPdfBlob = null;
        this.currentPdfUrl = null;
        this.init();
    }

    init() {
        this.setupPDFInterception();
        this.injectModalCSS();
    }

    setupPDFInterception() {
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.interceptPDFButton());
        } else {
            this.interceptPDFButton();
        }
    }

    interceptPDFButton() {
        // Buscar el botón de generar PDF
        const pdfButton = document.getElementById('download-instructions-button');
        if (!pdfButton) {
            // Reintentar en 1 segundo si no se encuentra
            setTimeout(() => this.interceptPDFButton(), 1000);
            return;
        }

        console.log('🧱 VisuBloq Ultra Simple: Botón PDF encontrado');

        // Interceptar el click
        pdfButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.handlePDFGeneration();
        });
    }

    async handlePDFGeneration() {
        console.log('🧱 Iniciando generación PDF ultra simple...');

        try {
            // Mostrar overlay de carga
            this.showLoadingOverlay();

            // Generar PDF usando la funcionalidad existente de VisuBloq
            const pdfBlob = await this.generatePDFBlob();
            
            // Crear URL temporal del blob
            const pdfUrl = URL.createObjectURL(pdfBlob);
            
            // Guardar referencias
            this.currentPdfBlob = pdfBlob;
            this.currentPdfUrl = pdfUrl;

            // Ocultar overlay
            this.hideLoadingOverlay();

            // Mostrar modal con el link
            this.showPDFModal(pdfUrl);

        } catch (error) {
            console.error('❌ Error generando PDF:', error);
            this.hideLoadingOverlay();
            alert('Error generando el PDF. Por favor intenta de nuevo.');
        }
    }

    async generatePDFBlob() {
        // Esta función usa la lógica existente de VisuBloq para generar el PDF
        return new Promise((resolve, reject) => {
            try {
                // Trigger la generación original pero interceptamos el resultado
                const originalDownloadInstructions = window.downloadInstructions;
                
                if (!originalDownloadInstructions) {
                    throw new Error('Función de generación PDF no encontrada');
                }

                // Patch temporal para capturar el blob
                const originalCreateObjectURL = URL.createObjectURL;
                URL.createObjectURL = (blob) => {
                    // Restaurar la función original
                    URL.createObjectURL = originalCreateObjectURL;
                    
                    // Devolver el blob en lugar de crear URL
                    resolve(blob);
                    
                    // Evitar que se descargue automáticamente
                    return '#';
                };

                // Ejecutar la generación original
                originalDownloadInstructions();

            } catch (error) {
                reject(error);
            }
        });
    }

    showPDFModal(pdfUrl) {
        // Crear modal HTML
        const modal = document.createElement('div');
        modal.className = 'visubloq-ultra-modal';
        modal.innerHTML = `
            <div class="visubloq-ultra-modal-content">
                <div class="visubloq-ultra-modal-header">
                    <h2>🧱 Tu Diseño LEGO Está Listo</h2>
                    <button class="visubloq-ultra-close" onclick="this.closest('.visubloq-ultra-modal').remove()">&times;</button>
                </div>
                
                <div class="visubloq-ultra-modal-body">
                    <div class="visubloq-ultra-preview">
                        <canvas id="visubloq-ultra-preview-canvas"></canvas>
                    </div>
                    
                    <div class="visubloq-ultra-instructions">
                        <h3>📋 Instrucciones:</h3>
                        <ol>
                            <li><strong>Copia</strong> el enlace de abajo (se copiará automáticamente)</li>
                            <li><strong>Pégalo</strong> en el carrito de Shopify en el campo "Enlace PDF"</li>
                            <li><strong>Completa</strong> tu compra</li>
                        </ol>
                    </div>
                    
                    <div class="visubloq-ultra-link-section">
                        <label>📎 Enlace a tus Instrucciones PDF:</label>
                        <div class="visubloq-ultra-link-container">
                            <input type="text" 
                                   id="visubloq-ultra-pdf-link" 
                                   value="${pdfUrl}" 
                                   readonly>
                            <button id="visubloq-ultra-copy-btn" onclick="visubloqUltraSimple.copyLink()">
                                📋 COPIAR
                            </button>
                        </div>
                        <div id="visubloq-ultra-copy-feedback" class="visubloq-ultra-feedback">
                            ✅ ¡Enlace copiado! Pégalo en Shopify.
                        </div>
                    </div>
                </div>
                
                <div class="visubloq-ultra-modal-footer">
                    <button class="visubloq-ultra-btn visubloq-ultra-btn-secondary" 
                            onclick="this.closest('.visubloq-ultra-modal').remove()">
                        ✏️ EDITAR DISEÑO
                    </button>
                    <button class="visubloq-ultra-btn visubloq-ultra-btn-primary" 
                            onclick="window.open('${this.shopifyProductUrl}', '_blank')">
                        🛒 CONSTRUIR AHORA
                    </button>
                </div>
            </div>
        `;

        // Añadir al DOM
        document.body.appendChild(modal);

        // Generar preview del canvas
        this.generatePreview();

        // Auto-copiar el enlace
        setTimeout(() => this.copyLink(), 500);
    }

    generatePreview() {
        const canvas = document.getElementById('visubloq-ultra-preview-canvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        canvas.width = 300;
        canvas.height = 300;

        // Copiar del canvas principal de VisuBloq
        const mainCanvas = document.getElementById('step-4-canvas-upscaled');
        if (mainCanvas) {
            ctx.drawImage(mainCanvas, 0, 0, canvas.width, canvas.height);
        } else {
            // Fallback: crear preview básico
            ctx.fillStyle = '#f0f0f0';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#333';
            ctx.font = '20px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('🧱 Diseño LEGO', canvas.width/2, canvas.height/2);
        }
    }

    copyLink() {
        const linkInput = document.getElementById('visubloq-ultra-pdf-link');
        const feedback = document.getElementById('visubloq-ultra-copy-feedback');
        const copyBtn = document.getElementById('visubloq-ultra-copy-btn');

        if (linkInput) {
            linkInput.select();
            linkInput.setSelectionRange(0, 99999); // Para móviles
            
            navigator.clipboard.writeText(linkInput.value).then(() => {
                feedback.style.display = 'block';
                copyBtn.textContent = '✅ COPIADO';
                copyBtn.style.background = '#28a745';
                
                setTimeout(() => {
                    copyBtn.textContent = '📋 COPIAR';
                    copyBtn.style.background = '';
                }, 2000);
            });
        }
    }

    showLoadingOverlay() {
        const overlay = document.getElementById('lego-preview-loading-overlay');
        if (overlay) {
            overlay.classList.add('show');
        }
    }

    hideLoadingOverlay() {
        const overlay = document.getElementById('lego-preview-loading-overlay');
        if (overlay) {
            overlay.classList.remove('show');
        }
    }

    injectModalCSS() {
        const style = document.createElement('style');
        style.textContent = `
            .visubloq-ultra-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                z-index: 10000;
                display: flex;
                justify-content: center;
                align-items: center;
                font-family: 'Dortmund', Arial, sans-serif;
            }

            .visubloq-ultra-modal-content {
                background: white;
                border-radius: 16px;
                max-width: 600px;
                width: 90%;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: visubloqUltraSlideIn 0.3s ease-out;
            }

            @keyframes visubloqUltraSlideIn {
                from { transform: translateY(-50px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }

            .visubloq-ultra-modal-header {
                padding: 24px 24px 16px;
                border-bottom: 2px solid #f0f0f0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .visubloq-ultra-modal-header h2 {
                margin: 0;
                color: #333;
                font-size: 1.5em;
            }

            .visubloq-ultra-close {
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                color: #666;
                padding: 0;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .visubloq-ultra-close:hover {
                color: #333;
                background: #f0f0f0;
                border-radius: 50%;
            }

            .visubloq-ultra-modal-body {
                padding: 24px;
            }

            .visubloq-ultra-preview {
                text-align: center;
                margin-bottom: 24px;
            }

            #visubloq-ultra-preview-canvas {
                border: 3px solid #333;
                border-radius: 12px;
                max-width: 250px;
                height: auto;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            }

            .visubloq-ultra-instructions {
                background: #f8f9fa;
                padding: 16px;
                border-radius: 8px;
                margin-bottom: 24px;
                border-left: 4px solid #007bff;
            }

            .visubloq-ultra-instructions h3 {
                margin: 0 0 12px 0;
                color: #333;
            }

            .visubloq-ultra-instructions ol {
                margin: 0;
                padding-left: 20px;
            }

            .visubloq-ultra-instructions li {
                margin-bottom: 8px;
                color: #555;
            }

            .visubloq-ultra-link-section {
                margin-bottom: 24px;
            }

            .visubloq-ultra-link-section label {
                display: block;
                font-weight: bold;
                margin-bottom: 8px;
                color: #333;
            }

            .visubloq-ultra-link-container {
                display: flex;
                gap: 8px;
                margin-bottom: 8px;
            }

            #visubloq-ultra-pdf-link {
                flex: 1;
                padding: 12px;
                border: 2px solid #ddd;
                border-radius: 6px;
                font-family: monospace;
                font-size: 12px;
                background: #f9f9f9;
            }

            #visubloq-ultra-copy-btn {
                padding: 12px 16px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-weight: bold;
                font-family: 'Dortmund', Arial, sans-serif;
                transition: background 0.2s;
            }

            #visubloq-ultra-copy-btn:hover {
                background: #0056b3;
            }

            .visubloq-ultra-feedback {
                display: none;
                background: #d4edda;
                color: #155724;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 14px;
                border: 1px solid #c3e6cb;
            }

            .visubloq-ultra-modal-footer {
                padding: 16px 24px 24px;
                display: flex;
                gap: 12px;
                justify-content: flex-end;
            }

            .visubloq-ultra-btn {
                padding: 12px 24px;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-weight: bold;
                font-family: 'Dortmund', Arial, sans-serif;
                text-transform: uppercase;
                transition: all 0.2s;
            }

            .visubloq-ultra-btn-secondary {
                background: #6c757d;
                color: white;
            }

            .visubloq-ultra-btn-secondary:hover {
                background: #545b62;
                transform: translateY(-1px);
            }

            .visubloq-ultra-btn-primary {
                background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
                color: white;
                box-shadow: 0 4px 16px rgba(255, 107, 53, 0.3);
            }

            .visubloq-ultra-btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 24px rgba(255, 107, 53, 0.4);
            }

            @media (max-width: 768px) {
                .visubloq-ultra-modal-content {
                    width: 95%;
                    margin: 20px;
                }
                
                .visubloq-ultra-link-container {
                    flex-direction: column;
                }
                
                .visubloq-ultra-modal-footer {
                    flex-direction: column;
                }
                
                #visubloq-ultra-pdf-link {
                    font-size: 10px;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// Crear instancia global
window.visubloqUltraSimple = new VisuBloqUltraSimple();

console.log('🧱 VisuBloq Ultra Simple cargado - Sistema 100% Frontend sin backend');