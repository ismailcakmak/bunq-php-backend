<?php
require __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;

use App\Controllers\GroupController;
use App\Controllers\MessageController;

// Create app
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add routes
$groupController = new GroupController();
$messageController = new MessageController();

// GET / - API information
$app->get('/', function (Request $request, Response $response) {
    $apiInfo = [
        'name' => 'Chat Application API',
        'version' => '1.0.0',
        'endpoints' => [
            ['method' => 'GET', 'path' => '/groups', 'description' => 'List all chat groups'],
            ['method' => 'POST', 'path' => '/groups', 'description' => 'Create a new chat group'],
            ['method' => 'POST', 'path' => '/groups/{group_id}/join', 'description' => 'Join a chat group'],
            ['method' => 'POST', 'path' => '/groups/{group_id}/messages', 'description' => 'Send a message to a group'],
            ['method' => 'GET', 'path' => '/groups/{group_id}/messages', 'description' => 'Get messages from a group']
        ]
    ];
    
    $response->getBody()->write(json_encode($apiInfo, JSON_PRETTY_PRINT));
    return $response->withHeader('Content-Type', 'application/json');
});

// GET /groups - List all chat groups
$app->get('/groups', function (Request $request, Response $response) use ($groupController) {
    return $groupController->getAllGroups($request, $response);
});

// POST /groups - Create a new chat group
$app->post('/groups', function (Request $request, Response $response) use ($groupController) {
    return $groupController->createGroup($request, $response);
});

// POST /groups/{group_id}/join - Join a chat group
$app->post('/groups/{group_id}/join', function (Request $request, Response $response, array $args) use ($groupController) {
    return $groupController->joinGroup($request, $response, $args);
});

// POST /groups/{group_id}/messages - Send a message to a group
$app->post('/groups/{group_id}/messages', function (Request $request, Response $response, array $args) use ($messageController) {
    return $messageController->createMessage($request, $response, $args);
});

// GET /groups/{group_id}/messages - Get messages from a group
$app->get('/groups/{group_id}/messages', function (Request $request, Response $response, array $args) use ($messageController) {
    return $messageController->getMessages($request, $response, $args);
});

// Run app
$app->run();
