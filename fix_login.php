<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance()->getConnection();

// Check current users
$stmt = $db->query("SELECT user_id, username, password_hash, role FROM users");
$users = $stmt->fetchAll();
echo "Current users in DB:\n";
foreach ($users as $u) {
    echo "  ID={$u['user_id']} username={$u['username']} role={$u['role']}\n";
    echo "  hash={$u['password_hash']}\n";
    $valid = password_verify('password123', $u['password_hash']);
    echo "  password_verify('password123'): " . ($valid ? 'TRUE' : 'FALSE') . "\n\n";
}

// Generate a correct hash and update
$correctHash = password_hash('password123', PASSWORD_DEFAULT);
echo "Generating correct hash for 'password123': $correctHash\n\n";

// Update all users with correct hash
$update = $db->prepare("UPDATE users SET password_hash = :hash WHERE username = :user");
$update->execute(['hash' => $correctHash, 'user' => 'admin']);
$update->execute(['hash' => $correctHash, 'user' => 'principal']);
echo "Updated password hashes for admin and principal.\n";

// Verify
$stmt2 = $db->query("SELECT username, password_hash FROM users");
foreach ($stmt2->fetchAll() as $u) {
    $valid = password_verify('password123', $u['password_hash']);
    echo "Verify {$u['username']}: " . ($valid ? 'OK' : 'FAIL') . "\n";
}
