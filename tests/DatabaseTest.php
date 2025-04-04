<?php
/**
 * Database tests
 * 
 * Tests the functionality of the Database class, particularly initialization and configuration.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;

echo "Running Database Tests...\n";

// Test 1: Set custom database path
$testDbPath = __DIR__ . '/custom_test.sqlite';

// Delete the test database if it exists
if (file_exists($testDbPath)) {
    unlink($testDbPath);
}

// Set the database path
Database::setDbPath($testDbPath);

// Check if the path was properly set
$dbPath = Database::getDbPath();

if ($dbPath !== $testDbPath) {
    echo "❌ Database path setting failed. Expected '$testDbPath', got '$dbPath'\n";
    exit(1);
}

echo "✅ Database path set successfully\n";

// Test 2: Initialize database and verify file creation
$db = Database::getInstance();

if (!file_exists($testDbPath)) {
    echo "❌ Database file was not created\n";
    exit(1);
}

echo "✅ Database file created successfully\n";

// Test 3: Check if tables were created
$tables = [];
$stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
while ($row = $stmt->fetch()) {
    $tables[] = $row['name'];
}

$requiredTables = ['users', 'groups', 'group_memberships', 'messages'];
$missingTables = array_diff($requiredTables, $tables);

if (!empty($missingTables)) {
    echo "❌ Missing tables: " . implode(', ', $missingTables) . "\n";
    exit(1);
}

echo "✅ All required tables were created:\n";
foreach ($requiredTables as $table) {
    echo "  - $table\n";
}

// Test 4: Test singleton pattern
$db2 = Database::getInstance();
if (spl_object_hash($db) !== spl_object_hash($db2)) {
    echo "❌ Database singleton pattern failed - different instances returned\n";
    exit(1);
}

echo "✅ Database singleton pattern works correctly\n";

// Test 5: Check database schema
$userTableSchema = [];
$stmt = $db->query("PRAGMA table_info(users)");
while ($row = $stmt->fetch()) {
    $userTableSchema[$row['name']] = $row['type'];
}

// Check if token column exists in users table
if (!isset($userTableSchema['token'])) {
    echo "❌ Token column not found in users table\n";
    exit(1);
}

echo "✅ Token column exists in users table\n";

// Clean up
if (file_exists($testDbPath)) {
    unlink($testDbPath);
    echo "✅ Test database file cleaned up\n";
}

echo "\nDatabase Tests Completed Successfully!\n"; 