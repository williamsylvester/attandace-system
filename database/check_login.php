<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
$db = Database::getConnection();
$stmt = $db->prepare("SELECT email, password_hash FROM users WHERE email = :email");
$stmt->execute(['email' => 'admin@school.edu']);
$row = $stmt->fetch();
if ($row) {
    echo $row['email'] . PHP_EOL;
    echo $row['password_hash'] . PHP_EOL;
    echo password_verify('Password123', $row['password_hash']) ? 'verified' : 'not-verified';
} else {
    echo 'no-user';
}
