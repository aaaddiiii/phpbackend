<?php

namespace App\Services;

use Exception;

class GoogleAuthService {
    public function __construct() {
        // Simplified version - in production you would use the Google Client Library
    }

    public function getAuthUrl() {
        // For now, return a placeholder
        return 'https://accounts.google.com/oauth/authorize?client_id=' . $_ENV['GOOGLE_CLIENT_ID'];
    }

    public function handleCallback($code, $state = null) {
        // Simplified version - in production you would exchange the code for tokens
        throw new Exception('Google OAuth callback handling requires the Google Client Library. Please use verifyIdToken method instead.');
    }

    public function verifyIdToken($idToken) {
        // Simplified version for development - in production you should verify the signature
        try {
            $parts = explode('.', $idToken);
            if (count($parts) !== 3) {
                throw new Exception('Invalid ID token format');
            }

            $payload = json_decode($this->base64UrlDecode($parts[1]), true);
            
            if (!$payload) {
                throw new Exception('Invalid ID token payload');
            }

            // Basic validation - in production, verify signature and claims properly
            if (!isset($payload['email']) || !isset($payload['sub'])) {
                throw new Exception('Invalid token claims');
            }

            return [
                'google_id' => $payload['sub'],
                'email' => $payload['email'],
                'name' => $payload['name'] ?? $payload['email'],
                'avatar' => $payload['picture'] ?? null,
                'verified' => $payload['email_verified'] ?? true
            ];

        } catch (Exception $e) {
            throw new Exception('ID token verification failed: ' . $e->getMessage());
        }
    }

    private function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
