-- 📊 DATOS DE PRUEBA PARA VISUBLOQ
-- Ejecuta este SQL en phpMyAdmin para añadir pedidos de prueba

-- Primero, insertar algunos pedidos de ejemplo
INSERT INTO orders (shopify_order_id, order_number, customer_name, customer_email, order_value, order_status, created_at) VALUES
(1001234567, '#1001', 'Juan Pérez', 'juan@example.com', 29.99, 'paid', '2025-01-15 10:30:00'),
(1001234568, '#1002', 'María García', 'maria@example.com', 45.50, 'paid', '2025-01-16 14:20:00'),
(1001234569, '#1003', 'Carlos López', 'carlos@example.com', 32.75, 'pending', '2025-01-16 16:45:00'),
(1001234570, '#1004', 'Ana Martínez', 'ana@example.com', 28.00, 'paid', '2025-01-17 09:15:00');

-- Asociar los diseños existentes con pedidos
UPDATE design_images 
SET order_id = 1 
WHERE design_id LIKE 'VB-%TEST';

-- Insertar más diseños asociados a pedidos
INSERT INTO design_images (design_id, session_id, order_id, width, height, pieces_data, pdf_blob, visubloq_config, status) VALUES
('VB-1758000001-SHOP', 'SESS-001', 1, 32, 32, 
 '{"totalPieces": 48, "uniqueColors": 5, "studMap": []}', 
 'JVBERi0xLjQKMSAwIG9iago=', 
 '{"imageWidth": 32, "imageHeight": 32, "studSize": 8}', 
 'purchased'),

('VB-1758000002-SHOP', 'SESS-002', 2, 48, 48, 
 '{"totalPieces": 72, "uniqueColors": 6, "studMap": []}', 
 'JVBERi0xLjQKMSAwIG9iago=', 
 '{"imageWidth": 48, "imageHeight": 48, "studSize": 8}', 
 'purchased'),

('VB-1758000003-SHOP', 'SESS-003', 4, 24, 24, 
 '{"totalPieces": 36, "uniqueColors": 4, "studMap": []}', 
 'JVBERi0xLjQKMSAwIG9iago=', 
 '{"imageWidth": 24, "imageHeight": 24, "studSize": 8}', 
 'purchased');

-- Insertar piezas para los nuevos diseños
INSERT INTO image_pieces (design_id, color_name, color_code, piece_count) VALUES
-- Para diseño VB-1758000001-SHOP
('VB-1758000001-SHOP', 'Bright Red', '#FF0000', 18),
('VB-1758000001-SHOP', 'Bright Blue', '#0000FF', 15),
('VB-1758000001-SHOP', 'Bright Green', '#00FF00', 10),
('VB-1758000001-SHOP', 'Bright Yellow', '#FFFF00', 5),

-- Para diseño VB-1758000002-SHOP  
('VB-1758000002-SHOP', 'Black', '#000000', 25),
('VB-1758000002-SHOP', 'White', '#FFFFFF', 20),
('VB-1758000002-SHOP', 'Bright Red', '#FF0000', 12),
('VB-1758000002-SHOP', 'Bright Blue', '#0000FF', 8),
('VB-1758000002-SHOP', 'Bright Green', '#00FF00', 7),

-- Para diseño VB-1758000003-SHOP
('VB-1758000003-SHOP', 'Bright Orange', '#FF8000', 12),
('VB-1758000003-SHOP', 'Dark Purple', '#800080', 10),
('VB-1758000003-SHOP', 'Bright Pink', '#FF69B4', 8),
('VB-1758000003-SHOP', 'Lime Green', '#32CD32', 6);

-- Insertar algunos order_pieces para estadísticas
INSERT INTO order_pieces (order_id, piece_types, total_pieces, pieces_data) VALUES
(1, 4, 48, '{"colors": ["Red", "Blue", "Green", "Yellow"], "breakdown": [18, 15, 10, 5]}'),
(2, 5, 72, '{"colors": ["Black", "White", "Red", "Blue", "Green"], "breakdown": [25, 20, 12, 8, 7]}'),
(4, 4, 36, '{"colors": ["Orange", "Purple", "Pink", "Lime"], "breakdown": [12, 10, 8, 6]}');