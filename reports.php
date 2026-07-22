<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();

$user = Auth::currentUser();
$classes = $user instanceof Admin ? ClassRoom::all() : $user->getAssignedClasses();
$allowedClassIds = array_column($classes, 'id');

$classId = isset($_GET['class_id']) && $_GET['class_id'] !== '' ? (int)$_GET['class_id'] : null;
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$status = $_GET['status'] ?? '';

// authorization: teacher cannot query a class they don't own
if ($classId && !in_array($classId, $allowedClassIds)) {
    $classId = null;
}

$records = [];
$searched = isset($_GET['search']);
if ($searched) {
    if ($user instanceof Admin) {
        $records = AttendanceRecord::search($classId, $from ?: null, $to ?: null, $status ?: null);
    } else {
        // Teacher: restrict to their own classes only
        $all = AttendanceRecord::search($classId, $from ?: null, $to ?: null, $status ?: null);
        $records = array_filter($all, fn($r) => in_array($r['class_id'], $allowedClassIds));
    }
}

$pageTitle = 'Reports';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<h4 class="mb-3 no-print"><i class="bi bi-bar-chart-line-fill me-2"></i>Attendance Reports</h4>

<div class="card p-3 mb-3 no-print">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label">Class</label>
      <select name="class_id" class="form-select">
        <option value="">All my classes</option>
        <?php foreach ($classes as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $classId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['class_code']) ?> - <?= e($c['class_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">From</label>
      <input type="date" name="from" class="form-control" value="<?= e($from) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label">To</label>
      <input type="date" name="to" class="form-control" value="<?= e($to) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="">Any</option>
        <?php foreach (['present','absent','late','excused'] as $s): ?>
          <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button type="submit" name="search" value="1" class="btn btn-outline-primary flex-fill"><i class="bi bi-search me-1"></i>Search</button>
      <a href="<?= BASE_URL ?>/reports.php" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>
</div>

<?php if ($searched): ?>
<div class="card p-3">
  <div class="d-flex justify-content-between align-items-center mb-2 no-print">
    <h6 class="mb-0">Results (<?= count($records) ?>)</h6>
    <button onclick="window.print()" class="btn btn-outline-dark btn-sm"><i class="bi bi-printer-fill me-1"></i>Print</button>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Date</th><th>Student No.</th><th>Student Name</th><th>Class</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (empty($records)): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No records match your search.</td></tr>
        <?php endif; ?>
        <?php foreach ($records as $r): ?>
        <tr>
          <td><?= e(date('M j, Y', strtotime($r['attendance_date']))) ?></td>
          <td><?= e($r['student_number']) ?></td>
          <td><?= e($r['student_name']) ?></td>
          <td><?= e($r['class_name']) ?></td>
          <td>
            <?php $colors = ['present'=>'success','absent'=>'danger','late'=>'warning','excused'=>'info']; ?>
            <span class="badge bg-<?= $colors[$r['status']] ?? 'secondary' ?>"><?= ucfirst($r['status']) ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="mt-3 no-print">
  <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
