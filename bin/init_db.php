<?php
/**
 * Sample data insertion script
 * 
 * Run this script to populate the SQLite database with sample data.
 * Usage: php bin/init_db.php
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;
use App\Models\User;
use App\Models\Group;
use App\Models\Membership;
use App\Models\Message;

// Get database connection
$db = Database::getInstance();
$dbPath = Database::getDbPath();

echo "Connected to SQLite database at: $dbPath\n";

// Check if tables already exist and have data
$stmt = $db->query("SELECT COUNT(*) as count FROM users");
$result = $stmt->fetch();
$hasData = $result['count'] > 0;

if ($hasData) {
    // Ask for confirmation before resetting
    echo "WARNING: Database already contains data. Do you want to reset it? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    
    if (strtolower($line) !== 'y') {
        echo "Operation cancelled.\n";
        exit;
    }
    
    // Clean all data
    $db->exec("DELETE FROM messages");
    $db->exec("DELETE FROM group_memberships");
    $db->exec("DELETE FROM groups");
    $db->exec("DELETE FROM users");
    
    // Reset sequences
    $db->exec("DELETE FROM sqlite_sequence WHERE name IN ('messages', 'group_memberships', 'groups', 'users')");
    
    echo "Database has been reset.\n";
}

echo "Inserting sample data...\n";

// Create models
$userModel = new User();
$groupModel = new Group();
$membershipModel = new Membership();
$messageModel = new Message();

// Create sample users
echo "Creating users...\n";
$user1 = $userModel->findOrCreate('john');
$user2 = $userModel->findOrCreate('jane');
$user3 = $userModel->findOrCreate('bob');

// Create sample groups
echo "Creating groups...\n";
$group1 = $groupModel->create('General', $user1['id']);
$group2 = $groupModel->create('Random', $user2['id']);
$group3 = $groupModel->create('Development', $user3['id']);

// Add users to groups
echo "Adding users to groups...\n";
$membershipModel->joinGroup($group1['id'], $user1['id']);
$membershipModel->joinGroup($group1['id'], $user2['id']);
$membershipModel->joinGroup($group1['id'], $user3['id']);

$membershipModel->joinGroup($group2['id'], $user1['id']);
$membershipModel->joinGroup($group2['id'], $user2['id']);

$membershipModel->joinGroup($group3['id'], $user2['id']);
$membershipModel->joinGroup($group3['id'], $user3['id']);

// Add sample messages
echo "Adding messages...\n";
$messageModel->create($group1['id'], $user1['id'], 'Hello everyone!');
$messageModel->create($group1['id'], $user2['id'], 'Hi John!');
$messageModel->create($group1['id'], $user3['id'], 'Hey guys, how are you?');
$messageModel->create($group1['id'], $user1['id'], 'I\'m good, thanks!');

$messageModel->create($group2['id'], $user1['id'], 'This is a random message');
$messageModel->create($group2['id'], $user2['id'], 'Yes, very random indeed');

$messageModel->create($group3['id'], $user3['id'], 'Any updates on the project?');
$messageModel->create($group3['id'], $user2['id'], 'We\'re making good progress!');

echo "Sample data has been inserted successfully.\n";
echo "Database is ready to use.\n"; 