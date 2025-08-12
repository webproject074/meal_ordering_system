<?php
// Test password hashing and verification
$password = 'password';

// Generate a hash
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Generated hash: " . $hash . "\n";

// Test verification
$verify = password_verify($password, $hash);
echo "Verification result: " . ($verify ? 'Success' : 'Failed') . "\n";

// Test with the hash from users.json
$stored_hash = '$2y$10$YUKj5tF6SRUmZjF9VJNyVOFnL.4QYjsTY0F.j9d9A9K5UNKcJPX8m';
$verify_stored = password_verify($password, $stored_hash);
echo "Verification with stored hash: " . ($verify_stored ? 'Success' : 'Failed') . "\n";
?>
