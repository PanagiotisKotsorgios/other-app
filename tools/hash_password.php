<?php
/**
 * Run this script from CLI to generate a bcrypt password hash:
 *   php tools/hash_password.php yourpassword
 */
$password = $argv[1] ?? 'Admin@1234';
echo password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]) . PHP_EOL;
