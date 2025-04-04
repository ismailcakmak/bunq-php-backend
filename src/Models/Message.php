<?php
namespace App\Models;

use App\Database\Database;

class Message {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create a new message in a group
     */
    public function create($groupId, $userId, $content) {
        $stmt = $this->db->prepare("
            INSERT INTO messages (group_id, user_id, content) 
            VALUES (:group_id, :user_id, :content)
        ");
        
        $stmt->bindParam(':group_id', $groupId, \PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':content', $content, \PDO::PARAM_STR);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Get messages from a group with optional pagination
     */
    public function getByGroupId($groupId, $limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.username
            FROM messages m
            JOIN users u ON m.user_id = u.id
            WHERE m.group_id = :group_id
            ORDER BY m.created_at ASC
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindParam(':group_id', $groupId, \PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get a single message by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.username
            FROM messages m
            JOIN users u ON m.user_id = u.id
            WHERE m.id = :id
        ");
        
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
} 