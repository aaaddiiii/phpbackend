<?php

namespace App\Services;

use Exception;

class CloudinaryService {
    private $cloudName;
    private $apiKey;
    private $apiSecret;

    public function __construct() {
        $this->cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'];
        $this->apiKey = $_ENV['CLOUDINARY_API_KEY'];
        $this->apiSecret = $_ENV['CLOUDINARY_API_SECRET'];
    }

    public function uploadImage($file, $options = []) {
        // Simplified version - for now, we'll just move files to a local uploads directory
        // In production, you would use the actual Cloudinary API
        
        try {
            $uploadsDir = __DIR__ . '/../../uploads/';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('img_') . '.' . $extension;
            $targetPath = $uploadsDir . $filename;

            // Move uploaded file
            if (is_array($file) && isset($file['tmp_name'])) {
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/phpbackendPMS/uploads/';
                    return [
                        'url' => $baseUrl . $filename,
                        'public_id' => pathinfo($filename, PATHINFO_FILENAME),
                        'width' => 800, // Mock dimensions
                        'height' => 600,
                        'format' => $extension,
                        'bytes' => filesize($targetPath)
                    ];
                }
            }

            throw new Exception('Failed to upload file');

        } catch (Exception $e) {
            throw new Exception('Image upload failed: ' . $e->getMessage());
        }
    }

    public function uploadMultipleImages($files, $options = []) {
        $uploadedImages = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $result = $this->uploadImage($file, $options);
                $uploadedImages[] = $result;
            } catch (Exception $e) {
                $errors[] = "File {$index}: " . $e->getMessage();
            }
        }

        return [
            'uploaded' => $uploadedImages,
            'errors' => $errors,
            'success_count' => count($uploadedImages),
            'error_count' => count($errors)
        ];
    }

    public function deleteImage($publicId) {
        // Simplified version - delete from local uploads directory
        try {
            $uploadsDir = __DIR__ . '/../../uploads/';
            $filePath = $uploadsDir . $publicId;
            
            // Try different extensions
            $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            foreach ($extensions as $ext) {
                $fullPath = $filePath . '.' . $ext;
                if (file_exists($fullPath)) {
                    return unlink($fullPath);
                }
            }
            
            return false;
        } catch (Exception $e) {
            throw new Exception('Image deletion failed: ' . $e->getMessage());
        }
    }

    public function generateThumbnail($publicId, $width = 300, $height = 200) {
        // For the simplified version, return the original image URL
        $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/phpbackendPMS/uploads/';
        return $baseUrl . $publicId;
    }

    public function getImageInfo($publicId) {
        throw new Exception('Image info requires Cloudinary API. Using simplified local storage.');
    }
}
