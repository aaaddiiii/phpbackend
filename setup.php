<?php
// Web-based setup interface

// Simple environment variable loader
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

loadEnv(__DIR__ . '/.env');

$setupComplete = false;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['setup_database'])) {
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

            // Create token_blacklist table
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

            // Insert sample data if requested
            if (isset($_POST['sample_data'])) {
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
                        $userId,
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
                        $userId,
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
                    ]
                ];

                $stmt = $pdo->prepare("
                    INSERT INTO properties 
                    (user_id, title, description, price, location, address, property_type, bedrooms, bathrooms, area, status, featured, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");

                foreach ($properties as $property) {
                    $stmt->execute($property);
                }
            }

            $setupComplete = true;
            $success = 'Database setup completed successfully!';

        } catch (PDOException $e) {
            $error = 'Database setup failed: ' . $e->getMessage();
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real Estate API Setup</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .btn {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin: 10px 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }
        .form-group {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        input[type="checkbox"] {
            margin-right: 8px;
        }
        .config-info {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .config-info h3 {
            margin-top: 0;
        }
        .config-item {
            margin: 8px 0;
            font-family: monospace;
            font-size: 14px;
        }
        .next-steps {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 Real Estate API Setup</h1>
            <p>Initialize your Real Estate Property Management System</p>
        </div>
        
        <div class="content">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>Success:</strong> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="config-info">
                <h3>Current Configuration</h3>
                <div class="config-item"><strong>Database Host:</strong> <?= htmlspecialchars($_ENV['DB_HOST'] ?? 'Not set') ?></div>
                <div class="config-item"><strong>Database Name:</strong> <?= htmlspecialchars($_ENV['DB_NAME'] ?? 'Not set') ?></div>
                <div class="config-item"><strong>Database User:</strong> <?= htmlspecialchars($_ENV['DB_USER'] ?? 'Not set') ?></div>
                <div class="config-item"><strong>JWT Secret:</strong> <?= strlen($_ENV['JWT_SECRET'] ?? '') > 0 ? 'Set (' . strlen($_ENV['JWT_SECRET']) . ' characters)' : 'Not set' ?></div>
            </div>

            <?php if (!$setupComplete): ?>
                <form method="POST">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="sample_data" checked>
                            Include sample data (user: john@example.com, password: password123)
                        </label>
                    </div>
                    
                    <button type="submit" name="setup_database" class="btn btn-success">
                        🚀 Setup Database
                    </button>
                </form>
            <?php else: ?>
                <div class="next-steps">
                    <h3>🎉 Setup Complete!</h3>
                    <p>Your database has been set up successfully. Here's what you can do next:</p>
                    <ul>
                        <li>Test the API endpoints using the API tester</li>
                        <li>View the API documentation</li>
                        <li>Start building your Flutter app</li>
                    </ul>
                </div>

                <div style="text-align: center; margin: 30px 0;">
                    <a href="test.php" class="btn">🧪 Test API</a>
                    <a href="docs.php" class="btn">📚 Documentation</a>
                    <a href="index.php" class="btn">🚀 API Endpoint</a>
                </div>
            <?php endif; ?>

            <div class="config-info">
                <h3>API Endpoints</h3>
                <div class="config-item"><strong>Base URL:</strong> http://<?= $_SERVER['HTTP_HOST'] ?>/phpbackendPMS</div>
                <div class="config-item"><strong>Authentication:</strong> JWT Bearer Token</div>
                <div class="config-item"><strong>Content-Type:</strong> application/json</div>
            </div>
        </div>
    </div>
</body>
</html>
