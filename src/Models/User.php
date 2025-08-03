<?php

namespace App\Models;

class User extends Model {
    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'email_verified_at',
        'created_at',
        'updated_at'
    ];

    public function findByEmail($email) {
        return $this->findBy('email', $email);
    }

    public function findByGoogleId($googleId) {
        return $this->findBy('google_id', $googleId);
    }

    public function createUser($data) {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => isset($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : null,
            'google_id' => $data['google_id'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'email_verified_at' => isset($data['google_id']) ? date('Y-m-d H:i:s') : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($userData);
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function updateUser($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    public function getProperties($userId) {
        $sql = "SELECT p.*, 
                GROUP_CONCAT(pi.image_url) as images 
                FROM properties p 
                LEFT JOIN property_images pi ON p.id = pi.property_id 
                WHERE p.user_id = ? 
                GROUP BY p.id 
                ORDER BY p.created_at DESC";
        
        return $this->query($sql, [$userId]);
    }
}
