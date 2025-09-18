<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VisuBloq Admin - Dashboard de Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .piece-color-sample {
            width: 20px;
            height: 20px;
            border-radius: 3px;
            border: 1px solid #ddd;
            display: inline-block;
            margin-right: 8px;
        }
        .order-card {
            transition: all 0.3s ease;
            border-left: 4px solid #007bff;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .pdf-available {
            border-left-color: #28a745 !important;
        }
        .no-pdf {
            border-left-color: #dc3545 !important;
        }
        .piece-list {
            max-height: 200px;
            overflow-y: auto;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cubes me-2"></i>VisuBloq Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home me-1"></i>Inicio
                </a>
                <a class="nav-link" href="#" onclick="logout()">
                    <i class="fas fa-sign-out-alt me-1"></i>Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-chart-bar me-2"></i>Estadísticas del Sistema
                        </h5>
                        <div class="row" id="stats-container">
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 id="total-orders">-</h3>
                                    <small>Total Pedidos</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 id="total-revenue">-</h3>
                                    <small>Ingresos Totales</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 id="pending-orders">-</h3>
                                    <small>Pendientes</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 id="total-designs">-</h3>
                                    <small>Diseños Creados</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 id="designs-with-pdf">-</h3>
                                    <small>Con PDF</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h3 id="pdfs-generated">-</h3>
                                    <small>PDFs Generados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="row mb-3">
            <div class="col-md-6">
                <h4><i class="fas fa-shopping-cart me-2"></i>Pedidos Pagados</h4>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="search-input" placeholder="Buscar pedido...">
                    <button class="btn btn-outline-secondary" onclick="searchOrders()">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-outline-primary" onclick="loadOrders()">
                        <i class="fas fa-refresh"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de pedidos -->
        <div id="orders-container">
            <div class="text-center py-4">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Cargando pedidos...</p>
            </div>
        </div>
    </div>

    <!-- Modal para detalle del pedido -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>Detalle del Pedido
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="order-detail-content">
                    <!-- Contenido dinámico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="download-pdf-btn" style="display: none;">
                        <i class="fas fa-download me-1"></i>Descargar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentOrders = [];

        // Cargar datos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
            loadOrders();
        });

        // Cargar estadísticas
        async function loadStatistics() {
            try {
                const response = await fetch('orders.php?action=stats');
                const data = await response.json();
                
                if (data.success) {
                    const stats = data.data;
                    document.getElementById('total-orders').textContent = stats.total_orders;
                    document.getElementById('total-revenue').textContent = '€' + stats.total_revenue;
                    document.getElementById('pending-orders').textContent = stats.pending_orders;
                    document.getElementById('total-designs').textContent = stats.total_designs || '0';
                    document.getElementById('designs-with-pdf').textContent = stats.designs_with_pdf || '0';
                    document.getElementById('pdfs-generated').textContent = stats.pdfs_generated;
                }
            } catch (error) {
                console.error('Error cargando estadísticas:', error);
            }
        }

        // Cargar pedidos
        async function loadOrders() {
            try {
                const response = await fetch('orders.php?action=list');
                const data = await response.json();
                
                if (data.success) {
                    currentOrders = data.data;
                    renderOrders(currentOrders);
                } else {
                    showError('Error cargando pedidos: ' + data.message);
                }
            } catch (error) {
                console.error('Error cargando pedidos:', error);
                showError('Error de conexión');
            }
        }

        // Renderizar pedidos
        function renderOrders(orders) {
            const container = document.getElementById('orders-container');
            
            if (orders.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay pedidos pagados</h5>
                        <p class="text-muted">Los pedidos aparecerán aquí cuando se confirmen los pagos.</p>
                    </div>
                `;
                return;
            }

            const html = orders.map(order => {
                const hasDesign = order.design_count > 0;
                const hasPDF = order.pdf_count > 0;
                const cardClass = hasDesign && hasPDF ? 'pdf-available' : 'no-pdf';
                
                const pieceColors = order.piece_colors ? JSON.parse(order.piece_colors) : {};
                const totalPieces = order.total_pieces || 0;
                
                return `
                    <div class="card order-card ${cardClass} mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <h6 class="card-title mb-1">
                                        <i class="fas fa-receipt me-1"></i>
                                        Pedido #${order.shopify_order_id}
                                    </h6>
                                    <small class="text-muted">${order.customer_name || 'Cliente'}</small><br>
                                    <small class="text-muted">${order.customer_email || ''}</small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <strong>€${order.order_value}</strong><br>
                                    <span class="badge bg-success">${order.order_status}</span>
                                </div>
                                <div class="col-md-2 text-center">
                                    <i class="fas fa-cubes me-1"></i>
                                    <strong>${totalPieces}</strong> piezas<br>
                                    <small class="text-muted">${Object.keys(pieceColors).length} colores</small>
                                </div>
                                <div class="col-md-2 text-center">
                                    ${hasDesign ? 
                                        `<i class="fas fa-check-circle text-success me-1"></i>
                                         <span class="text-success">Diseño Listo</span>` :
                                        `<i class="fas fa-clock text-warning me-1"></i>
                                         <span class="text-warning">Sin Diseño</span>`
                                    }
                                    ${hasPDF ? 
                                        `<br><i class="fas fa-file-pdf text-danger me-1"></i>
                                         <span class="text-danger">PDF Disponible</span>` : ''
                                    }
                                </div>
                                <div class="col-md-2 text-center">
                                    <small class="text-muted">${new Date(order.created_at).toLocaleDateString()}</small>
                                </div>
                                <div class="col-md-1 text-end">
                                    <button class="btn btn-sm btn-outline-primary" onclick="showOrderDetail(${order.id})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    ${hasDesign && hasPDF ? 
                                        `<button class="btn btn-sm btn-outline-success ms-1" onclick="downloadPDF(${order.id})">
                                            <i class="fas fa-download"></i>
                                        </button>` : ''
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = html;
        }

        // Mostrar detalle del pedido
        async function showOrderDetail(orderId) {
            try {
                const response = await fetch(`orders.php?action=detail&id=${orderId}`);
                const data = await response.json();
                
                if (data.success) {
                    const order = data.data.order;
                    const pieces = data.data.pieces;
                    
                    let piecesList = '';
                    if (pieces && pieces.color_analysis) {
                        piecesList = pieces.color_analysis.map(color => `
                            <tr>
                                <td>
                                    <div class="piece-color-sample" style="background-color: ${color.hex}"></div>
                                    ${color.hex}
                                </td>
                                <td class="text-center">${color.quantity}</td>
                                <td class="text-center">${color.percentage}%</td>
                            </tr>
                        `).join('');
                    }

                    const content = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-info-circle me-2"></i>Información del Pedido</h6>
                                <table class="table table-sm">
                                    <tr><th>ID Shopify:</th><td>${order.shopify_order_id}</td></tr>
                                    <tr><th>Cliente:</th><td>${order.customer_name || 'N/A'}</td></tr>
                                    <tr><th>Email:</th><td>${order.customer_email || 'N/A'}</td></tr>
                                    <tr><th>Valor:</th><td>€${order.order_value}</td></tr>
                                    <tr><th>Estado:</th><td><span class="badge bg-success">${order.order_status}</span></td></tr>
                                    <tr><th>Fecha:</th><td>${new Date(order.created_at).toLocaleString()}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-cubes me-2"></i>Resumen de Piezas</h6>
                                ${pieces ? `
                                    <p><strong>Total de piezas:</strong> ${pieces.total_pieces}</p>
                                    <p><strong>Tipos de colores:</strong> ${pieces.color_count}</p>
                                    <p><strong>Dimensiones:</strong> ${pieces.dimensions || 'N/A'}</p>
                                ` : '<p class="text-muted">No hay información de piezas disponible</p>'}
                            </div>
                        </div>
                        
                        ${pieces && pieces.color_analysis ? `
                            <hr>
                            <h6><i class="fas fa-palette me-2"></i>Detalle de Piezas por Color</h6>
                            <div class="piece-list">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Color</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Porcentaje</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${piecesList}
                                    </tbody>
                                </table>
                            </div>
                        ` : ''}
                    `;

                    document.getElementById('order-detail-content').innerHTML = content;
                    
                    // Mostrar botón de descarga si hay PDF
                    const downloadBtn = document.getElementById('download-pdf-btn');
                    if (data.data.order.design_count > 0) {
                        downloadBtn.style.display = 'inline-block';
                        downloadBtn.onclick = () => downloadPDF(orderId);
                    } else {
                        downloadBtn.style.display = 'none';
                    }
                    
                    new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
                } else {
                    showError('Error cargando detalle: ' + data.message);
                }
            } catch (error) {
                console.error('Error cargando detalle:', error);
                showError('Error de conexión');
            }
        }

        // Descargar PDF
        function downloadPDF(orderId) {
            window.open(`orders.php?action=download_pdf&order_id=${orderId}`, '_blank');
        }

        // Buscar pedidos
        function searchOrders() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const filtered = currentOrders.filter(order => 
                order.shopify_order_id.toString().includes(searchTerm) ||
                (order.customer_name && order.customer_name.toLowerCase().includes(searchTerm)) ||
                (order.customer_email && order.customer_email.toLowerCase().includes(searchTerm))
            );
            renderOrders(filtered);
        }

        // Cerrar sesión
        function logout() {
            if (confirm('¿Estás seguro que quieres cerrar sesión?')) {
                window.location.href = 'logout.php';
            }
        }

        // Mostrar error
        function showError(message) {
            const container = document.getElementById('orders-container');
            container.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${message}
                </div>
            `;
        }

        // Búsqueda en tiempo real
        document.getElementById('search-input').addEventListener('input', searchOrders);
    </script>
</body>
</html>