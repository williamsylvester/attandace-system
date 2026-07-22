<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();

$id = (int)($_GET['id'] ?? 0);

if (!Security::verifyCsrfToken($_GET['csrf_token'] ?? null)) {
    setFlash('error', 'Invalid request token.');
    redirect('/students.php');
}

if ($id) {
    $row = Student::findById($id);
    if ($row) {
        $student = new Student($row['student_number'], $row['full_name'], $row['email'], '', $row['course_name'], $row['year_level'], $id);
        $student->delete();
        setFlash('success', 'Student deleted successfully.');
    } else {
        setFlash('error', 'Student not found.');
    }
}
redirect('/students.php');
