<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧱 VisuBloq Admin Panel - Gestión Avanzada</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #ff6b35;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ff6b35;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .orders-section {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .section-header {
            background: #ff6b35;
            color: white;
            padding: 1rem 2rem;
            font-weight: bold;
            font-size: 1.1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .close {
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }
        
        .close:hover {
            color: #333;
        }
        
        .pieces-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .piece-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }
        
        .color-sample {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin: 0 auto 0.5rem;
            border: 2px solid #333;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #ff6b35;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .order-summary {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ff6b35;
        }
        
        .summary-label {
            font-size: 0.8rem;
            color: #666;
        }
        
        .pieces-table {
            margin-top: 1rem;
        }
        
        .pieces-table th {
            background: #ff6b35;
            color: white;
        }
        
        .pdf-list {
            margin-top: 1rem;
        }
        
        .pdf-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }
        
        .refresh-btn {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">🧱 VisuBloq Admin Panel</div>
        <div class="user-info">
            <span>👤 Administrador</span>
            <button class="btn btn-danger" onclick="logout()">Cerrar Sesión</button>
        </div>
    </header>

    <div class="container">
        <!-- Estadísticas -->
        <div class="stats-grid" id="stats-grid">
            <div class="stat-card">
                <div class="stat-number" id="total-orders">-</div>
                <div class="stat-label">Total Pedidos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-revenue">-</div>
                <div class="stat-label">Ingresos Totales</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="pending-orders">-</div>
                <div class="stat-label">Pedidos Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="pdfs-generated">-</div>
                <div class="stat-label">PDFs Generados</div>
            </div>
        </div>

        <!-- Lista de Pedidos -->
        <div class="orders-section">
            <div class="section-header">
                📋 Gestión de Pedidos VisuBloq
                <button class="refresh-btn" onclick="loadOrders()">🔄 Actualizar</button>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Pedido #</th>
                            <th>Cliente</th>
                            <th>Email</th>
                            <th>Valor</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>PDFs</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="orders-table-body">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">
                                <div class="loading"></div> Cargando pedidos...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Detalle de Pedido -->
    <div id="order-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📋 Detalle del Pedido</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div id="order-detail-content">
                <!-- Contenido del detalle se carga aquí -->
            </div>
        </div>
    </div>

    <script>
        // 🔧 CONFIGURACIÓN
        const API_BASE = 'orders.php';

        // 🚀 INICIALIZACIÓN
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
            loadOrders();
        });

        // 📊 CARGAR ESTADÍSTICAS
        async function loadStatistics() {
            try {
                const response = await fetch(`${API_BASE}?action=stats`);
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('total-orders').textContent = data.data.total_orders;
                    document.getElementById('total-revenue').textContent = '€' + data.data.total_revenue;
                    document.getElementById('pending-orders').textContent = data.data.pending_orders;
                    document.getElementById('pdfs-generated').textContent = data.data.pdfs_generated;
                }
            } catch (error) {
                console.error('Error cargando estadísticas:', error);
            }
        }

        // 📋 CARGAR LISTA DE PEDIDOS
        async function loadOrders() {
            try {
                const response = await fetch(`${API_BASE}?action=list`);
                const data = await response.json();
                
                if (data.success) {
                    renderOrdersTable(data.data);
                } else {
                    document.getElementById('orders-table-body').innerHTML = 
                        `<tr><td colspan="8" style="text-align: center;">❌ Error: ${data.message}</td></tr>`;
                }
            } catch (error) {
                console.error('Error cargando pedidos:', error);
                document.getElementById('orders-table-body').innerHTML = 
                    `<tr><td colspan="8" style="text-align: center;">❌ Error de conexión</td></tr>`;
            }
        }

        // 🎨 RENDERIZAR TABLA DE PEDIDOS
        function renderOrdersTable(orders) {
            const tbody = document.getElementById('orders-table-body');
            
            if (orders.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align: center;">📦 No hay pedidos</td></tr>`;
                return;
            }
            
            tbody.innerHTML = orders.map(order => `
                <tr>
                    <td><strong>#${order.order_number}</strong></td>
                    <td>${order.customer_name || 'N/A'}</td>
                    <td>${order.customer_email || 'N/A'}</td>
                    <td><strong>€${order.order_value || '0.00'}</strong></td>
                    <td>
                        <span class="status-badge ${order.order_status === 'paid' ? 'status-paid' : 'status-pending'}">
                            ${order.order_status === 'paid' ? '✅ Pagado' : '⏳ Pendiente'}
                        </span>
                    </td>
                    <td>${new Date(order.created_at).toLocaleDateString('es-ES')}</td>
                    <td><span class="status-badge status-paid">${order.pdf_count || 0}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-primary" onclick="viewOrderDetail(${order.id})">
                                🔍 Ver Detalle
                            </button>
                            <button class="btn btn-success" onclick="generatePiecesPDF(${order.id})">
                                📄 PDF Piezas
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // 🔍 VER DETALLE DE PEDIDO
        async function viewOrderDetail(orderId) {
            try {
                document.getElementById('order-detail-content').innerHTML = '<div class="loading"></div> Cargando detalle...';
                document.getElementById('order-modal').style.display = 'flex';
                
                const response = await fetch(`${API_BASE}?action=detail&id=${orderId}`);
                const data = await response.json();
                
                if (data.success) {
                    renderOrderDetail(data.data);
                } else {
                    document.getElementById('order-detail-content').innerHTML = `❌ Error: ${data.message}`;
                }
            } catch (error) {
                console.error('Error cargando detalle:', error);
                document.getElementById('order-detail-content').innerHTML = '❌ Error de conexión';
            }
        }

        // 🎨 RENDERIZAR DETALLE DE PEDIDO
        function renderOrderDetail(data) {
            const { order, pieces, pdfs } = data;
            
            let content = `
                <div class="order-summary">
                    <h3>📋 Información del Pedido</h3>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="summary-number">#${order.order_number}</div>
                            <div class="summary-label">Número de Pedido</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-number">€${order.order_value}</div>
                            <div class="summary-label">Valor Total</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-number">${order.order_status === 'paid' ? '✅' : '⏳'}</div>
                            <div class="summary-label">${order.order_status === 'paid' ? 'Pagado' : 'Pendiente'}</div>
                        </div>
                    </div>
                    <p><strong>Cliente:</strong> ${order.customer_name || 'N/A'}</p>
                    <p><strong>Email:</strong> ${order.customer_email || 'N/A'}</p>
                    <p><strong>Fecha:</strong> ${new Date(order.created_at).toLocaleString('es-ES')}</p>
                </div>
            `;
            
            // Información de piezas VisuBloq
            if (pieces) {
                content += `
                    <div class="order-summary">
                        <h3>🧱 Información del VisuBloq Personalizado</h3>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <div class="summary-number">${pieces.total_pieces}</div>
                                <div class="summary-label">Total Piezas</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number">${pieces.color_count}</div>
                                <div class="summary-label">Colores Diferentes</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number">${pieces.dimensions}</div>
                                <div class="summary-label">Dimensiones</div>
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 1rem;">📊 Detalle de Piezas por Color</h4>
                        <table class="pieces-table">
                            <thead>
                                <tr>
                                    <th>Color</th>
                                    <th>Código Hex</th>
                                    <th>Cantidad</th>
                                    <th>Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${pieces.color_analysis.map(color => `
                                    <tr>
                                        <td>
                                            <div class="color-sample" style="background-color: ${color.hex}; width: 20px; height: 20px; display: inline-block; margin-right: 10px; border-radius: 50%;"></div>
                                        </td>
                                        <td><code>${color.hex}</code></td>
                                        <td><strong>${color.quantity}</strong></td>
                                        <td>${color.percentage}%</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                content += `
                    <div class="order-summary">
                        <h3>⚠️ Sin Datos de VisuBloq</h3>
                        <p>Este pedido no tiene información de diseño VisuBloq asociada.</p>
                    </div>
                `;
            }
            
            // PDFs generados
            content += `
                <div class="order-summary">
                    <h3>📄 PDFs Generados</h3>
                    <div class="action-buttons" style="margin-bottom: 1rem;">
                        <button class="btn btn-success" onclick="generatePiecesPDF(${order.id})">
                            📄 Generar PDF de Piezas
                        </button>
                        <button class="btn btn-primary" onclick="generateInstructionsPDF(${order.id})">
                            📋 Generar PDF de Instrucciones
                        </button>
                    </div>
            `;
            
            if (pdfs && pdfs.length > 0) {
                content += `<div class="pdf-list">`;
                pdfs.forEach(pdf => {
                    content += `
                        <div class="pdf-item">
                            <div>
                                <strong>${pdf.pdf_filename}</strong><br>
                                <small>Tipo: ${pdf.pdf_type} | Tamaño: ${(pdf.pdf_size / 1024).toFixed(1)} KB | 
                                Generado: ${new Date(pdf.generated_at).toLocaleString('es-ES')}</small>
                            </div>
                            <button class="btn btn-primary" onclick="downloadPDF('${pdf.pdf_filename}')">
                                💾 Descargar
                            </button>
                        </div>
                    `;
                });
                content += `</div>`;
            } else {
                content += `<p>No hay PDFs generados para este pedido.</p>`;
            }
            
            content += `</div>`;
            
            document.getElementById('order-detail-content').innerHTML = content;
        }

        // 📄 GENERAR PDF DE PIEZAS
        async function generatePiecesPDF(orderId) {
            try {
                const response = await fetch(`${API_BASE}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'generate_pieces_pdf',
                        order_id: orderId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ PDF de piezas generado exitosamente');
                    if (document.getElementById('order-modal').style.display === 'flex') {
                        viewOrderDetail(orderId); // Recargar el detalle
                    }
                    loadOrders(); // Recargar la lista
                } else {
                    alert('❌ Error generando PDF: ' + data.message);
                }
            } catch (error) {
                console.error('Error generando PDF:', error);
                alert('❌ Error de conexión');
            }
        }

        // 📄 GENERAR PDF DE INSTRUCCIONES
        async function generateInstructionsPDF(orderId) {
            try {
                const response = await fetch(`${API_BASE}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'generate_pdf',
                        order_id: orderId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ PDF de instrucciones generado exitosamente');
                    if (document.getElementById('order-modal').style.display === 'flex') {
                        viewOrderDetail(orderId); // Recargar el detalle
                    }
                    loadOrders(); // Recargar la lista
                } else {
                    alert('❌ Error generando PDF: ' + data.message);
                }
            } catch (error) {
                console.error('Error generando PDF:', error);
                alert('❌ Error de conexión');
            }
        }

        // 💾 DESCARGAR PDF
        function downloadPDF(filename) {
            window.open(`../api/generate-pdf.php?action=download&file=${encodeURIComponent(filename)}`, '_blank');
        }

        // 🔐 CERRAR SESIÓN
        function logout() {
            if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                window.location.href = 'login.php?action=logout';
            }
        }

        // 📱 CERRAR MODAL
        function closeModal() {
            document.getElementById('order-modal').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('order-modal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
