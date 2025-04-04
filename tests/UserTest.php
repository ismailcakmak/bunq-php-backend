<?php
/**
 * User model tests
 * 
 * Tests the functionality of the User model, particularly token-based authentication.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Database\Database;

// Set a test database path
Database::setDbPath(__DIR__ . '/test.sqlite');

// Clean the database
$db = Database::getInstance();
$db->exec("DELETE FROM users");

echo "Running User Model Tests...\n";

// Test 1: Create a new user and verify token generation
$user = new User();
$username = "testuser_" . time(); // Ensure unique username
$createdUser = $user->findOrCreate($username);

if (!$createdUser) {
    echo "❌ Failed to create user\n";
    exit(1);
}

echo "✅ User created successfully\n";

if (empty($createdUser['token'])) {
    echo "❌ User token was not generated\n";
    exit(1);
}

echo "✅ User token generated successfully: " . $createdUser['token'] . "\n";

// Test 2: Retrieve user by token
$retrievedUser = $user->getByToken($createdUser['token']);

if (!$retrievedUser) {
    echo "❌ Failed to retrieve user by token\n";
    exit(1);
}

if ($retrievedUser['id'] !== $createdUser['id']) {
    echo "❌ Retrieved user ID doesn't match: expected {$createdUser['id']}, got {$retrievedUser['id']}\n";
    exit(1);
}

echo "✅ User retrieved by token successfully\n";

// Test 3: Verify invalid token doesn't retrieve a user
$invalidToken = bin2hex(random_bytes(16)); // Generate a random token
$invalidUser = $user->getByToken($invalidToken);

if ($invalidUser) {
    echo "❌ Retrieved user with invalid token\n";
    exit(1);
}

echo "✅ Invalid token correctly returns no user\n";

// Test 4: Create a user that already exists
$existingUser = $user->findOrCreate($username);

if ($existingUser['id'] !== $createdUser['id']) {
    echo "❌ Created duplicate user instead of retrieving existing user\n";
    exit(1);
}

echo "✅ Existing user retrieved correctly instead of creating duplicate\n";

echo "\nUser Model Tests Completed Successfully!\n"; 