<?php

namespace App\Services;

use Exception;

class JWTService {
    private $secretKey;
    private $expireTime;
    private $refreshExpireTime;

    public function __construct() {
        $this->secretKey = $_ENV['JWT_SECRET'];
        $this->expireTime = (int)$_ENV['JWT_EXPIRE'];
        $this->refreshExpireTime = (int)$_ENV['JWT_REFRESH_EXPIRE'];
    }

    public function generateToken($userId, $email) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'iss' => 'real-estate-api',
            'aud' => 'real-estate-app',
            'iat' => time(),
            'exp' => time() + $this->expireTime,
            'user_id' => $userId,
            'email' => $email,
            'type' => 'access'
        ]);

        $headerEncoded = $this->base64UrlEncode($header);
        $payloadEncoded = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $this->secretKey, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
    }

    public function generateRefreshToken($userId, $email) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'iss' => 'real-estate-api',
            'aud' => 'real-estate-app',
            'iat' => time(),
            'exp' => time() + $this->refreshExpireTime,
            'user_id' => $userId,
            'email' => $email,
            'type' => 'refresh'
        ]);

        $headerEncoded = $this->base64UrlEncode($header);
        $payloadEncoded = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $this->secretKey, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
    }

    public function verifyToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // Verify signature
        $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $this->secretKey, true);
        $expectedSignature = $this->base64UrlEncode($signature);

        if (!hash_equals($expectedSignature, $signatureEncoded)) {
            throw new Exception('Invalid token signature');
        }

        // Decode payload
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        
        if (!$payload) {
            throw new Exception('Invalid token payload');
        }

        // Check expiration
        if ($payload['exp'] < time()) {
            throw new Exception('Token has expired');
        }

        return $payload;
    }

    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public function extractTokenFromHeader() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader) {
            throw new Exception('Authorization header not found');
        }

        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            throw new Exception('Invalid authorization header format');
        }

        return $matches[1];
    }

    public function getCurrentUser() {
        try {
            $token = $this->extractTokenFromHeader();
            $payload = $this->verifyToken($token);
            
            if ($payload['type'] !== 'access') {
                throw new Exception('Invalid token type');
            }
            
            return [
                'user_id' => $payload['user_id'],
                'email' => $payload['email']
            ];
        } catch (Exception $e) {
            throw new Exception('Authentication failed: ' . $e->getMessage());
        }
    }

    public function refreshAccessToken($refreshToken) {
        try {
            $payload = $this->verifyToken($refreshToken);
            
            if ($payload['type'] !== 'refresh') {
                throw new Exception('Invalid refresh token');
            }
            
            // Generate new access token
            $newAccessToken = $this->generateToken($payload['user_id'], $payload['email']);
            
            return [
                'access_token' => $newAccessToken,
                'expires_in' => $this->expireTime
            ];
        } catch (Exception $e) {
            throw new Exception('Token refresh failed: ' . $e->getMessage());
        }
    }
}
