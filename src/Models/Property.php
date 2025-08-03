<?php

namespace App\Models;

class Property extends Model {
    protected $table = 'properties';
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'location',
        'address',
        'property_type',
        'bedrooms',
        'bathrooms',
        'area',
        'status',
        'featured',
        'created_at',
        'updated_at'
    ];

    public function createProperty($data) {
        $propertyData = [
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'location' => $data['location'],
            'address' => $data['address'] ?? null,
            'property_type' => $data['property_type'] ?? 'residential',
            'bedrooms' => $data['bedrooms'] ?? 0,
            'bathrooms' => $data['bathrooms'] ?? 0,
            'area' => $data['area'] ?? null,
            'status' => $data['status'] ?? 'available',
            'featured' => $data['featured'] ?? 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($propertyData);
    }

    public function updateProperty($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    public function getPropertiesWithImages($filters = [], $limit = 10, $offset = 0) {
        $where = [];
        $params = [];

        if (isset($filters['user_id'])) {
            $where[] = 'p.user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (isset($filters['property_type'])) {
            $where[] = 'p.property_type = ?';
            $params[] = $filters['property_type'];
        }

        if (isset($filters['min_price'])) {
            $where[] = 'p.price >= ?';
            $params[] = $filters['min_price'];
        }

        if (isset($filters['max_price'])) {
            $where[] = 'p.price <= ?';
            $params[] = $filters['max_price'];
        }

        if (isset($filters['location'])) {
            $where[] = 'p.location LIKE ?';
            $params[] = '%' . $filters['location'] . '%';
        }

        if (isset($filters['status'])) {
            $where[] = 'p.status = ?';
            $params[] = $filters['status'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT p.*, u.name as owner_name, u.email as owner_email,
                GROUP_CONCAT(pi.image_url) as images 
                FROM properties p 
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN property_images pi ON p.id = pi.property_id 
                {$whereClause}
                GROUP BY p.id 
                ORDER BY p.featured DESC, p.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";

        return $this->query($sql, $params);
    }

    public function getPropertyWithImages($id) {
        $sql = "SELECT p.*, u.name as owner_name, u.email as owner_email,
                GROUP_CONCAT(pi.image_url) as images 
                FROM properties p 
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN property_images pi ON p.id = pi.property_id 
                WHERE p.id = ? 
                GROUP BY p.id";

        $result = $this->query($sql, [$id]);
        return $result ? $result[0] : null;
    }

    public function getUserProperty($propertyId, $userId) {
        $sql = "SELECT * FROM properties WHERE id = ? AND user_id = ?";
        $result = $this->query($sql, [$propertyId, $userId]);
        return $result ? $result[0] : null;
    }

    public function addImages($propertyId, $images) {
        $sql = "INSERT INTO property_images (property_id, image_url, created_at) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $success = true;
        foreach ($images as $imageUrl) {
            if (!$stmt->execute([$propertyId, $imageUrl, date('Y-m-d H:i:s')])) {
                $success = false;
            }
        }
        
        return $success;
    }

    public function getImages($propertyId) {
        $sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY created_at ASC";
        return $this->query($sql, [$propertyId]);
    }

    public function deleteImages($propertyId) {
        $stmt = $this->db->prepare("DELETE FROM property_images WHERE property_id = ?");
        return $stmt->execute([$propertyId]);
    }
}
