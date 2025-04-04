<?php
/**
 * Authentication tests
 * 
 * Tests the token-based authentication functionality across controllers.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Group;
use App\Database\Database;

// Set a test database path
Database::setDbPath(__DIR__ . '/test.sqlite');

// Clean the database
$db = Database::getInstance();
$db->exec("DELETE FROM group_memberships");
$db->exec("DELETE FROM messages");
$db->exec("DELETE FROM groups");
$db->exec("DELETE FROM users");

echo "Running Authentication Tests...\n";

// First, create a test user with a token
$userModel = new User();
$username = "authtest_user_" . time();
$user = $userModel->findOrCreate($username);
$userId = $user['id'];
$userToken = $user['token'];

echo "✅ Test user created with token: $userToken\n";

// Helper function to simulate API request and get response
function simulateRequest($url, $method = 'GET', $data = [], $queryParams = []) {
    $ch = curl_init();
    
    // Add query parameters to URL if provided
    if (!empty($queryParams) && $method === 'GET') {
        $url .= '?' . http_build_query($queryParams);
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'body' => $response ? json_decode($response, true) : null,
        'status' => $httpCode
    ];
}

// Test 1: Create a group with valid token
$baseUrl = 'http://localhost:8000'; // Adjust if your server uses a different URL
$groupName = "Auth Test Group " . time();

$createResponse = simulateRequest(
    "$baseUrl/groups",
    'POST',
    [
        'name' => $groupName,
        'token' => $userToken
    ]
);

if ($createResponse['status'] !== 201) {
    echo "❌ Failed to create group with valid token. Status: {$createResponse['status']}\n";
    echo "Response: " . print_r($createResponse['body'], true) . "\n";
    exit(1);
}

echo "✅ Group created successfully with valid token\n";
$groupId = $createResponse['body']['group']['id'];

// Test 2: Create a group with invalid token
$invalidToken = bin2hex(random_bytes(16));

$invalidResponse = simulateRequest(
    "$baseUrl/groups",
    'POST',
    [
        'name' => "Group with invalid token",
        'token' => $invalidToken
    ]
);

if ($invalidResponse['status'] !== 401) {
    echo "❌ Invalid token was accepted. Expected 401, got: {$invalidResponse['status']}\n";
    exit(1);
}

echo "✅ Invalid token correctly rejected with 401 status\n";

// Test 3: Join a group with valid token
$joinResponse = simulateRequest(
    "$baseUrl/groups/$groupId/join",
    'POST',
    [
        'token' => $userToken
    ]
);

if ($joinResponse['status'] !== 200) {
    echo "❌ Failed to join group with valid token. Status: {$joinResponse['status']}\n";
    echo "Response: " . print_r($joinResponse['body'], true) . "\n";
    exit(1);
}

echo "✅ Joined group successfully with valid token\n";

// Test 4: Get messages with token as query parameter
$messagesResponse = simulateRequest(
    "$baseUrl/groups/$groupId/messages",
    'GET',
    [],
    ['token' => $userToken]
);

if ($messagesResponse['status'] !== 200) {
    echo "❌ Failed to get messages with valid token. Status: {$messagesResponse['status']}\n";
    echo "Response: " . print_r($messagesResponse['body'], true) . "\n";
    exit(1);
}

echo "✅ Retrieved messages successfully with valid token as query parameter\n";

// Test 5: Send a message with valid token
$messageResponse = simulateRequest(
    "$baseUrl/groups/$groupId/messages",
    'POST',
    [
        'token' => $userToken,
        'content' => "Test message with token authentication"
    ]
);

if ($messageResponse['status'] !== 201) {
    echo "❌ Failed to send message with valid token. Status: {$messageResponse['status']}\n";
    echo "Response: " . print_r($messageResponse['body'], true) . "\n";
    exit(1);
}

echo "✅ Message sent successfully with valid token\n";

echo "\nAuthentication Tests Completed Successfully!\n"; 