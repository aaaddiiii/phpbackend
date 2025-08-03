# Real Estate Property Management System API

A comprehensive PHP backend API for a real estate listing platform with JWT authentication, Google Sign-In, and Cloudinary integration.

## Features

- JWT-based authentication
- Google OAuth2 integration
- Property CRUD operations
- Image upload via Cloudinary
- User management
- RESTful API design
- Input validation and security

## Installation

1. Install dependencies:
```bash
composer install
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Configure your `.env` file with database and API credentials

4. Set up database:
```bash
php setup/database.php
```

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
