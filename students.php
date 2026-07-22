<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();

$keyword = trim($_GET['q'] ?? '');
$students = $keyword !== '' ? Student::search($keyword) : Student::all();

$pageTitle = 'Students';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i>Students</h4>
  <a href="<?= BASE_URL ?>/student_form.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Student</a>
</div>

<div class="card p-3 mb-3">
  <form method="GET" class="row g-2">
    <div class="col-md-8">
      <input type="text" name="q" class="form-control" placeholder="Search by name, student number or course..." value="<?= e($keyword) ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search me-1"></i>Search</button>
    </div>
    <div class="col-md-2">
      <a href="<?= BASE_URL ?>/students.php" class="btn btn-outline-secondary w-100">Reset</a>
    </div>
  </form>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>#</th><th>Student No.</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Course</th><th>Year</th><th class="no-print">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($students)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">No students found.</td></tr>
        <?php endif; ?>
        <?php foreach ($students as $i => $s): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= e($s['student_number']) ?></td>
          <td><?= e($s['full_name']) ?></td>
          <td><?= e($s['email']) ?></td>
          <td><?= e($s['phone_decrypted']) ?></td>
          <td><?= e($s['course_name']) ?></td>
          <td><?= e($s['year_level']) ?></td>
          <td class="no-print">
            <a href="<?= BASE_URL ?>/student_form.php?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil-square"></i></a>
            <a href="<?= BASE_URL ?>/student_delete.php?id=<?= (int)$s['id'] ?>&csrf_token=<?= Security::generateCsrfToken() ?>"
               class="btn btn-sm btn-outline-danger confirm-delete"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3 no-print">
  <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
