<?php
/**
 * One-time setup script.
 * Run from CLI: php tools/setup.php
 * This seeds the default admin user with password "Admin@1234"
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$host   = $_ENV['DB_HOST']     ?? '127.0.0.1';
$port   = $_ENV['DB_PORT']     ?? '3306';
$dbname = $_ENV['DB_DATABASE'] ?? 'call_center';
$user   = $_ENV['DB_USERNAME'] ?? 'root';
$pass   = $_ENV['DB_PASSWORD'] ?? '';

$pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$hash = password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost' => 12]);

// INSERT IGNORE: only seeds the admin if no row with this email exists yet.
// This prevents overwriting a custom admin password on container restart.
$pdo->prepare("
    INSERT IGNORE INTO users (name, email, password, role, is_active)
    VALUES ('Administrator', 'admin@callcenter.com', ?, 'admin', 1)
")->execute([$hash]);

echo "Admin user seeded.\n";
echo "  Email:    admin@callcenter.com\n";
echo "  Password: Admin@1234\n";
echo "\nChange the password after first login!\n";
