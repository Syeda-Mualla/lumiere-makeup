-- LUMIÈRE Database Schema
-- Luxury Makeup Brand Website

CREATE DATABASE IF NOT EXISTS lumiere;
USE lumiere;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    bio TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category ENUM('Lips', 'Eyes', 'Face', 'Sets') NOT NULL,
    image VARCHAR(255) NOT NULL,
    stock INT DEFAULT 0,
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_name VARCHAR(100),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_zip VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Blog posts table
CREATE TABLE blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Comments table (for blog and community)
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    post_type ENUM('blog', 'community') NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Community posts table
CREATE TABLE community (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    caption TEXT,
    likes INT DEFAULT 0,
    reported TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Community likes table (to track who liked what)
CREATE TABLE community_likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    community_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (user_id, community_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (community_id) REFERENCES community(id) ON DELETE CASCADE
);

-- Newsletter subscribers table
CREATE TABLE newsletter (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@lumiere.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample products
INSERT INTO products (name, description, price, category, image, stock, featured) VALUES
('Velvet Rouge Lipstick', 'A luxurious matte lipstick with long-lasting formula that glides on smoothly for a flawless finish.', 45.00, 'Lips', 'velvet-rouge.jpg', 50, 1),
('Golden Hour Eyeshadow Palette', 'A stunning palette featuring 12 warm-toned shades perfect for creating day-to-night looks.', 68.00, 'Eyes', 'golden-hour.jpg', 30, 1),
('Luminous Silk Foundation', 'A lightweight, buildable foundation that delivers a natural, radiant finish all day long.', 52.00, 'Face', 'luminous-silk.jpg', 40, 1),
('Complete Glam Set', 'Our bestselling collection featuring essentials for a complete luxury makeup routine.', 189.00, 'Sets', 'complete-glam.jpg', 20, 1),
('Midnight Kiss Lip Gloss', 'A high-shine lip gloss with subtle shimmer for the perfect pout.', 32.00, 'Lips', 'midnight-kiss.jpg', 60, 0),
('Smoky Obsession Eye Pencil', 'Intensely pigmented eye pencil for dramatic smoky eyes.', 28.00, 'Eyes', 'smoky-obsession.jpg', 45, 0),
('Flawless Finish Powder', 'Ultra-fine setting powder for a photo-ready finish.', 42.00, 'Face', 'flawless-finish.jpg', 35, 0),
('Starter Beauty Set', 'Perfect introduction to LUMIÈRE with our mini essentials.', 99.00, 'Sets', 'starter-set.jpg', 25, 0);

-- Insert sample blog posts
INSERT INTO blog_posts (title, content, excerpt, image) VALUES
('The Art of the Perfect Red Lip', 'Red lipstick has been a symbol of confidence and glamour for decades. In this guide, we explore the secrets to achieving the perfect red lip that stays flawless all day...', 'Discover the secrets to achieving a perfect red lip that stays flawless all day long.', 'red-lip-blog.jpg'),
('Spring 2024 Beauty Trends', 'This spring, beauty is all about embracing natural radiance with a touch of luxury. From dewy skin to soft, romantic eyes, here are the trends you need to know...', 'Explore the hottest beauty trends this season and how to achieve them.', 'spring-trends.jpg'),
('Skincare Before Makeup: Essential Steps', 'The key to flawless makeup application starts with proper skincare. Learn the essential steps to prepare your skin for a perfect makeup base...', 'Learn the essential skincare steps for a flawless makeup base.', 'skincare-blog.jpg');
