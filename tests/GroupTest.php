<?php
/**
 * Group model tests
 * 
 * Tests the functionality of the Group model, including creation and access.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Group;
use App\Models\User;
use App\Models\Membership;
use App\Database\Database;

// Set a test database path
Database::setDbPath(__DIR__ . '/test.sqlite');

// Clean the database
$db = Database::getInstance();
$db->exec("DELETE FROM group_memberships");
$db->exec("DELETE FROM groups");
$db->exec("DELETE FROM users");

echo "Running Group Model Tests...\n";

// Create test user
$userModel = new User();
$username = "grouptest_user_" . time();
$user = $userModel->findOrCreate($username);
$userId = $user['id'];

echo "✅ Test user created with ID: $userId\n";

// Test 1: Create a new group
$groupModel = new Group();
$groupName = "Test Group " . time();
$group = $groupModel->create($groupName, $userId);

if (!$group) {
    echo "❌ Failed to create group\n";
    exit(1);
}

$groupId = $group['id'];
echo "✅ Group created successfully with ID: $groupId\n";

// Test 2: Get group by ID
$retrievedGroup = $groupModel->getById($groupId);

if (!$retrievedGroup) {
    echo "❌ Failed to retrieve group by ID\n";
    exit(1);
}

if ($retrievedGroup['name'] !== $groupName) {
    echo "❌ Retrieved group name doesn't match: expected '$groupName', got '{$retrievedGroup['name']}'\n";
    exit(1);
}

echo "✅ Group retrieved by ID successfully\n";

// Test 3: Get all groups
$allGroups = $groupModel->getAll();
$found = false;

foreach ($allGroups as $g) {
    if ($g['id'] === $groupId) {
        $found = true;
        break;
    }
}

if (!$found) {
    echo "❌ Created group not found in the list of all groups\n";
    exit(1);
}

echo "✅ Group found in the list of all groups\n";

// Test 4: Test group membership
$membershipModel = new Membership();
$result = $membershipModel->joinGroup($groupId, $userId);

if (!$result) {
    echo "❌ Failed to add user to group\n";
    exit(1);
}

echo "✅ User added to group successfully\n";

// Test 5: Check if user is a member
$isMember = $membershipModel->isMember($groupId, $userId);

if (!$isMember) {
    echo "❌ User is not recognized as a member of the group\n";
    exit(1);
}

echo "✅ User is correctly identified as a group member\n";

// Test 6: Get group members
$members = $membershipModel->getGroupMembers($groupId);
$found = false;

foreach ($members as $member) {
    if ($member['id'] === $userId) {
        $found = true;
        break;
    }
}

if (!$found) {
    echo "❌ User not found in group members list\n";
    exit(1);
}

echo "✅ User correctly listed as a group member\n";

echo "\nGroup Model Tests Completed Successfully!\n"; 