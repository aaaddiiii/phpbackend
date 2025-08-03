# PHP Real Estate Backend - Render Deployment

This PHP backend is configured for deployment on Render using Docker.

## 🚀 Quick Deploy to Render

### Method 1: Using render.yaml (Recommended)

1. **Fork/Clone this repository** to your GitHub account
2. **Connect to Render**:
   - Go to [Render Dashboard](https://dashboard.render.com/)
   - Click "New" → "Blueprint"
   - Connect your GitHub repository
   - Render will automatically detect the `render.yaml` file

3. **Configure Environment Variables**:
   - `GOOGLE_CLIENT_ID` - Your Google OAuth client ID
   - `GOOGLE_CLIENT_SECRET` - Your Google OAuth client secret
   - `CLOUDINARY_CLOUD_NAME` - (Optional) Your Cloudinary cloud name
   - `CLOUDINARY_API_KEY` - (Optional) Your Cloudinary API key
   - `CLOUDINARY_API_SECRET` - (Optional) Your Cloudinary API secret

### Method 2: Manual Setup

1. **Create Web Service**:
   - Go to Render Dashboard
   - Click "New" → "Web Service"
   - Connect your repository
   - Configure:
     - **Environment**: Docker
     - **Region**: Oregon (or your preferred region)
     - **Branch**: master
     - **Dockerfile Path**: ./Dockerfile

2. **Create Database**:
   - Click "New" → "PostgreSQL" or "MySQL"
   - Choose your preferred database
   - Note the connection details

3. **Set Environment Variables**:
   ```
   DB_HOST=<your-database-host>
   DB_NAME=real_estate_db
   DB_USER=<your-database-user>
   DB_PASS=<your-database-password>
   DB_PORT=<your-database-port>
   JWT_SECRET=<generate-a-secure-32-character-string>
   JWT_EXPIRE=3600
   JWT_REFRESH_EXPIRE=604800
   APP_DEBUG=false
   GOOGLE_CLIENT_ID=<your-google-client-id>
   GOOGLE_CLIENT_SECRET=<your-google-client-secret>
   ```

## 🛠️ Local Development with Docker

### Prerequisites
- Docker and Docker Compose installed

### Run Locally
```bash
# Clone the repository
git clone <your-repo-url>
cd phpbackendPMS

# Start the application
docker-compose up -d

# Access the application
http://localhost:8080
```

### Database Setup
The database will be automatically initialized with the schema from `database/init.sql`.

## 📝 Environment Variables

| Variable | Description | Required |
|----------|-------------|----------|
| `DB_HOST` | Database host | Yes |
| `DB_NAME` | Database name | Yes |
| `DB_USER` | Database username | Yes |
| `DB_PASS` | Database password | Yes |
| `DB_PORT` | Database port (default: 3306) | No |
| `JWT_SECRET` | Secret key for JWT tokens (32+ chars) | Yes |
| `JWT_EXPIRE` | Access token expiry in seconds | No |
| `JWT_REFRESH_EXPIRE` | Refresh token expiry in seconds | No |
| `APP_DEBUG` | Enable debug mode (true/false) | No |
| `GOOGLE_CLIENT_ID` | Google OAuth client ID | Yes* |
| `GOOGLE_CLIENT_SECRET` | Google OAuth client secret | Yes* |
| `CLOUDINARY_CLOUD_NAME` | Cloudinary cloud name | No |
| `CLOUDINARY_API_KEY` | Cloudinary API key | No |
| `CLOUDINARY_API_SECRET` | Cloudinary API secret | No |

*Required for Google authentication to work

## 🔗 API Endpoints

Once deployed, your API will be available at: `https://your-app-name.onrender.com`

### Authentication Endpoints
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/google-login` - Google OAuth login
- `GET /api/user` - Get current user
- `POST /api/refresh` - Refresh access token
- `POST /api/logout` - User logout

### Property Endpoints
- `GET /api/properties` - List all properties
- `GET /api/properties/{id}` - Get single property
- `POST /api/properties` - Create new property
- `PUT /api/properties/{id}` - Update property
- `DELETE /api/properties/{id}` - Delete property
- `POST /api/properties/{id}/images` - Upload property images

### Debug Endpoint
- `GET /debug` - System debug information (remove in production)

## 🔒 Security Notes

1. **Always use HTTPS** in production
2. **Set strong JWT secrets** (32+ characters)
3. **Disable debug mode** in production (`APP_DEBUG=false`)
4. **Use environment variables** for sensitive data
5. **Implement rate limiting** for API endpoints
6. **Validate and sanitize** all user inputs

## 📱 Frontend Integration

Update your Flutter app's API base URL to point to your Render deployment:

```dart
const String apiBaseUrl = 'https://your-app-name.onrender.com';
```

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Errors**:
   - Check database credentials in environment variables
   - Ensure database is running and accessible

2. **File Upload Issues**:
   - Check file permissions for uploads directory
   - Verify Cloudinary configuration if using cloud storage

3. **CORS Issues**:
   - Update CORS headers in the application
   - Check frontend domain is allowed

4. **Google Auth Issues**:
   - Verify Google OAuth credentials
   - Check authorized domains in Google Console

### Logs
View application logs in Render Dashboard:
- Go to your web service
- Click on "Logs" tab
- Monitor for errors and debug information
