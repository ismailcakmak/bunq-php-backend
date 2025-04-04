<?php
namespace App\Models;

use App\Database\Database;

class Membership {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Add a user to a group (join a group)
     */
    public function joinGroup($groupId, $userId) {
        // Check if already a member
        if ($this->isMember($groupId, $userId)) {
            return true; // Already a member
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO group_memberships (group_id, user_id) 
            VALUES (:group_id, :user_id)
        ");
        
        $stmt->bindParam(':group_id', $groupId, \PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Check if a user is a member of a group
     */
    public function isMember($groupId, $userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM group_memberships 
            WHERE group_id = :group_id AND user_id = :user_id
        ");
        
        $stmt->bindParam(':group_id', $groupId, \PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Get all members of a group
     */
    public function getGroupMembers($groupId) {
        $stmt = $this->db->prepare("
            SELECT u.* 
            FROM users u
            JOIN group_memberships gm ON u.id = gm.user_id
            WHERE gm.group_id = :group_id
            ORDER BY gm.joined_at ASC
        ");
        
        $stmt->bindParam(':group_id', $groupId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
} 