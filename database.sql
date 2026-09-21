CREATE DATABASE IF NOT EXISTS wti;
USE wti;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    profile_picture VARCHAR(255),
    address TEXT NOT NULL,
    phone VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_type ENUM('liquid','solid') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category_id INT,
    vendor_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    availability INT NOT NULL DEFAULT 0,
    description TEXT,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (medicine_id) REFERENCES medicines(id)
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (medicine_id) REFERENCES medicines(id)
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Sample Categories
INSERT INTO categories (name, category_type)
SELECT 'Aspirin genre', 'solid'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Aspirin genre');

INSERT INTO categories (name, category_type)
SELECT 'Paracetamol genre', 'solid'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Paracetamol genre');

INSERT INTO categories (name, category_type)
SELECT 'Cough Syrup genre', 'liquid'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = 'Cough Syrup genre');

-- Sample Medicines
INSERT INTO medicines (name, category_id, vendor_name, price, availability, description)
SELECT 'Napa 500', id, 'Beximco Pharma', 12.00, 120, 'Paracetamol tablet'
FROM categories WHERE name = 'Paracetamol genre'
AND NOT EXISTS (SELECT 1 FROM medicines WHERE name = 'Napa 500');

INSERT INTO medicines (name, category_id, vendor_name, price, availability, description)
SELECT 'Aspirin Protect', id, 'Bayer', 80.00, 50, 'Aspirin tablet'
FROM categories WHERE name = 'Aspirin genre'
AND NOT EXISTS (SELECT 1 FROM medicines WHERE name = 'Aspirin Protect');

INSERT INTO medicines (name, category_id, vendor_name, price, availability, description)
SELECT 'Dexo Cough Syrup', id, 'Square Pharma', 95.00, 35, 'Liquid cough syrup'
FROM categories WHERE name = 'Cough Syrup genre'
AND NOT EXISTS (SELECT 1 FROM medicines WHERE name = 'Dexo Cough Syrup');

-- Default administrator for local/course use.
INSERT INTO users (name, email, password_hash, role, address, phone, profile_picture)
SELECT 'Site Admin', 'asadbinjafor@gmail.com',
       '$2y$10$wa2k1wEjstjfm.3BvkxdUuFh8mOrGhAMvxQuHUmTz5TVkDB2Ebx6S',
       'admin', 'Dhaka', '00000000000', ''
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE LOWER(email) = LOWER('asadbinjafor@gmail.com')
);

UPDATE users
SET role = 'admin',
    password_hash = '$2y$10$wa2k1wEjstjfm.3BvkxdUuFh8mOrGhAMvxQuHUmTz5TVkDB2Ebx6S'
WHERE LOWER(email) = LOWER('asadbinjafor@gmail.com');
