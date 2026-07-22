<?php
/**
 * generate_hash.php
 * One-off utility: run this in the browser (or CLI: php generate_hash.php)
 * if you ever need a fresh bcrypt hash for a password, e.g. to reset the
 * demo admin/teacher password directly in the database.
 *
 * Usage: http://localhost/attendance-system/database/generate_hash.php?password=Password123
 * Then copy the output into the users.password_hash column via phpMyAdmin.
 *
 * DELETE THIS FILE before deploying to a real production server.
 */
$password = $_GET['password'] ?? 'Password123';
echo 'Password: ' . htmlspecialchars($password) . '<br>';
echo 'Hash: ' . password_hash($password, PASSWORD_DEFAULT);
