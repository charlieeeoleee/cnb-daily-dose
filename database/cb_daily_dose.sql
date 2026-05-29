CREATE DATABASE IF NOT EXISTS cb_daily_dose CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cb_daily_dose;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS pos_transaction_items;
DROP TABLE IF EXISTS pos_transactions;
DROP TABLE IF EXISTS online_order_items;
DROP TABLE IF EXISTS online_orders;
DROP TABLE IF EXISTS recipes;
DROP TABLE IF EXISTS inventory_items;
DROP TABLE IF EXISTS addons;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier', 'staff') NOT NULL DEFAULT 'cashier',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category ENUM('Bottled Coffee', 'Snacks', 'Bundles') NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size VARCHAR(80) NOT NULL,
    flavor VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE addons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE inventory_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category VARCHAR(80) NOT NULL,
    unit VARCHAR(30) NOT NULL,
    current_stock DECIMAL(10,2) NOT NULL DEFAULT 0,
    low_stock_level DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    variant_id INT NOT NULL,
    inventory_item_id INT NOT NULL,
    quantity_required DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE CASCADE
);

CREATE TABLE online_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(40) NOT NULL UNIQUE,
    customer_name VARCHAR(120) NOT NULL,
    contact_number VARCHAR(40) NOT NULL,
    order_type ENUM('Pickup', 'Delivery') NOT NULL,
    delivery_address TEXT,
    payment_method ENUM('Cash', 'GCash', 'Bank Transfer') NOT NULL,
    payment_reference VARCHAR(120),
    status ENUM('Pending', 'Accepted', 'Preparing', 'Ready', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    subtotal DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    inventory_deducted TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE online_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    online_order_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    variant_label VARCHAR(180) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (online_order_id) REFERENCES online_orders(id) ON DELETE CASCADE
);

CREATE TABLE pos_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(40) NOT NULL UNIQUE,
    cashier_id INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Cash', 'GCash', 'Card', 'Mixed') NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    change_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cashier_id) REFERENCES users(id)
);

CREATE TABLE pos_transaction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pos_transaction_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    variant_label VARCHAR(180) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pos_transaction_id) REFERENCES pos_transactions(id) ON DELETE CASCADE
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_type ENUM('online_order', 'pos') NOT NULL,
    source_id INT NOT NULL,
    payment_method VARCHAR(40) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2),
    reference_number VARCHAR(120),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_category VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_item_id INT NOT NULL,
    movement_type ENUM('add', 'deduct', 'adjust') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    source_type VARCHAR(40),
    source_id INT,
    notes TEXT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(120) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO users (full_name, email, password_hash, role) VALUES
('Owner Admin', 'admin@cbdailydose.local', '$2y$10$kFAgMeYJoUqIk2nVPZ382OUPqGEnVsvcVHPV1Ht.r6xDoZjY81w5y', 'admin'),
('Cashier One', 'cashier@cbdailydose.local', '$2y$10$kFAgMeYJoUqIk2nVPZ382OUPqGEnVsvcVHPV1Ht.r6xDoZjY81w5y', 'cashier');

INSERT INTO products (name, category, description, image_path) VALUES
('Classic Cold Brew', 'Bottled Coffee', 'Smooth homemade cold brew with a balanced coffee finish.', '../assets/img/coffee-classic.svg'),
('Caramel Coffee', 'Bottled Coffee', 'Creamy bottled iced coffee with caramel syrup.', '../assets/img/coffee-caramel.svg'),
('Mocha Coffee', 'Bottled Coffee', 'Chocolatey iced coffee for a richer daily dose.', '../assets/img/coffee-mocha.svg'),
('Banana Loaf Slice', 'Snacks', 'Soft homemade banana loaf slice, packed fresh.', '../assets/img/snack-loaf.svg'),
('Coffee Snack Pair', 'Bundles', 'One bottled coffee with one snack for pickup or delivery.', '../assets/img/bundle.svg');

INSERT INTO product_variants (product_id, size, flavor, price) VALUES
(1, '250ml', 'Classic', 79.00),
(1, '500ml', 'Classic', 139.00),
(2, '250ml', 'Caramel', 89.00),
(2, '500ml', 'Caramel', 149.00),
(3, '250ml', 'Mocha', 89.00),
(4, '1 slice', 'Banana', 45.00),
(5, 'Combo', 'Caramel + Banana', 125.00);

INSERT INTO addons (name, price) VALUES
('Extra caramel drizzle', 10.00),
('Extra coffee shot', 20.00);

INSERT INTO inventory_items (name, category, unit, current_stock, low_stock_level) VALUES
('Coffee', 'Raw Materials', 'ml', 10000, 1500),
('Milk', 'Raw Materials', 'ml', 8000, 1200),
('Condensed Milk', 'Raw Materials', 'ml', 4000, 700),
('Flavored Syrup', 'Raw Materials', 'ml', 2500, 500),
('250ml Bottle', 'Packaging', 'pcs', 80, 15),
('500ml Bottle', 'Packaging', 'pcs', 40, 10),
('Label', 'Packaging', 'pcs', 120, 20),
('Cups', 'Packaging', 'pcs', 50, 10),
('Lids', 'Packaging', 'pcs', 50, 10),
('Snacks', 'Finished Goods', 'pcs', 40, 8),
('Ice', 'Raw Materials', 'g', 8000, 1000);

INSERT INTO recipes (variant_id, inventory_item_id, quantity_required) VALUES
(1, 1, 100), (1, 2, 80), (1, 3, 20), (1, 5, 1), (1, 7, 1),
(2, 1, 200), (2, 2, 160), (2, 3, 40), (2, 6, 1), (2, 7, 1),
(3, 1, 100), (3, 2, 80), (3, 4, 20), (3, 5, 1), (3, 7, 1),
(4, 1, 200), (4, 2, 160), (4, 4, 40), (4, 6, 1), (4, 7, 1),
(5, 1, 100), (5, 2, 80), (5, 4, 20), (5, 5, 1), (5, 7, 1),
(6, 10, 1),
(7, 1, 100), (7, 2, 80), (7, 4, 20), (7, 5, 1), (7, 7, 1), (7, 10, 1);
