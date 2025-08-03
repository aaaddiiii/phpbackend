<?php

namespace App\Services;

use Exception;

class ValidationService {
    private $errors = [];

    public function validateRegistration($data) {
        $this->errors = [];

        // Name validation
        if (empty($data['name']) || strlen($data['name']) < 2 || strlen($data['name']) > 50) {
            $this->errors['name'] = 'Name must be between 2 and 50 characters';
        }

        // Email validation
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please provide a valid email address';
        }

        // Password validation (only if provided - for regular registration)
        if (isset($data['password'])) {
            if (strlen($data['password']) < 8) {
                $this->errors['password'] = 'Password must be at least 8 characters long';
            }

            // Password strength check
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $data['password'])) {
                $this->errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
            }
        }

        return empty($this->errors);
    }

    public function validateLogin($data) {
        $this->errors = [];

        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please provide a valid email address';
        }

        if (empty($data['password'])) {
            $this->errors['password'] = 'Password is required';
        }

        return empty($this->errors);
    }

    public function validateProperty($data) {
        $this->errors = [];

        // Title validation
        if (empty($data['title']) || strlen($data['title']) < 5 || strlen($data['title']) > 100) {
            $this->errors['title'] = 'Title must be between 5 and 100 characters';
        }

        // Description validation
        if (empty($data['description']) || strlen($data['description']) < 10 || strlen($data['description']) > 1000) {
            $this->errors['description'] = 'Description must be between 10 and 1000 characters';
        }

        // Price validation
        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            $this->errors['price'] = 'Price must be a positive number';
        }

        // Location validation
        if (empty($data['location']) || strlen($data['location']) < 2 || strlen($data['location']) > 100) {
            $this->errors['location'] = 'Location must be between 2 and 100 characters';
        }

        // Property type validation
        $validTypes = ['residential', 'commercial', 'industrial', 'land'];
        if (isset($data['property_type']) && !in_array($data['property_type'], $validTypes)) {
            $this->errors['property_type'] = 'Property type must be one of: ' . implode(', ', $validTypes);
        }

        // Bedrooms validation
        if (isset($data['bedrooms']) && (!is_numeric($data['bedrooms']) || $data['bedrooms'] < 0 || $data['bedrooms'] > 20)) {
            $this->errors['bedrooms'] = 'Bedrooms must be between 0 and 20';
        }

        // Bathrooms validation
        if (isset($data['bathrooms']) && (!is_numeric($data['bathrooms']) || $data['bathrooms'] < 0 || $data['bathrooms'] > 20)) {
            $this->errors['bathrooms'] = 'Bathrooms must be between 0 and 20';
        }

        // Area validation
        if (isset($data['area']) && (!is_numeric($data['area']) || $data['area'] <= 0)) {
            $this->errors['area'] = 'Area must be a positive number';
        }

        // Status validation
        $validStatuses = ['available', 'sold', 'rented', 'pending'];
        if (isset($data['status']) && !in_array($data['status'], $validStatuses)) {
            $this->errors['status'] = 'Status must be one of: ' . implode(', ', $validStatuses);
        }

        return empty($this->errors);
    }

    public function validateImageUpload($files) {
        $this->errors = [];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (empty($files)) {
            $this->errors['images'] = 'At least one image is required';
            return false;
        }

        foreach ($files as $index => $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->errors["file_{$index}"] = 'File upload error occurred';
                continue;
            }

            if ($file['size'] > $maxFileSize) {
                $this->errors["file_{$index}"] = 'File size must be less than 5MB';
            }

            if (!in_array($file['type'], $allowedTypes)) {
                $this->errors["file_{$index}"] = 'File must be a valid image (JPEG, PNG, GIF, WebP)';
            }

            // Additional MIME type check
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mimeType, $allowedTypes)) {
                    $this->errors["file_{$index}"] = 'Invalid file type detected';
                }
            }
        }

        return empty($this->errors);
    }

    public function validatePagination($data) {
        $this->errors = [];

        $page = $data['page'] ?? 1;
        $limit = $data['limit'] ?? 10;

        if (!is_numeric($page) || $page < 1) {
            $this->errors['page'] = 'Page must be a positive integer';
        }

        if (!is_numeric($limit) || $limit < 1 || $limit > 100) {
            $this->errors['limit'] = 'Limit must be between 1 and 100';
        }

        return empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function hasErrors() {
        return !empty($this->errors);
    }

    public function sanitizeString($string) {
        return trim(htmlspecialchars($string, ENT_QUOTES, 'UTF-8'));
    }

    public function sanitizeArray($array) {
        $sanitized = [];
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
