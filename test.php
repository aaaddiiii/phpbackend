<?php

// API Testing Interface
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title    <script>
        // Detect the correct base URL automatically
        const currentUrl = window.location.href;
        const baseUrl = currentUrl.substring(0, currentUrl.lastIndexOf('/'));
        
        console.log('Base URL detected:', baseUrl);
        
        // Update token display when token is entered
        document.getElementById('authToken').addEventListener('input', function() { Estate API Tester</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        select, input, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        select:focus, input:focus, textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        textarea {
            height: 120px;
            resize: vertical;
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
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .response {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        .error {
            border-left-color: #dc3545;
        }
        .code {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            overflow-x: auto;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .auth-section {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .token-display {
            background: #d1ecf1;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            word-break: break-all;
        }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 Real Estate API Tester</h1>
            <p>Test your Real Estate Property Management API endpoints</p>
        </div>
        
        <div class="content">
            <div class="auth-section">
                <h3>Authentication Token</h3>
                <div class="form-group">
                    <label for="authToken">JWT Token:</label>
                    <input type="text" id="authToken" placeholder="Paste your JWT token here">
                </div>
                <div id="tokenDisplay" class="token-display" style="display:none;"></div>
            </div>

            <div class="grid">
                <div>
                    <div class="form-group">
                        <label for="endpoint">Endpoint:</label>
                        <select id="endpoint">
                            <optgroup label="Authentication">
                                <option value="POST:/api/register">POST /api/register</option>
                                <option value="POST:/api/login">POST /api/login</option>
                                <option value="POST:/api/google-login">POST /api/google-login</option>
                                <option value="GET:/api/user">GET /api/user</option>
                                <option value="POST:/api/refresh">POST /api/refresh</option>
                                <option value="POST:/api/logout">POST /api/logout</option>
                            </optgroup>
                            <optgroup label="Properties">
                                <option value="GET:/api/properties">GET /api/properties</option>
                                <option value="GET:/api/properties/{id}">GET /api/properties/{id}</option>
                                <option value="POST:/api/properties">POST /api/properties</option>
                                <option value="PUT:/api/properties/{id}">PUT /api/properties/{id}</option>
                                <option value="DELETE:/api/properties/{id}">DELETE /api/properties/{id}</option>
                                <option value="POST:/api/properties/{id}/images">POST /api/properties/{id}/images</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="resourceId">Resource ID (if needed):</label>
                        <input type="text" id="resourceId" placeholder="e.g., 1, 2, 3...">
                    </div>

                    <div class="form-group">
                        <label for="queryParams">Query Parameters:</label>
                        <input type="text" id="queryParams" placeholder="e.g., page=1&limit=10&location=New York">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label for="requestBody">Request Body (JSON):</label>
                        <textarea id="requestBody" placeholder='{"key": "value"}'></textarea>
                    </div>

                    <div class="form-group">
                        <button class="btn" onclick="sendRequest()">Send Request</button>
                        <button class="btn" onclick="loadSampleData()" style="background: #6c757d; margin-left: 10px;">Load Sample</button>
                    </div>
                </div>
            </div>

            <div id="response" class="response" style="display:none;">
                <h4>Response:</h4>
                <div id="responseCode" class="code"></div>
            </div>
        </div>
    </div>

    <script>
        const baseUrl = window.location.origin + '/phpbackendPMS/index.php';
        
        // Update token display when token is entered
        document.getElementById('authToken').addEventListener('input', function() {
            const token = this.value;
            const display = document.getElementById('tokenDisplay');
            
            if (token) {
                display.style.display = 'block';
                display.textContent = token;
            } else {
                display.style.display = 'none';
            }
        });

        function loadSampleData() {
            const endpoint = document.getElementById('endpoint').value;
            const bodyTextarea = document.getElementById('requestBody');
            
            const samples = {
                'POST:/api/register': JSON.stringify({
                    "name": "Test User",
                    "email": "test@example.com", 
                    "password": "SecurePassword123"
                }, null, 2),
                
                'POST:/api/login': JSON.stringify({
                    "email": "john@example.com",
                    "password": "password123"
                }, null, 2),
                
                'POST:/api/google-login': JSON.stringify({
                    "id_token": "GOOGLE_ID_TOKEN_HERE"
                }, null, 2),
                
                'POST:/api/refresh': JSON.stringify({
                    "refresh_token": "YOUR_REFRESH_TOKEN_HERE"
                }, null, 2),
                
                'POST:/api/properties': JSON.stringify({
                    "title": "Modern Apartment in Downtown",
                    "description": "Beautiful 2-bedroom apartment with city views and modern amenities.",
                    "price": 850000.00,
                    "location": "Downtown, New York",
                    "address": "123 Main Street, New York, NY 10001",
                    "property_type": "residential",
                    "bedrooms": 2,
                    "bathrooms": 2.0,
                    "area": 1200.50,
                    "status": "available",
                    "featured": false
                }, null, 2),
                
                'PUT:/api/properties/{id}': JSON.stringify({
                    "title": "Updated Property Title",
                    "description": "Updated description for the property.",
                    "price": 900000.00,
                    "status": "pending"
                }, null, 2)
            };
            
            if (samples[endpoint]) {
                bodyTextarea.value = samples[endpoint];
            } else {
                bodyTextarea.value = '{}';
            }
            
            // Set sample query params for GET requests
            if (endpoint === 'GET:/api/properties') {
                document.getElementById('queryParams').value = 'page=1&limit=10&property_type=residential';
            }
            
            // Set sample resource ID
            if (endpoint.includes('{id}')) {
                document.getElementById('resourceId').value = '1';
            }
        }

        async function sendRequest() {
            const endpointValue = document.getElementById('endpoint').value;
            const [method, path] = endpointValue.split(':');
            const resourceId = document.getElementById('resourceId').value;
            const queryParams = document.getElementById('queryParams').value;
            const requestBody = document.getElementById('requestBody').value;
            const authToken = document.getElementById('authToken').value;
            
            let url = baseUrl + path.replace('{id}', resourceId);
            
            if (queryParams) {
                url += '?' + queryParams;
            }
            
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                }
            };
            
            if (authToken) {
                options.headers['Authorization'] = 'Bearer ' + authToken;
            }
            
            if (method !== 'GET' && requestBody) {
                options.body = requestBody;
            }
            
            try {
                const response = await fetch(url, options);
                const responseData = await response.text();
                
                const responseDiv = document.getElementById('response');
                const responseCode = document.getElementById('responseCode');
                
                responseDiv.style.display = 'block';
                responseDiv.className = 'response ' + (response.ok ? '' : 'error');
                
                let formattedResponse = `Status: ${response.status} ${response.statusText}\n\n`;
                
                try {
                    const jsonData = JSON.parse(responseData);
                    formattedResponse += JSON.stringify(jsonData, null, 2);
                } catch (e) {
                    formattedResponse += responseData;
                }
                
                responseCode.textContent = formattedResponse;
                
                // Auto-extract token from login responses
                if (response.ok && (endpointValue.includes('login') || endpointValue.includes('register'))) {
                    try {
                        const jsonData = JSON.parse(responseData);
                        if (jsonData.data && jsonData.data.access_token) {
                            document.getElementById('authToken').value = jsonData.data.access_token;
                            document.getElementById('authToken').dispatchEvent(new Event('input'));
                        }
                    } catch (e) {
                        // Ignore parsing errors
                    }
                }
                
            } catch (error) {
                const responseDiv = document.getElementById('response');
                const responseCode = document.getElementById('responseCode');
                
                responseDiv.style.display = 'block';
                responseDiv.className = 'response error';
                responseCode.textContent = 'Error: ' + error.message;
            }
        }
        
        // Load default sample on page load
        window.addEventListener('load', function() {
            loadSampleData();
        });
    </script>
</body>
</html>
