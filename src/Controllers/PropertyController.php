<?php

namespace App\Controllers;

use App\Services\JWTService;
use App\Services\ValidationService;
use App\Services\CloudinaryService;
use App\Models\Property;

class PropertyController extends BaseController {
    private $jwtService;
    private $validationService;
    private $cloudinaryService;
    private $propertyModel;

    public function __construct() {
        parent::__construct();
        $this->jwtService = new JWTService();
        $this->validationService = new ValidationService();
        $this->cloudinaryService = new CloudinaryService();
        $this->propertyModel = new Property();
    }

    public function index($params = []) {
        try {
            $queryParams = $this->getQueryParams();
            
            // Extract pagination parameters
            $page = max(1, (int)($queryParams['page'] ?? 1));
            $limit = min(50, max(1, (int)($queryParams['limit'] ?? 10)));
            $offset = ($page - 1) * $limit;

            // Extract filters
            $filters = [];
            
            if (isset($queryParams['property_type'])) {
                $filters['property_type'] = $queryParams['property_type'];
            }
            
            if (isset($queryParams['min_price'])) {
                $filters['min_price'] = (float)$queryParams['min_price'];
            }
            
            if (isset($queryParams['max_price'])) {
                $filters['max_price'] = (float)$queryParams['max_price'];
            }
            
            if (isset($queryParams['location'])) {
                $filters['location'] = $queryParams['location'];
            }
            
            if (isset($queryParams['status'])) {
                $filters['status'] = $queryParams['status'];
            }

            // If user_properties=true, filter by current user
            if (isset($queryParams['user_properties']) && $queryParams['user_properties'] === 'true') {
                $currentUser = $this->jwtService->getCurrentUser();
                $filters['user_id'] = $currentUser['user_id'];
            }

            // Get properties with images
            $properties = $this->propertyModel->getPropertiesWithImages($filters, $limit, $offset);
            
            // Process images for each property
            foreach ($properties as &$property) {
                $property['images'] = $property['images'] ? explode(',', $property['images']) : [];
                $property['price'] = (float)$property['price'];
                $property['bedrooms'] = (int)$property['bedrooms'];
                $property['bathrooms'] = (float)$property['bathrooms'];
                $property['area'] = $property['area'] ? (float)$property['area'] : null;
                $property['featured'] = (bool)$property['featured'];
            }

            // Get total count for pagination
            $totalCount = $this->propertyModel->count(
                !empty($filters) ? $this->buildWhereClause($filters) : null,
                array_values($filters)
            );

            $pagination = [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $totalCount,
                'total_pages' => ceil($totalCount / $limit),
                'has_next' => $page < ceil($totalCount / $limit),
                'has_prev' => $page > 1
            ];

            return $this->paginatedResponse(
                'Properties retrieved successfully',
                $properties,
                $pagination
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve properties', 500, null, $e->getMessage());
        }
    }

    public function show($params) {
        try {
            $propertyId = $params['id'] ?? null;
            
            if (!$propertyId) {
                return $this->errorResponse('Property ID is required', 400);
            }

            $property = $this->propertyModel->getPropertyWithImages($propertyId);
            
            if (!$property) {
                return $this->errorResponse('Property not found', 404);
            }

            // Process property data
            $property['images'] = $property['images'] ? explode(',', $property['images']) : [];
            $property['price'] = (float)$property['price'];
            $property['bedrooms'] = (int)$property['bedrooms'];
            $property['bathrooms'] = (float)$property['bathrooms'];
            $property['area'] = $property['area'] ? (float)$property['area'] : null;
            $property['featured'] = (bool)$property['featured'];

            return $this->successResponse('Property retrieved successfully', ['property' => $property]);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve property', 500, null, $e->getMessage());
        }
    }

    public function store($params = []) {
        try {
            $currentUser = $this->jwtService->getCurrentUser();
            $data = $this->getJsonInput();

            // Validate input
            if (!$this->validationService->validateProperty($data)) {
                return $this->errorResponse('Validation failed', 400, $this->validationService->getErrors());
            }

            // Add user ID to property data
            $data['user_id'] = $currentUser['user_id'];

            // Sanitize input
            $data = $this->validationService->sanitizeArray($data);

            // Create property
            $property = $this->propertyModel->createProperty($data);

            if (!$property) {
                return $this->errorResponse('Failed to create property', 500);
            }

            // Process property data for response
            $property['price'] = (float)$property['price'];
            $property['bedrooms'] = (int)$property['bedrooms'];
            $property['bathrooms'] = (float)$property['bathrooms'];
            $property['area'] = $property['area'] ? (float)$property['area'] : null;
            $property['featured'] = (bool)$property['featured'];

            return $this->successResponse('Property created successfully', ['property' => $property], 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create property', 500, null, $e->getMessage());
        }
    }

    public function update($params) {
        try {
            $currentUser = $this->jwtService->getCurrentUser();
            $propertyId = $params['id'] ?? null;
            $data = $this->getJsonInput();

            if (!$propertyId) {
                return $this->errorResponse('Property ID is required', 400);
            }

            // Check if property exists and belongs to user
            $existingProperty = $this->propertyModel->getUserProperty($propertyId, $currentUser['user_id']);
            
            if (!$existingProperty) {
                return $this->errorResponse('Property not found or access denied', 404);
            }

            // Validate input
            if (!$this->validationService->validateProperty($data)) {
                return $this->errorResponse('Validation failed', 400, $this->validationService->getErrors());
            }

            // Sanitize input
            $data = $this->validationService->sanitizeArray($data);

            // Update property
            $property = $this->propertyModel->updateProperty($propertyId, $data);

            if (!$property) {
                return $this->errorResponse('Failed to update property', 500);
            }

            // Get updated property with images
            $property = $this->propertyModel->getPropertyWithImages($propertyId);
            
            // Process property data
            $property['images'] = $property['images'] ? explode(',', $property['images']) : [];
            $property['price'] = (float)$property['price'];
            $property['bedrooms'] = (int)$property['bedrooms'];
            $property['bathrooms'] = (float)$property['bathrooms'];
            $property['area'] = $property['area'] ? (float)$property['area'] : null;
            $property['featured'] = (bool)$property['featured'];

            return $this->successResponse('Property updated successfully', ['property' => $property]);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update property', 500, null, $e->getMessage());
        }
    }

    public function destroy($params) {
        try {
            $currentUser = $this->jwtService->getCurrentUser();
            $propertyId = $params['id'] ?? null;

            if (!$propertyId) {
                return $this->errorResponse('Property ID is required', 400);
            }

            // Check if property exists and belongs to user
            $property = $this->propertyModel->getUserProperty($propertyId, $currentUser['user_id']);
            
            if (!$property) {
                return $this->errorResponse('Property not found or access denied', 404);
            }

            // Delete property images from database and Cloudinary
            $images = $this->propertyModel->getImages($propertyId);
            foreach ($images as $image) {
                try {
                    // Extract public_id from Cloudinary URL
                    $publicId = $this->extractPublicIdFromUrl($image['image_url']);
                    if ($publicId) {
                        $this->cloudinaryService->deleteImage($publicId);
                    }
                } catch (\Exception $e) {
                    // Log error but continue with deletion
                    $this->logError('Failed to delete image from Cloudinary', [
                        'image_url' => $image['image_url'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Delete property images from database
            $this->propertyModel->deleteImages($propertyId);

            // Delete property
            $deleted = $this->propertyModel->delete($propertyId);

            if (!$deleted) {
                return $this->errorResponse('Failed to delete property', 500);
            }

            return $this->successResponse('Property deleted successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete property', 500, null, $e->getMessage());
        }
    }

    public function uploadImages($params) {
        try {
            $currentUser = $this->jwtService->getCurrentUser();
            $propertyId = $params['id'] ?? null;

            if (!$propertyId) {
                return $this->errorResponse('Property ID is required', 400);
            }

            // Check if property exists and belongs to user
            $property = $this->propertyModel->getUserProperty($propertyId, $currentUser['user_id']);
            
            if (!$property) {
                return $this->errorResponse('Property not found or access denied', 404);
            }

            // Handle file uploads
            $files = $this->handleMultipleFileUpload('images');
            
            if (empty($files)) {
                return $this->errorResponse('No images uploaded', 400);
            }

            // Validate files
            if (!$this->validationService->validateImageUpload($files)) {
                return $this->errorResponse('Image validation failed', 400, $this->validationService->getErrors());
            }

            // Upload images to Cloudinary
            $uploadResult = $this->cloudinaryService->uploadMultipleImages($files, [
                'folder' => "real-estate/properties/{$propertyId}"
            ]);

            if ($uploadResult['error_count'] > 0) {
                return $this->errorResponse('Some images failed to upload', 400, [
                    'upload_errors' => $uploadResult['errors']
                ]);
            }

            // Save image URLs to database
            $imageUrls = array_column($uploadResult['uploaded'], 'url');
            $saved = $this->propertyModel->addImages($propertyId, $imageUrls);

            if (!$saved) {
                return $this->errorResponse('Failed to save image references', 500);
            }

            return $this->successResponse('Images uploaded successfully', [
                'uploaded_count' => $uploadResult['success_count'],
                'images' => $uploadResult['uploaded']
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload images', 500, null, $e->getMessage());
        }
    }

    private function buildWhereClause($filters) {
        $conditions = [];
        
        if (isset($filters['user_id'])) {
            $conditions[] = 'user_id = ?';
        }
        
        if (isset($filters['property_type'])) {
            $conditions[] = 'property_type = ?';
        }
        
        if (isset($filters['min_price'])) {
            $conditions[] = 'price >= ?';
        }
        
        if (isset($filters['max_price'])) {
            $conditions[] = 'price <= ?';
        }
        
        if (isset($filters['location'])) {
            $conditions[] = 'location LIKE ?';
        }
        
        if (isset($filters['status'])) {
            $conditions[] = 'status = ?';
        }

        return implode(' AND ', $conditions);
    }

    private function extractPublicIdFromUrl($url) {
        // Extract public_id from Cloudinary URL
        // Example: https://res.cloudinary.com/demo/image/upload/v1234567890/sample.jpg
        preg_match('/\/v\d+\/(.+)\.(jpg|jpeg|png|gif|webp)$/i', $url, $matches);
        return $matches[1] ?? null;
    }
}
