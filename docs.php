<?php

// API Documentation and Testing Interface
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real Estate API Documentation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .endpoint {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            margin-right: 10px;
        }
        .get { background: #28a745; color: white; }
        .post { background: #007bff; color: white; }
        .put { background: #ffc107; color: black; }
        .delete { background: #dc3545; color: white; }
        .code {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .section {
            margin: 30px 0;
        }
        .auth-required {
            color: #dc3545;
            font-size: 12px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .note {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏠 Real Estate Property Management API</h1>
        
        <div class="note">
            <strong>Base URL:</strong> <code>http://localhost/phpbackendPMS</code><br>
            <strong>API Version:</strong> 1.0<br>
            <strong>Content-Type:</strong> application/json
        </div>

        <div class="section">
            <h2>Authentication</h2>
            <p>This API uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header:</p>
            <div class="code">Authorization: Bearer YOUR_JWT_TOKEN</div>
        </div>

        <div class="section">
            <h2>Authentication Endpoints</h2>
            
            <div class="endpoint">
                <span class="method post">POST</span>
                <strong>/api/register</strong>
                <p>Register a new user with email and password.</p>
                <div class="code">{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePassword123"
}</div>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <strong>/api/login</strong>
                <p>Login with email and password.</p>
                <div class="code">{
  "email": "john@example.com",
  "password": "SecurePassword123"
}</div>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <strong>/api/google-login</strong>
                <p>Login with Google ID token.</p>
                <div class="code">{
  "id_token": "GOOGLE_ID_TOKEN_HERE"
}</div>
            </div>

            <div class="endpoint">
                <span class="method get">GET</span>
                <strong>/api/user</strong>
                <span class="auth-required">🔒 Auth Required</span>
                <p>Get current user information.</p>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <strong>/api/refresh</strong>
                <p>Refresh access token using refresh token.</p>
                <div class="code">{
  "refresh_token": "YOUR_REFRESH_TOKEN"
}</div>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <strong>/api/logout</strong>
                <span class="auth-required">🔒 Auth Required</span>
                <p>Logout current user.</p>
            </div>
        </div>

        <div class="section">
            <h2>Property Endpoints</h2>

            <div class="endpoint">
                <span class="method get">GET</span>
                <strong>/api/properties</strong>
                <p>Get all properties with pagination and filtering.</p>
                <h4>Query Parameters:</h4>
                <table>
                    <tr><th>Parameter</th><th>Type</th><th>Description</th></tr>
                    <tr><td>page</td><td>integer</td><td>Page number (default: 1)</td></tr>
                    <tr><td>limit</td><td>integer</td><td>Items per page (default: 10, max: 50)</td></tr>
                    <tr><td>property_type</td><td>string</td><td>residential, commercial, industrial, land</td></tr>
                    <tr><td>min_price</td><td>number</td><td>Minimum price filter</td></tr>
                    <tr><td>max_price</td><td>number</td><td>Maximum price filter</td></tr>
                    <tr><td>location</td><td>string</td><td>Location search (partial match)</td></tr>
                    <tr><td>status</td><td>string</td><td>available, sold, rented, pending</td></tr>
                    <tr><td>user_properties</td><td>boolean</td><td>Get only current user's properties</td></tr>
                </table>
            </div>

            <div class="endpoint">
                <span class="method get">GET</span>
                <strong>/api/properties/{id}</strong>
                <p>Get a specific property by ID.</p>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <strong>/api/properties</strong>
                <span class="auth-required">🔒 Auth Required</span>
                <p>Create a new property.</p>
                <div class="code">{
  "title": "Modern Apartment in Downtown",
  "description": "Beautiful 2-bedroom apartment with city views.",
  "price": 850000.00,
  "location": "Downtown, New York",
  "address": "123 Main Street, New York, NY 10001",
  "property_type": "residential",
  "bedrooms": 2,
  "bathrooms": 2.0,
  "area": 1200.50,
  "status": "available",
  "featured": false
}</div>
            </div>

            <div class="endpoint">
                <span class="method put">PUT</span>
                <strong>/api/properties/{id}</strong>
                <span class="auth-required">🔒 Auth Required</span>
                <p>Update an existing property (only owner can update).</p>
            </div>

            <div class="endpoint">
                <span class="method delete">DELETE</span>
                <strong>/api/properties/{id}</strong>
                <span class="auth-required">🔒 Auth Required</span>
                <p>Delete a property (only owner can delete).</p>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <strong>/api/properties/{id}/images</strong>
                <span class="auth-required">🔒 Auth Required</span>
                <p>Upload images for a property. Use multipart/form-data with 'images[]' field.</p>
                <div class="note">
                    <strong>File Requirements:</strong><br>
                    • Max file size: 5MB per image<br>
                    • Supported formats: JPEG, PNG, GIF, WebP<br>
                    • Multiple files supported
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Response Format</h2>
            <h3>Success Response:</h3>
            <div class="code">{
  "success": true,
  "message": "Operation completed successfully",
  "data": { ... },
  "timestamp": "2024-01-01T12:00:00+00:00"
}</div>

            <h3>Error Response:</h3>
            <div class="code">{
  "success": false,
  "message": "Error description",
  "errors": { ... },
  "timestamp": "2024-01-01T12:00:00+00:00"
}</div>

            <h3>Paginated Response:</h3>
            <div class="code">{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 50,
    "total_pages": 5,
    "has_next": true,
    "has_prev": false
  },
  "timestamp": "2024-01-01T12:00:00+00:00"
}</div>
        </div>

        <div class="section">
            <h2>Setup Instructions</h2>
            <ol>
                <li>Copy <code>.env.example</code> to <code>.env</code> and configure your settings</li>
                <li>Install PHP dependencies: <code>composer install</code></li>
                <li>Set up the database: <code>php setup/database.php</code></li>
                <li>Configure your web server to point to this directory</li>
                <li>Start making API requests!</li>
            </ol>
        </div>

        <div class="section">
            <h2>Environment Variables</h2>
            <table>
                <tr><th>Variable</th><th>Description</th><th>Required</th></tr>
                <tr><td>DB_HOST</td><td>Database host</td><td>Yes</td></tr>
                <tr><td>DB_NAME</td><td>Database name</td><td>Yes</td></tr>
                <tr><td>DB_USER</td><td>Database username</td><td>Yes</td></tr>
                <tr><td>DB_PASS</td><td>Database password</td><td>Yes</td></tr>
                <tr><td>JWT_SECRET</td><td>JWT signing secret</td><td>Yes</td></tr>
                <tr><td>GOOGLE_CLIENT_ID</td><td>Google OAuth client ID</td><td>For Google login</td></tr>
                <tr><td>GOOGLE_CLIENT_SECRET</td><td>Google OAuth client secret</td><td>For Google login</td></tr>
                <tr><td>CLOUDINARY_CLOUD_NAME</td><td>Cloudinary cloud name</td><td>For image upload</td></tr>
                <tr><td>CLOUDINARY_API_KEY</td><td>Cloudinary API key</td><td>For image upload</td></tr>
                <tr><td>CLOUDINARY_API_SECRET</td><td>Cloudinary API secret</td><td>For image upload</td></tr>
            </table>
        </div>
    </div>
</body>
</html>
