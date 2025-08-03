<?php

namespace App\Controllers;

abstract class BaseController {
    protected $requestData;

    public function __construct() {
        $this->requestData = $this->getJsonInput();
    }

    protected function getJsonInput() {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    protected function getQueryParams() {
        return $_GET;
    }

    protected function successResponse($message, $data = null, $statusCode = 200) {
        http_response_code($statusCode);
        
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('c')
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    protected function errorResponse($message, $statusCode = 400, $errors = null, $details = null) {
        http_response_code($statusCode);
        
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('c')
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($details !== null && $_ENV['APP_DEBUG'] === 'true') {
            $response['details'] = $details;
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    protected function paginatedResponse($message, $data, $pagination, $statusCode = 200) {
        http_response_code($statusCode);
        
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination,
            'timestamp' => date('c')
        ];

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    protected function validateRequiredFields($data, $requiredFields) {
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            $this->errorResponse(
                'Missing required fields', 
                400, 
                ['missing_fields' => $missingFields]
            );
        }
    }

    protected function logError($message, $context = []) {
        $logEntry = [
            'timestamp' => date('c'),
            'message' => $message,
            'context' => $context,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];

        // In production, use a proper logging system
        error_log(json_encode($logEntry));
    }

    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        
        if (is_string($data)) {
            return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
        }

        return $data;
    }

    protected function handleFileUpload($fileKey) {
        if (!isset($_FILES[$fileKey])) {
            throw new \Exception("No file uploaded for key: {$fileKey}");
        }

        $file = $_FILES[$fileKey];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("File upload error: " . $this->getUploadErrorMessage($file['error']));
        }

        return $file;
    }

    protected function handleMultipleFileUpload($fileKey) {
        if (!isset($_FILES[$fileKey])) {
            throw new \Exception("No files uploaded for key: {$fileKey}");
        }

        $files = $_FILES[$fileKey];
        $uploadedFiles = [];

        // Handle multiple files
        if (is_array($files['name'])) {
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $uploadedFiles[] = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                }
            }
        } else {
            // Single file
            if ($files['error'] === UPLOAD_ERR_OK) {
                $uploadedFiles[] = $files;
            }
        }

        return $uploadedFiles;
    }

    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File is too large (exceeds upload_max_filesize)';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File is too large (exceeds MAX_FILE_SIZE)';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
}
