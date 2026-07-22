<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();

$user = Auth::currentUser();
$isAdmin = $user instanceof Admin;

$classes = ClassRoom::all();

$pageTitle = 'Classes';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-easel-fill me-2"></i>Classes / Subjects</h4>
  <?php if ($isAdmin): ?>
  <a href="<?= BASE_URL ?>/class_form.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Class</a>
  <?php endif; ?>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr><th>#</th><th>Class Code</th><th>Class Name</th><th>Teacher</th><th>Enrolled</th><th class="no-print">Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($classes)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No classes found.</td></tr>
        <?php endif; ?>
        <?php foreach ($classes as $i => $c): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= e($c['class_code']) ?></td>
          <td><?= e($c['class_name']) ?></td>
          <td><?= e($c['teacher_name']) ?></td>
          <td><?= count(ClassRoom::getEnrolledStudents((int)$c['id'])) ?></td>
          <td class="no-print">
            <a href="<?= BASE_URL ?>/class_form.php?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-info" title="Manage Enrollment"><i class="bi bi-people"></i></a>
            <?php if ($isAdmin): ?>
            <a href="<?= BASE_URL ?>/class_delete.php?id=<?= (int)$c['id'] ?>&csrf_token=<?= Security::generateCsrfToken() ?>"
               class="btn btn-sm btn-outline-danger confirm-delete"><i class="bi bi-trash"></i></a>
            <?php endif; ?>
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
