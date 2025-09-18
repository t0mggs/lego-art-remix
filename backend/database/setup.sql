-- 🗄️ BASE DE DATOS VISUBLOQ
-- Estructura sencilla para gestionar pedidos y PDFs

-- Tabla principal de pedidos
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shopify_order_id BIGINT UNIQUE NOT NULL,
    order_number VARCHAR(50) NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    order_value DECIMAL(10,2) NOT NULL,
    order_status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de información de piezas por pedido
CREATE TABLE order_pieces (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    piece_types INT NOT NULL,
    total_pieces INT NOT NULL,
    pieces_data JSON, -- Guardará el studMap completo con detalles por color
    pieces_by_color JSON, -- JSON específico para tracking de piezas por color
    image_resolution VARCHAR(20),
    image_name VARCHAR(255), -- Nombre de la imagen procesada
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Tabla de PDFs generados
CREATE TABLE order_pdfs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    pdf_filename VARCHAR(255) NOT NULL,
    pdf_path VARCHAR(500) NOT NULL,
    pdf_size INT, -- tamaño en bytes
    pdf_type ENUM('instructions', 'pieces_list') DEFAULT 'pieces_list',
    pdf_content LONGTEXT, -- Para guardar PDF en base64 (Vercel compatible)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Tabla de logs para debugging
CREATE TABLE system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    log_type VARCHAR(50),
    message TEXT,
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para optimización
CREATE INDEX idx_shopify_order_id ON orders(shopify_order_id);
CREATE INDEX idx_order_number ON orders(order_number);
CREATE INDEX idx_customer_email ON orders(customer_email);
CREATE INDEX idx_created_at ON orders(created_at);
