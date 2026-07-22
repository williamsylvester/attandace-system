<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireAdmin();

$id = (int)($_GET['id'] ?? 0);

if (!Security::verifyCsrfToken($_GET['csrf_token'] ?? null)) {
    setFlash('error', 'Invalid request token.');
    redirect('/teachers.php');
}

if ($id) {
    $db = Database::getConnection();
    $stmt = $db->prepare("DELETE FROM users WHERE id = :id AND role='teacher'");
    $stmt->execute(['id' => $id]);
    setFlash('success', 'Teacher account deleted.');
}
redirect('/teachers.php');
