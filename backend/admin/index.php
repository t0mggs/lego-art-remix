<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 VisuBloq Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .container {
            max-width: 1200px;
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
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .orders-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .table-header {
            background: #667eea;
            color: white;
            padding: 1rem 2rem;
            font-weight: bold;
            font-size: 1.1rem;
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
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status.paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5a6fd8;
        }
        
        .btn.danger {
            background: #dc3545;
        }
        
        .btn.danger:hover {
            background: #c82333;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .loading {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .refresh-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        
        .refresh-btn:hover {
            transform: scale(1.1);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 0.8rem;
            }
            
            th, td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">🧱 VisuBloq Admin</div>
        <div>
            <span id="last-update">Cargando...</span>
            <button class="btn" onclick="logout()">Cerrar Sesión</button>
        </div>
    </div>

    <div class="container">
        <!-- Estadísticas -->
        <div class="stats-grid">
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

        <!-- Tabla de Pedidos -->
        <div class="orders-table">
            <div class="table-header">
                📋 Pedidos Recientes
            </div>
            <div id="orders-content">
                <div class="loading">
                    🔄 Cargando pedidos...
                </div>
            </div>
        </div>
    </div>

    <button class="refresh-btn" onclick="loadDashboard()" title="Actualizar">
        🔄
    </button>

    <script>
        // 🔄 CARGAR DASHBOARD
        async function loadDashboard() {
            try {
                // Cargar estadísticas
                const statsResponse = await fetch('orders.php?action=stats');
                const stats = await statsResponse.json();
                
                if (stats.success) {
                    document.getElementById('total-orders').textContent = stats.data.total_orders;
                    document.getElementById('total-revenue').textContent = '€' + stats.data.total_revenue;
                    document.getElementById('pending-orders').textContent = stats.data.pending_orders;
                    document.getElementById('pdfs-generated').textContent = stats.data.pdfs_generated;
                }
                
                // Cargar pedidos
                const ordersResponse = await fetch('orders.php?action=list');
                const orders = await ordersResponse.json();
                
                if (orders.success) {
                    renderOrdersTable(orders.data);
                }
                
                document.getElementById('last-update').textContent = 'Actualizado: ' + new Date().toLocaleTimeString();
                
            } catch (error) {
                console.error('Error cargando dashboard:', error);
                document.getElementById('orders-content').innerHTML = 
                    '<div class="no-data">❌ Error cargando datos</div>';
            }
        }
        
        // 📋 RENDERIZAR TABLA DE PEDIDOS
        function renderOrdersTable(orders) {
            const content = document.getElementById('orders-content');
            
            if (orders.length === 0) {
                content.innerHTML = '<div class="no-data">📭 No hay pedidos todavía</div>';
                return;
            }
            
            const tableHTML = `
                <table>
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Email</th>
                            <th>Valor</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${orders.map(order => `
                            <tr>
                                <td><strong>${order.order_number}</strong></td>
                                <td>${order.customer_name}</td>
                                <td>${order.customer_email}</td>
                                <td>€${order.order_value}</td>
                                <td><span class="status ${order.order_status}">${order.order_status}</span></td>
                                <td>${new Date(order.created_at).toLocaleDateString()}</td>
                                <td>
                                    <div class="actions">
                                        <button class="btn" onclick="viewOrder(${order.id})">👀 Ver</button>
                                        <button class="btn" onclick="generatePDF(${order.id})">📄 PDF</button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            
            content.innerHTML = tableHTML;
        }
        
        // 👀 VER DETALLES DEL PEDIDO
        function viewOrder(orderId) {
            window.open(`order-details.php?id=${orderId}`, '_blank');
        }
        
        // 📄 GENERAR PDF PARA PEDIDO
        async function generatePDF(orderId) {
            if (!confirm('¿Generar PDF de instrucciones para este pedido?')) return;
            
            try {
                const response = await fetch('orders.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'generate_pdf',
                        order_id: orderId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('✅ PDF generado exitosamente!');
                    loadDashboard(); // Recargar
                } else {
                    alert('❌ Error: ' + result.message);
                }
            } catch (error) {
                alert('❌ Error generando PDF: ' + error.message);
            }
        }
        
        // 🚪 CERRAR SESIÓN
        function logout() {
            if (confirm('¿Cerrar sesión?')) {
                window.location.href = 'login.php?logout=1';
            }
        }
        
        // 🔄 AUTO-REFRESH CADA 30 SEGUNDOS
        setInterval(loadDashboard, 30000);
        
        // 🚀 CARGAR AL INICIO
        loadDashboard();
    </script>
</body>
</html>
