<?php
namespace App\Models;

use App\Database\Database;

class Group {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all chat groups
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM groups ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Get a group by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM groups WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Create a new chat group
     */
    public function create($name, $creatorId = null) {
        $stmt = $this->db->prepare("
            INSERT INTO groups (name, creator_id) 
            VALUES (:name, :creator_id)
        ");
        
        $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
        $stmt->bindParam(':creator_id', $creatorId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->getById($this->db->lastInsertId());
    }
    
    /**
     * Check if a group exists
     */
    public function exists($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM groups WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
} 