<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();

$user = Auth::currentUser();
$db = Database::getConnection();

// Determine which classes this user is allowed to mark attendance for
if ($user instanceof Admin) {
    $availableClasses = ClassRoom::all();
} else {
    $availableClasses = $user->getAssignedClasses();
}

$classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : (int)($availableClasses[0]['id'] ?? 0);
$date = $_GET['date'] ?? date('Y-m-d');

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid session token.';
    } else {
        $classId = (int)$_POST['class_id'];
        $date = $_POST['date'];

        $v = new Validator();
        $v->required($classId, 'class')->required($date, 'date')->date($date, 'date');

        // authorization check: teacher can only mark their own class
        $allowedIds = array_column($availableClasses, 'id');
        if (!in_array($classId, $allowedIds)) {
            $errors[] = 'You are not authorized to mark attendance for this class.';
        }

        if (empty($errors) && !$v->hasErrors()) {
            $statuses = $_POST['status'] ?? [];
            foreach ($statuses as $studentId => $status) {
                $record = new AttendanceRecord((int)$studentId, $classId, $date, $status, $user->getId());
                $record->create();
            }
            setFlash('success', 'Attendance saved for ' . date('F j, Y', strtotime($date)) . '.');
            redirect('/attendance.php?class_id=' . $classId . '&date=' . $date);
        } elseif ($v->hasErrors()) {
            $errors = array_merge($errors, array_values($v->getErrors()));
        }
    }
}

$students = $classId ? ClassRoom::getEnrolledStudents($classId) : [];
$existingStatuses = $classId ? AttendanceRecord::getForClassAndDate($classId, $date) : [];

$pageTitle = 'Mark Attendance';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<h4 class="mb-3"><i class="bi bi-check2-square me-2"></i>Mark Attendance</h4>

<?php foreach ($errors as $err): ?>
  <div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<?php if (empty($availableClasses)): ?>
  <div class="alert alert-warning">You have no classes assigned yet. Please contact an administrator.</div>
<?php else: ?>

<div class="card p-3 mb-3">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-md-6">
      <label class="form-label">Class</label>
      <select name="class_id" class="form-select" onchange="this.form.submit()">
        <?php foreach ($availableClasses as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $classId === (int)$c['id'] ? 'selected' : '' ?>>
            <?= e($c['class_code']) ?> - <?= e($c['class_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Date</label>
      <input type="date" name="date" class="form-control" value="<?= e($date) ?>" onchange="this.form.submit()">
    </div>
    <div class="col-md-2">
      <button class="btn btn-outline-primary w-100" type="submit">Load</button>
    </div>
  </form>
</div>

<?php if ($classId): ?>
<div class="card p-3">
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
    <input type="hidden" name="class_id" value="<?= $classId ?>">
    <input type="hidden" name="date" value="<?= e($date) ?>">

    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>#</th><th>Student</th><th>Present</th><th>Absent</th><th>Late</th><th>Excused</th></tr></thead>
        <tbody>
          <?php if (empty($students)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No students are enrolled in this class yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($students as $i => $s):
              $current = $existingStatuses[$s['id']] ?? 'present';
          ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($s['full_name']) ?> <span class="text-muted small">(<?= e($s['student_number']) ?>)</span></td>
            <?php foreach (['present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'excused' => 'info'] as $status => $color): ?>
            <td class="text-center">
              <input class="form-check-input" type="radio" name="status[<?= (int)$s['id'] ?>]" value="<?= $status ?>" <?= $current === $status ? 'checked' : '' ?>>
            </td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if (!empty($students)): ?>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill me-1"></i>Save Attendance</button>
    <?php endif; ?>
  </form>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="mt-3">
  <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
