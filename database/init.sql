-- Real Estate Database Schema
CREATE DATABASE IF NOT EXISTS real_estate_db;
USE real_estate_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255),
    google_id VARCHAR(255) UNIQUE,
    avatar TEXT,
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_google_id (google_id)
);

-- Properties table
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(15,2) NOT NULL,
    property_type ENUM('house', 'apartment', 'condo', 'townhouse', 'land', 'commercial') NOT NULL,
    status ENUM('available', 'pending', 'sold', 'rented') DEFAULT 'available',
    bedrooms INT,
    bathrooms INT,
    area DECIMAL(10,2),
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20),
    country VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    features JSON,
    amenities JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_property_type (property_type),
    INDEX idx_status (status),
    INDEX idx_price (price),
    INDEX idx_location (city, state),
    INDEX idx_created_at (created_at)
);

-- Property images table
CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_url TEXT NOT NULL,
    alt_text VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property_id (property_id),
    INDEX idx_primary (is_primary),
    INDEX idx_sort_order (sort_order)
);

-- Token blacklist table (for JWT token management)
CREATE TABLE IF NOT EXISTS token_blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_hash VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires_at (expires_at)
);

-- Insert sample data (optional)
INSERT IGNORE INTO users (name, email, password, created_at) VALUES 
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW());

-- Sample property data
INSERT IGNORE INTO properties (user_id, title, description, price, property_type, bedrooms, bathrooms, area, address, city, state, country, created_at) VALUES 
(1, 'Beautiful Family Home', 'A stunning 4-bedroom house in a quiet neighborhood', 450000.00, 'house', 4, 3, 2500.00, '123 Main Street', 'Los Angeles', 'California', 'USA', NOW()),
(1, 'Modern Downtown Apartment', 'Luxury apartment with city views', 320000.00, 'apartment', 2, 2, 1200.00, '456 City Center Blvd', 'New York', 'New York', 'USA', NOW());
