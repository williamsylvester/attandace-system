<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireAdmin();

$id = (int)($_GET['id'] ?? 0);

if (!Security::verifyCsrfToken($_GET['csrf_token'] ?? null)) {
    setFlash('error', 'Invalid request token.');
    redirect('/classes_page.php');
}

if ($id) {
    $row = ClassRoom::findById($id);
    if ($row) {
        $classRoom = new ClassRoom($row['class_name'], $row['class_code'], $row['teacher_id'], $id);
        $classRoom->delete();
        setFlash('success', 'Class deleted successfully.');
    } else {
        setFlash('error', 'Class not found.');
    }
}
redirect('/classes_page.php');
