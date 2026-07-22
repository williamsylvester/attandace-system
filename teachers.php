<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireAdmin();

$db = Database::getConnection();
$teachers = $db->query("SELECT * FROM users WHERE role='teacher' ORDER BY full_name")->fetchAll();

$pageTitle = 'Teachers';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-person-badge-fill me-2"></i>Teacher Accounts</h4>
  <a href="<?= BASE_URL ?>/teacher_form.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Teacher</a>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>#</th><th>Full Name</th><th>Username</th><th>Email</th><th>Created</th><th class="no-print">Actions</th></tr></thead>
      <tbody>
        <?php if (empty($teachers)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No teacher accounts found.</td></tr>
        <?php endif; ?>
        <?php foreach ($teachers as $i => $t): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= e($t['full_name']) ?></td>
          <td><?= e($t['username']) ?></td>
          <td><?= e($t['email']) ?></td>
          <td><?= e(date('M j, Y', strtotime($t['created_at']))) ?></td>
          <td class="no-print">
            <a href="<?= BASE_URL ?>/teacher_form.php?id=<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil-square"></i></a>
            <a href="<?= BASE_URL ?>/teacher_delete.php?id=<?= (int)$t['id'] ?>&csrf_token=<?= Security::generateCsrfToken() ?>"
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
