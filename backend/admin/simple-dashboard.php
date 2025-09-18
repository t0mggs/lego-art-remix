<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 VisuBloq Admin - Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn-download { background: #28a745; border: none; }
        .btn-download:hover { background: #218838; }
        .order-status-paid { color: #28a745; font-weight: bold; }
        .order-status-pending { color: #ffc107; font-weight: bold; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark">
        <div class="container">
            <span class="navbar-brand">
                <i class="fas fa-cubes me-2"></i>VisuBloq Admin - Pedidos con PDF
            </span>
            <span class="text-white">
                <i class="fas fa-clock me-1"></i><span id="current-time"></span>
            </span>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Estadísticas Simples -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 id="total-orders" class="text-primary">0</h3>
                        <small>Total Pedidos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 id="total-revenue" class="text-success">€0</h3>
                        <small>Ingresos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 id="paid-orders" class="text-success">0</h3>
                        <small>Pagados</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 id="pending-orders" class="text-warning">0</h3>
                        <small>Pendientes</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Pedidos Simple -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Pedidos con PDFs Generados
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Cliente</th>
                                <th>Email</th>
                                <th>Estado</th>
                                <th>Valor</th>
                                <th>Fecha</th>
                                <th>PDF</th>
                            </tr>
                        </thead>
                        <tbody id="orders-table">
                            <tr>
                                <td colspan="7" class="text-center">
                                    <i class="fas fa-spinner fa-spin me-2"></i>Cargando pedidos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Actualizar hora
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleString('es-ES');
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Cargar estadísticas
        async function loadStats() {
            try {
                const response = await fetch('orders.php?action=simple_stats');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('total-orders').textContent = data.data.total_orders;
                    document.getElementById('total-revenue').textContent = '€' + data.data.total_revenue;
                    document.getElementById('paid-orders').textContent = data.data.paid_orders;
                    document.getElementById('pending-orders').textContent = data.data.pending_orders;
                }
            } catch (error) {
                console.error('Error cargando estadísticas:', error);
            }
        }

        // Cargar pedidos
        async function loadOrders() {
            try {
                const response = await fetch('orders.php?action=simple_list');
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('orders-table');
                    tbody.innerHTML = '';
                    
                    if (data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay pedidos con PDFs</td></tr>';
                        return;
                    }
                    
                    data.data.forEach(order => {
                        const row = `
                            <tr>
                                <td><strong>${order.order_number}</strong></td>
                                <td>${order.customer_name}</td>
                                <td>${order.customer_email}</td>
                                <td><span class="order-status-${order.order_status}">${order.order_status.toUpperCase()}</span></td>
                                <td>€${order.order_value}</td>
                                <td>${new Date(order.created_at).toLocaleDateString('es-ES')}</td>
                                <td>
                                    <button class="btn btn-download btn-sm" onclick="downloadPDF('${order.design_id}')">
                                        <i class="fas fa-download me-1"></i>Descargar PDF
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                } else {
                    document.getElementById('orders-table').innerHTML = 
                        '<tr><td colspan="7" class="text-center text-danger">Error: ' + data.message + '</td></tr>';
                }
            } catch (error) {
                console.error('Error cargando pedidos:', error);
                document.getElementById('orders-table').innerHTML = 
                    '<tr><td colspan="7" class="text-center text-danger">Error de conexión</td></tr>';
            }
        }

        // Descargar PDF
        function downloadPDF(designId) {
            window.open(`orders.php?action=download_pdf&design_id=${designId}`, '_blank');
        }

        // Cargar datos al inicio
        loadStats();
        loadOrders();
        
        // Recargar cada 30 segundos
        setInterval(() => {
            loadStats();
            loadOrders();
        }, 30000);
    </script>
</body>
</html>