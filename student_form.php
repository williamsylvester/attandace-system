<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$existing = $id ? Student::findById($id) : null;
if ($id && !$existing) {
    setFlash('error', 'Student not found.');
    redirect('/students.php');
}

$errors = [];
$formData = [
    'student_number' => $existing['student_number'] ?? '',
    'full_name'      => $existing['full_name'] ?? '',
    'email'          => $existing['email'] ?? '',
    'phone'          => $existing['phone_decrypted'] ?? '',
    'course_name'    => $existing['course_name'] ?? '',
    'year_level'     => $existing['year_level'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid session token. Please try again.';
    } else {
        $formData['student_number'] = trim($_POST['student_number'] ?? '');
        $formData['full_name']      = trim($_POST['full_name'] ?? '');
        $formData['email']          = trim($_POST['email'] ?? '');
        $formData['phone']          = trim($_POST['phone'] ?? '');
        $formData['course_name']    = trim($_POST['course_name'] ?? '');
        $formData['year_level']     = trim($_POST['year_level'] ?? '');

        $v = new Validator();
        $v->required($formData['student_number'], 'student_number')
          ->required($formData['full_name'], 'full_name')
          ->required($formData['email'], 'email')
          ->email($formData['email'], 'email')
          ->required($formData['course_name'], 'course_name');

        if ($v->hasErrors()) {
            $errors = array_values($v->getErrors());
        } else {
            $student = new Student(
                $formData['student_number'],
                $formData['full_name'],
                $formData['email'],
                $formData['phone'],
                $formData['course_name'],
                $formData['year_level'],
                $id
            );

            $success = $id ? $student->update() : $student->create();

            if ($success !== false) {
                setFlash('success', $id ? 'Student updated successfully.' : 'Student added successfully.');
                redirect('/students.php');
            } else {
                $errors[] = 'Could not save student. The student number or email may already exist.';
            }
        }
    }
}

$pageTitle = $id ? 'Edit Student' : 'Add Student';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<h4 class="mb-3"><i class="bi bi-person-plus-fill me-2"></i><?= $id ? 'Edit Student' : 'Add New Student' ?></h4>

<div class="card p-4" style="max-width:700px;">
  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger py-2"><?= e($err) ?></div>
  <?php endforeach; ?>

  <form method="POST" novalidate>
    <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Student Number *</label>
        <input type="text" name="student_number" class="form-control" required value="<?= e($formData['student_number']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Full Name *</label>
        <input type="text" name="full_name" class="form-control" required value="<?= e($formData['full_name']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Email *</label>
        <input type="email" name="email" class="form-control" required value="<?= e($formData['email']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Phone Number (encrypted at rest)</label>
        <input type="text" name="phone" class="form-control" value="<?= e($formData['phone']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Course *</label>
        <input type="text" name="course_name" class="form-control" required value="<?= e($formData['course_name']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Year Level</label>
        <select name="year_level" class="form-select">
          <?php foreach (['Year 1','Year 2','Year 3','Year 4'] as $yl): ?>
            <option value="<?= $yl ?>" <?= $formData['year_level'] === $yl ? 'selected' : '' ?>><?= $yl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill me-1"></i>Save Student</button>
      <a href="<?= BASE_URL ?>/students.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to List</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
