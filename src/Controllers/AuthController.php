<?php

namespace App\Controllers;

use App\Services\JWTService;
use App\Services\GoogleAuthService;
use App\Services\ValidationService;
use App\Models\User;

class AuthController extends BaseController {
    private $jwtService;
    private $googleAuthService;
    private $validationService;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->jwtService = new JWTService();
        $this->googleAuthService = new GoogleAuthService();
        $this->validationService = new ValidationService();
        $this->userModel = new User();
    }

    public function register($params = []) {
        try {
            $data = $this->getJsonInput();
            
            // Validate input
            if (!$this->validationService->validateRegistration($data)) {
                return $this->errorResponse('Validation failed', 400, $this->validationService->getErrors());
            }

            // Check if user already exists
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser) {
                return $this->errorResponse('User already exists with this email', 409);
            }

            // Create user
            $userData = [
                'name' => $this->validationService->sanitizeString($data['name']),
                'email' => strtolower(trim($data['email'])),
                'password' => $data['password']
            ];

            $user = $this->userModel->createUser($userData);
            
            if (!$user) {
                return $this->errorResponse('Failed to create user', 500);
            }

            // Generate tokens
            $accessToken = $this->jwtService->generateToken($user['id'], $user['email']);
            $refreshToken = $this->jwtService->generateRefreshToken($user['id'], $user['email']);

            // Remove password from response
            unset($user['password']);

            return $this->successResponse('User registered successfully', [
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => (int)$_ENV['JWT_EXPIRE']
            ], 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed', 500, null, $e->getMessage());
        }
    }

    public function login($params = []) {
        try {
            $data = $this->getJsonInput();

            // Validate input
            if (!$this->validationService->validateLogin($data)) {
                return $this->errorResponse('Validation failed', 400, $this->validationService->getErrors());
            }

            // Find user
            $user = $this->userModel->findByEmail($data['email']);
            if (!$user) {
                return $this->errorResponse('Invalid credentials', 401);
            }

            // Verify password
            if (!$this->userModel->verifyPassword($data['password'], $user['password'])) {
                return $this->errorResponse('Invalid credentials', 401);
            }

            // Generate tokens
            $accessToken = $this->jwtService->generateToken($user['id'], $user['email']);
            $refreshToken = $this->jwtService->generateRefreshToken($user['id'], $user['email']);

            // Remove password from response
            unset($user['password']);

            return $this->successResponse('Login successful', [
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => (int)$_ENV['JWT_EXPIRE']
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Login failed', 500, null, $e->getMessage());
        }
    }

    public function googleLogin($params = []) {
        try {
            $data = $this->getJsonInput();
            
            if (!isset($data['id_token'])) {
                return $this->errorResponse('Google ID token is required', 400);
            }

            // Verify Google ID token
            $googleUser = $this->googleAuthService->verifyIdToken($data['id_token']);

            // Check if user exists
            $user = $this->userModel->findByGoogleId($googleUser['google_id']);
            
            if (!$user) {
                // Check if user exists with the same email
                $user = $this->userModel->findByEmail($googleUser['email']);
                
                if ($user) {
                    // Update existing user with Google ID
                    $this->userModel->updateUser($user['id'], [
                        'google_id' => $googleUser['google_id'],
                        'avatar' => $googleUser['avatar'],
                        'email_verified_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    // Create new user
                    $user = $this->userModel->createUser([
                        'name' => $googleUser['name'],
                        'email' => $googleUser['email'],
                        'google_id' => $googleUser['google_id'],
                        'avatar' => $googleUser['avatar']
                    ]);
                }
            }

            if (!$user) {
                return $this->errorResponse('Failed to authenticate with Google', 500);
            }

            // Generate tokens
            $accessToken = $this->jwtService->generateToken($user['id'], $user['email']);
            $refreshToken = $this->jwtService->generateRefreshToken($user['id'], $user['email']);

            // Remove password from response
            unset($user['password']);

            return $this->successResponse('Google login successful', [
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => (int)$_ENV['JWT_EXPIRE']
            ]);

        } catch (Exception $e) {
            return $this->errorResponse('Google login failed', 500, null, $e->getMessage());
        }
    }

    public function getUser($params = []) {
        try {
            $currentUser = $this->jwtService->getCurrentUser();
            $user = $this->userModel->find($currentUser['user_id']);

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            // Remove password from response
            unset($user['password']);

            return $this->successResponse('User retrieved successfully', ['user' => $user]);

        } catch (\Exception $e) {
            return $this->errorResponse('Authentication failed', 401, null, $e->getMessage());
        }
    }

    public function refreshToken($params = []) {
        try {
            $data = $this->getJsonInput();
            
            if (!isset($data['refresh_token'])) {
                return $this->errorResponse('Refresh token is required', 400);
            }

            $result = $this->jwtService->refreshAccessToken($data['refresh_token']);

            return $this->successResponse('Token refreshed successfully', $result);

        } catch (\Exception $e) {
            return $this->errorResponse('Token refresh failed', 401, null, $e->getMessage());
        }
    }

    public function logout($params = []) {
        try {
            // Since we're using stateless JWT, logout is mainly client-side
            // In a production app, you might want to maintain a blacklist of tokens
            
            return $this->successResponse('Logout successful', [
                'message' => 'Please remove the token from client storage'
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed', 500, null, $e->getMessage());
        }
    }

    public function debug($params = []) {
        return $this->successResponse('Debug endpoint working', [
            'server_info' => [
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                'REQUEST_URI' => $_SERVER['REQUEST_URI'],
                'HTTP_HOST' => $_SERVER['HTTP_HOST'],
                'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME']
            ],
            'environment' => [
                'DB_HOST' => $_ENV['DB_HOST'] ?? 'Not set',
                'DB_NAME' => $_ENV['DB_NAME'] ?? 'Not set',
                'JWT_SECRET' => strlen($_ENV['JWT_SECRET'] ?? '') > 0 ? 'Set' : 'Not set'
            ],
            'routes_working' => true
        ]);
    }
}
