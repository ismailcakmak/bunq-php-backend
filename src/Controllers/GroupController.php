<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\Group;
use App\Models\User;
use App\Models\Membership;

class GroupController {
    private $groupModel;
    private $userModel;
    private $membershipModel;
    
    public function __construct() {
        $this->groupModel = new Group();
        $this->userModel = new User();
        $this->membershipModel = new Membership();
    }
    
    /**
     * Get all groups - No authentication required
     */
    public function getAllGroups(Request $request, Response $response): Response {
        $groups = $this->groupModel->getAll();
        
        $payload = json_encode($groups);
        $response->getBody()->write($payload);
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
    
    /**
     * Create a new group
     */
    public function createGroup(Request $request, Response $response): Response {
        $data = json_decode($request->getBody(), true);
        
        // Validate required fields
        if (!isset($data['name']) || empty($data['name'])) {
            $error = json_encode(['error' => 'Group name is required']);
            $response->getBody()->write($error);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        
        // Authenticate with token
        if (!isset($data['token']) || empty($data['token'])) {
            $error = json_encode(['error' => 'Authentication token is required']);
            $response->getBody()->write($error);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }
        
        // Verify token and get user
        $user = $this->userModel->getByToken($data['token']);
        if (!$user) {
            $error = json_encode(['error' => 'Invalid authentication token']);
            $response->getBody()->write($error);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }
        
        // Create the group with the authenticated user as creator
        $group = $this->groupModel->create($data['name'], $user['id']);
        
        // Add creator as the first member
        $this->membershipModel->joinGroup($group['id'], $user['id']);
        
        $payload = json_encode([
            'message' => 'Group created successfully',
            'group' => $group
        ]);
        
        $response->getBody()->write($payload);
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }
    
    /**
     * Join a group
     */
    public function joinGroup(Request $request, Response $response, array $args): Response {
        $groupId = (int) $args['group_id'];
        $data = json_decode($request->getBody(), true);
        
        // Check if the group exists
        if (!$this->groupModel->exists($groupId)) {
            $error = json_encode(['error' => 'Group not found']);
            $response->getBody()->write($error);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        
        // Authenticate with token
        if (!isset($data['token']) || empty($data['token'])) {
            $error = json_encode(['error' => 'Authentication token is required']);
            $response->getBody()->write($error);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }
        
        // Verify token and get user
        $user = $this->userModel->getByToken($data['token']);
        if (!$user) {
            $error = json_encode(['error' => 'Invalid authentication token']);
            $response->getBody()->write($error);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }
        
        // Add authenticated user to the group
        $this->membershipModel->joinGroup($groupId, $user['id']);
        
        $payload = json_encode([
            'message' => 'Joined group successfully',
            'group_id' => $groupId,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username']
            ]
        ]);
        
        $response->getBody()->write($payload);
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
} 