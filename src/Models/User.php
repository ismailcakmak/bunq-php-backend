<?php
namespace App\Models;

use App\Database\Database;

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get a user by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Get a user by username
     */
    public function getByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Get a user by their authentication token
     */
    public function getByToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE token = :token");
        $stmt->bindParam(':token', $token, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Create a user or get existing one
     */
    public function findOrCreate($username) {
        // Check if user already exists
        $user = $this->getByUsername($username);
        
        if ($user) {
            return $user;
        }
        
        // Create a new user with a random token
        $token = bin2hex(random_bytes(16));
        
        $stmt = $this->db->prepare("
            INSERT INTO users (username, token) 
            VALUES (:username, :token)
        ");
        
        $stmt->bindParam(':username', $username, \PDO::PARAM_STR);
        $stmt->bindParam(':token', $token, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $this->getById($this->db->lastInsertId());
    }
} 