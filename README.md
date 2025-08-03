# PHP Real Estate Backend API

A comprehensive PHP backend for a real estate listing platform with JWT authentication, Google Sign-In integration, property CRUD operations, and image upload capabilities.

## 🚀 Quick Start

### Production Deployment (Render)
See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment instructions.

### Local Development

1. **Setup Environment**:
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

2. **Using Docker (Recommended)**:
   ```bash
   docker-compose up -d
   # Access: http://localhost:8080
   ```

3. **Manual Setup**:
   - Configure web server (Apache/Nginx)
   - Install PHP 8.0+ with MySQL extension
   - Import database schema from `database/init.sql`
   - Configure virtual host to point to project root

## 📋 Features

- ✅ **JWT Authentication** with refresh tokens
- ✅ **Google OAuth Integration** 
- ✅ **Property CRUD Operations**
- ✅ **Image Upload** (Cloudinary integration)
- ✅ **RESTful API Design**
- ✅ **Docker Support**
- ✅ **Database Migrations**
- ✅ **Input Validation**
- ✅ **Error Handling**
- ✅ **CORS Support**

## 🛠️ Tech Stack

- **PHP 8.2** with custom MVC framework
- **MySQL** for database
- **Apache** web server
- **Docker** for containerization
- **Custom JWT** implementation
- **PDO** for database operations

## API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login with email/password
- `POST /api/google-login` - Login with Google OAuth
- `GET /api/user` - Get current user info
- `POST /api/logout` - Logout user

### Properties
- `GET /api/properties` - List properties (with pagination)
- `GET /api/properties/{id}` - Get property details
- `POST /api/properties` - Create new property
- `PUT /api/properties/{id}` - Update property
- `DELETE /api/properties/{id}` - Delete property
- `POST /api/properties/{id}/images` - Upload property images

## Environment Variables

```
DB_HOST=localhost
DB_NAME=real_estate_db
DB_USER=root
DB_PASS=

JWT_SECRET=your-secret-key
JWT_EXPIRE=3600

GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret

CLOUDINARY_CLOUD_NAME=your-cloud-name
CLOUDINARY_API_KEY=your-api-key
CLOUDINARY_API_SECRET=your-api-secret
```
