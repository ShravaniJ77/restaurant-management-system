-- ============================================================================
-- Sample Data for Restaurant Management System
-- Import this file to populate the database with test data
-- ============================================================================

-- ============================================================================
-- ADMIN USERS
-- ============================================================================

INSERT INTO admins (full_name, email, password_hash, phone, status) VALUES
('John Manager', 'john.manager@restaurant.local', '$2y$10$hh1v.ft2IyH/ygF3efa6ieDbRMCH1iUri7HFVyreqXD8VSe4Wmvg.', '+1-555-0101', 'active'),
('Sarah Admin', 'sarah.admin@restaurant.local', '$2y$10$hh1v.ft2IyH/ygF3efa6ieDbRMCH1iUri7HFVyreqXD8VSe4Wmvg.', '+1-555-0102', 'active'),
('Mike Supervisor', 'mike.supervisor@restaurant.local', '$2y$10$hh1v.ft2IyH/ygF3efa6ieDbRMCH1iUri7HFVyreqXD8VSe4Wmvg.', '+1-555-0103', 'active');

-- Password: admin123 for all (hashed with password_hash)

-- ============================================================================
-- CATEGORIES
-- ============================================================================

INSERT INTO categories (name, description, status) VALUES
('Appetizers', 'Delicious starters to begin your meal', 'active'),
('Main Courses', 'Hearty and satisfying main dishes', 'active'),
('Desserts', 'Sweet treats to end your meal', 'active'),
('Beverages', 'Drinks and refreshments', 'active'),
('Salads', 'Fresh and healthy salad options', 'active'),
('Soups', 'Warm and comforting soup selections', 'active');

-- ============================================================================
-- MENU ITEMS
-- ============================================================================

INSERT INTO menu_items (category_id, name, description, price, image_path, status) VALUES
-- Appetizers
(1, 'Chicken Wings', 'Crispy buffalo chicken wings served with ranch dip', 8.99, 'menu_1.jpg', 'active'),
(1, 'Mozzarella Sticks', 'Golden fried mozzarella sticks with marinara sauce', 6.99, 'menu_2.jpg', 'active'),
(1, 'Calamari', 'Tender squid rings fried and served with cocktail sauce', 9.99, 'menu_3.jpg', 'active'),
(1, 'Bruschetta', 'Toasted bread with tomato, garlic, and basil', 7.99, 'menu_4.jpg', 'active'),

-- Main Courses
(2, 'Grilled Chicken Breast', 'Juicy grilled chicken with seasonal vegetables', 16.99, 'menu_5.jpg', 'active'),
(2, 'Ribeye Steak', 'Premium 12oz ribeye steak with garlic butter', 28.99, 'menu_6.jpg', 'active'),
(2, 'Salmon Fillet', 'Fresh Atlantic salmon with lemon herb sauce', 22.99, 'menu_7.jpg', 'active'),
(2, 'Pasta Carbonara', 'Classic Italian pasta with creamy bacon sauce', 14.99, 'menu_8.jpg', 'active'),
(2, 'Beef Burger', 'Juicy beef burger with lettuce, tomato, and cheese', 12.99, 'menu_9.jpg', 'active'),

-- Desserts
(3, 'Chocolate Cake', 'Rich chocolate cake with chocolate frosting', 7.99, 'menu_10.jpg', 'active'),
(3, 'Tiramisu', 'Classic Italian dessert with coffee and mascarpone', 8.99, 'menu_11.jpg', 'active'),
(3, 'Cheesecake', 'New York style cheesecake with berry topping', 9.99, 'menu_12.jpg', 'active'),
(3, 'Panna Cotta', 'Creamy Italian custard dessert', 7.99, 'menu_13.jpg', 'active'),

-- Beverages
(4, 'Coca Cola', 'Classic cold soft drink', 2.99, 'menu_14.jpg', 'active'),
(4, 'Orange Juice', 'Fresh squeezed orange juice', 3.99, 'menu_15.jpg', 'active'),
(4, 'Iced Coffee', 'Refreshing cold brew coffee with ice', 4.99, 'menu_16.jpg', 'active'),
(4, 'Red Wine', 'Premium Italian red wine glass', 8.99, 'menu_17.jpg', 'active'),

-- Salads
(5, 'Caesar Salad', 'Crispy romaine with caesar dressing and croutons', 9.99, 'menu_18.jpg', 'active'),
(5, 'Greek Salad', 'Fresh vegetables with feta cheese and olives', 10.99, 'menu_19.jpg', 'active'),

-- Soups
(6, 'Tomato Soup', 'Creamy tomato soup with basil', 6.99, 'menu_20.jpg', 'active'),
(6, 'French Onion Soup', 'Rich caramelized onion soup with melted cheese', 7.99, 'menu_21.jpg', 'active');

