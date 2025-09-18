-- Base de datos VisuBloq - Estructura completa
-- Tabla de pedidos de Shopify
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shopify_order_id VARCHAR(64) UNIQUE NOT NULL,
    order_number VARCHAR(32),
    customer_name VARCHAR(128),
    customer_email VARCHAR(128),
    order_value DECIMAL(10,2),
    order_status VARCHAR(32) DEFAULT 'pending', -- 'paid', 'pending', 'test', etc.
    payment_status VARCHAR(50) DEFAULT 'pending',
    fulfillment_status VARCHAR(50) DEFAULT 'unfulfilled',
    dimensions VARCHAR(32),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_shopify_order_id (shopify_order_id),
    INDEX idx_order_status (order_status),
    INDEX idx_payment_status (payment_status)
);

-- Tabla para almacenar imágenes de diseño generadas
CREATE TABLE IF NOT EXISTS design_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    design_id VARCHAR(32) UNIQUE NOT NULL,
    session_id VARCHAR(128),
    order_id INT NULL,
    width INT NOT NULL,
    height INT NOT NULL,
    pieces_data JSON,
    pdf_blob LONGBLOB,
    visubloq_config JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('generated', 'purchased', 'fulfilled') DEFAULT 'generated',
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_design_id (design_id),
    INDEX idx_session_id (session_id),
    INDEX idx_order_id (order_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Tabla de piezas por pedido (para compatibilidad con sistema actual)
CREATE TABLE IF NOT EXISTS order_pieces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    design_id VARCHAR(32) NULL,
    piece_colors JSON, -- JSON: { "color_name": cantidad, ... }
    visubloq_config JSON,
    total_pieces INT DEFAULT 0,
    dimensions VARCHAR(32),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_design_id (design_id)
);

-- Tabla para almacenar piezas individuales por color (para consultas fáciles)
CREATE TABLE IF NOT EXISTS image_pieces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    design_id VARCHAR(32) NOT NULL,
    color_name VARCHAR(100),
    color_code VARCHAR(7),
    piece_count INT DEFAULT 0,
    INDEX idx_design_id (design_id),
    INDEX idx_color_name (color_name),
    FOREIGN KEY (design_id) REFERENCES design_images(design_id) ON DELETE CASCADE
);

-- Tabla de PDFs generados (legacy - mantenida para compatibilidad)
CREATE TABLE IF NOT EXISTS order_pdfs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    design_id VARCHAR(32) NULL,
    pdf_filename VARCHAR(128),
    pdf_path VARCHAR(256),
    pdf_size INT,
    pdf_type VARCHAR(32) DEFAULT 'instructions',
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_design_id (design_id)
);

-- Tabla para logs del sistema
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level VARCHAR(20) DEFAULT 'INFO',
    message TEXT,
    context JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_level (level),
    INDEX idx_created_at (created_at)
);
