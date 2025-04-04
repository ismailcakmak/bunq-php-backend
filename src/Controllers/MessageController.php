<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Message;
use App\Models\Group;
use App\Models\User;
use App\Models\Membership;

class MessageController {
    private $messageModel;
    private $groupModel;
    private $userModel;
    private $membershipModel;
    
    public function __construct() {
        $this->messageModel = new Message();
        $this->groupModel = new Group();
        $this->userModel = new User();
        $this->membershipModel = new Membership();
    }
    
    /**
     * Create a new message in a group
     */
    public function createMessage(Request $request, Response $response, array $args): Response {
        $groupId = (int) $args['group_id'];
        $data = json_decode($request->getBody(), true);
        
        // Validate required fields
        if (!isset($data['content']) || empty($data['content'])) {
            $error = json_encode(['error' => 'Message content is required']);
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
        
        // Check if the group exists
        if (!$this->groupModel->exists($groupId)) {
            $error = json_encode(['error' => 'Group not found']);
            $response->getBody()->write($error);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        
        // Ensure user is a member of the group
        if (!$this->membershipModel->isMember($groupId, $user['id'])) {
            // Auto-join the group if not a member
            $this->membershipModel->joinGroup($groupId, $user['id']);
        }
        
        // Create the message
        $messageId = $this->messageModel->create($groupId, $user['id'], $data['content']);
        $message = $this->messageModel->getById($messageId);
        
        $payload = json_encode([
            'message' => 'Message sent successfully',
            'data' => $message
        ]);
        
        $response->getBody()->write($payload);
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }
    
    /**
     * Get messages from a group
     */
    public function getMessages(Request $request, Response $response, array $args): Response {
        $groupId = (int) $args['group_id'];
        $queryParams = $request->getQueryParams();
        
        // Authenticate with token
        if (!isset($queryParams['token']) || empty($queryParams['token'])) {
            $error = json_encode(['error' => 'Authentication token is required']);
            $response->getBody()->write($error);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }
        
        // Verify token and get user
        $user = $this->userModel->getByToken($queryParams['token']);
        if (!$user) {
            $error = json_encode(['error' => 'Invalid authentication token']);
            $response->getBody()->write($error);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }
        
        // Check if the group exists
        if (!$this->groupModel->exists($groupId)) {
            $error = json_encode(['error' => 'Group not found']);
            $response->getBody()->write($error);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }
        
        // Ensure user is a member of the group
        if (!$this->membershipModel->isMember($groupId, $user['id'])) {
            $error = json_encode(['error' => 'You are not a member of this group']);
            $response->getBody()->write($error);
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(403);
        }
        
        // Get query parameters for pagination
        $limit = isset($queryParams['limit']) ? (int) $queryParams['limit'] : 50;
        $offset = isset($queryParams['offset']) ? (int) $queryParams['offset'] : 0;
        
        // Get messages
        $messages = $this->messageModel->getByGroupId($groupId, $limit, $offset);
        
        $payload = json_encode([
            'group_id' => $groupId,
            'messages' => $messages,
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset
            ]
        ]);
        
        $response->getBody()->write($payload);
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
} 