-- ============================================================================
-- CUSTOMERS
-- ============================================================================

INSERT INTO customers (full_name, phone, email, address) VALUES
('Alice Johnson', '+1-555-1001', 'alice.johnson@email.com', '123 Main Street, New York, NY 10001'),
('Bob Smith', '+1-555-1002', 'bob.smith@email.com', '456 Oak Avenue, Los Angeles, CA 90001'),
('Carol Davis', '+1-555-1003', 'carol.davis@email.com', '789 Pine Road, Chicago, IL 60601'),
('David Wilson', '+1-555-1004', 'david.wilson@email.com', '321 Elm Street, Houston, TX 77001'),
('Eve Martinez', '+1-555-1005', 'eve.martinez@email.com', '654 Maple Drive, Phoenix, AZ 85001'),
('Frank Brown', '+1-555-1006', 'frank.brown@email.com', '987 Cedar Lane, Philadelphia, PA 19101'),
('Grace Lee', '+1-555-1007', 'grace.lee@email.com', '147 Birch Street, San Antonio, TX 78201'),
('Henry Taylor', '+1-555-1008', 'henry.taylor@email.com', '258 Spruce Avenue, San Diego, CA 92101'),
('Ivy Anderson', '+1-555-1009', 'ivy.anderson@email.com', '369 Ash Road, Dallas, TX 75201'),
('Jack Miller', '+1-555-1010', 'jack.miller@email.com', '741 Oak Street, San Jose, CA 95101');

-- ============================================================================
-- ORDERS (Sample orders)
-- ============================================================================

INSERT INTO orders (customer_id, admin_id, total_amount, payment_status, order_status) VALUES
(1, 1, 45.97, 'paid', 'served'),
(2, 1, 52.98, 'paid', 'served'),
(3, 2, 38.97, 'paid', 'ready'),
(4, 2, 63.97, 'unpaid', 'preparing'),
(5, 1, 29.98, 'paid', 'served'),
(6, 2, 71.96, 'unpaid', 'pending'),
(7, 1, 42.97, 'paid', 'served'),
(8, 2, 58.96, 'paid', 'ready'),
(9, 1, 35.98, 'paid', 'served'),
(10, 2, 49.97, 'paid', 'served');

-- ============================================================================
-- ORDER ITEMS (Items in the orders)
-- ============================================================================

INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, line_total) VALUES
-- Order 1
(1, 1, 2, 8.99, 17.98),
(1, 11, 1, 8.99, 8.99),
(1, 15, 2, 3.99, 7.98),
(1, 3, 1, 9.99, 9.99),

-- Order 2
(2, 6, 1, 28.99, 28.99),
(2, 18, 1, 9.99, 9.99),
(2, 20, 1, 6.99, 6.99),
(2, 16, 2, 4.99, 9.98),

-- Order 3
(3, 5, 2, 16.99, 33.98),
(3, 21, 1, 7.99, 7.99),

-- Order 4
(4, 7, 1, 22.99, 22.99),
(4, 12, 1, 9.99, 9.99),
(4, 19, 1, 10.99, 10.99),
(4, 16, 1, 4.99, 4.99),
(4, 15, 2, 3.99, 7.98),

-- Order 5
(5, 9, 2, 12.99, 25.98),
(5, 20, 1, 6.99, 6.99),

-- Order 6
(6, 8, 1, 14.99, 14.99),
(6, 10, 2, 7.99, 15.98),
(6, 17, 2, 8.99, 17.98),
(6, 14, 1, 2.99, 2.99),
(6, 21, 1, 7.99, 7.99),

-- Order 7
(7, 4, 1, 7.99, 7.99),
(7, 5, 1, 16.99, 16.99),
(7, 11, 1, 8.99, 8.99),
(7, 15, 1, 3.99, 3.99),

-- Order 8
(8, 2, 2, 6.99, 13.98),
(8, 7, 1, 22.99, 22.99),
(8, 12, 1, 9.99, 9.99),
(8, 17, 2, 8.99, 17.98),

-- Order 9
(9, 3, 1, 9.99, 9.99),
(9, 18, 1, 9.99, 9.99),
(9, 20, 1, 6.99, 6.99),

-- Order 10
(10, 1, 1, 8.99, 8.99),
(10, 9, 1, 12.99, 12.99),
(10, 13, 1, 7.99, 7.99),
(10, 16, 1, 4.99, 4.99),
(10, 15, 1, 3.99, 3.99);

-- ============================================================================
-- Summary
-- ============================================================================
-- Total Admins: 3
-- Total Categories: 6
-- Total Menu Items: 21
-- Total Customers: 10
-- Total Orders: 10
-- Total Order Items: 50
-- ============================================================================
