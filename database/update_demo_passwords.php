<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';

$db = Database::getConnection();
$hash = password_hash('Password123', PASSWORD_BCRYPT);
$stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE email IN ('admin@school.edu','mary.teacher@school.edu','john.teacher@school.edu')");
$stmt->execute(['hash' => $hash]);
echo "updated";
