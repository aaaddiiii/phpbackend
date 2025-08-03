<?php

// Database setup script

// Simple environment variable loader (same as in index.php)
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue; // Skip comments
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Load environment variables
loadEnv(__DIR__ . '/../.env');

try {
    // Connect to MySQL server
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$_ENV['DB_NAME']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '{$_ENV['DB_NAME']}' created or already exists.\n";

    // Use the database
    $pdo->exec("USE `{$_ENV['DB_NAME']}`");

    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NULL,
            google_id VARCHAR(255) NULL UNIQUE,
            avatar TEXT NULL,
            email_verified_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_google_id (google_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Users table created successfully.\n";

    // Create properties table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS properties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            price DECIMAL(15,2) NOT NULL,
            location VARCHAR(255) NOT NULL,
            address TEXT NULL,
            property_type ENUM('residential', 'commercial', 'industrial', 'land') DEFAULT 'residential',
            bedrooms INT DEFAULT 0,
            bathrooms DECIMAL(3,1) DEFAULT 0,
            area DECIMAL(10,2) NULL,
            status ENUM('available', 'sold', 'rented', 'pending') DEFAULT 'available',
            featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_property_type (property_type),
            INDEX idx_status (status),
            INDEX idx_location (location),
            INDEX idx_price (price),
            INDEX idx_featured (featured),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Properties table created successfully.\n";

    // Create property_images table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS property_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            property_id INT NOT NULL,
            image_url TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
            INDEX idx_property_id (property_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Property images table created successfully.\n";

    // Create token_blacklist table (optional, for logout functionality)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS token_blacklist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            token_hash VARCHAR(255) NOT NULL UNIQUE,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token_hash (token_hash),
            INDEX idx_expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Token blacklist table created successfully.\n";

    // Insert sample data (optional)
    if (isset($argv[1]) && $argv[1] === '--with-sample-data') {
        echo "Inserting sample data...\n";
        
        // Sample user
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO users (name, email, password, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([
            'John Doe',
            'john@example.com',
            password_hash('password123', PASSWORD_DEFAULT)
        ]);

        $userId = $pdo->lastInsertId() ?: 1;

        // Sample properties
        $properties = [
            [
                'Modern Apartment in Downtown',
                'Beautiful 2-bedroom apartment with city views and modern amenities.',
                850000.00,
                'Downtown, New York',
                '123 Main Street, New York, NY 10001',
                'residential',
                2,
                2.0,
                1200.50,
                'available',
                1
            ],
            [
                'Spacious Family Home',
                'Large 4-bedroom house perfect for families with a big garden.',
                1200000.00,
                'Suburbs, New York',
                '456 Oak Avenue, Suburbia, NY 10002',
                'residential',
                4,
                3.5,
                2500.00,
                'available',
                0
            ],
            [
                'Commercial Office Space',
                'Prime office location in business district with parking.',
                2500000.00,
                'Business District, New York',
                '789 Business Blvd, Business District, NY 10003',
                'commercial',
                0,
                4.0,
                5000.00,
                'available',
                1
            ]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO properties 
            (user_id, title, description, price, location, address, property_type, bedrooms, bathrooms, area, status, featured, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        foreach ($properties as $property) {
            $stmt->execute(array_merge([$userId], $property));
        }

        echo "Sample data inserted successfully.\n";
    }

    echo "\nDatabase setup completed successfully!\n";
    echo "You can now use the API endpoints.\n";

} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
