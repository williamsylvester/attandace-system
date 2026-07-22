<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();

$user = Auth::currentUser();          // returns Admin or Teacher object (polymorphism)
$widgets = $user->getDashboardWidgets(); // each subclass returns its own list

$totalStudents = Student::countAll();
$totalClasses  = ClassRoom::countAll();
$todaySummary  = AttendanceRecord::getTodaySummary();

$db = Database::getConnection();
$totalTeachers = (int)$db->query("SELECT COUNT(*) c FROM users WHERE role='teacher'")->fetch()['c'];

$myClasses = [];
if ($user instanceof Teacher) {
    $myClasses = $user->getAssignedClasses();
}

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<h4 class="mb-1">Welcome, <?= e($user->getFullName()) ?> <span class="badge bg-secondary"><?= e($user->getRoleLabel()) ?></span></h4>
<p class="text-muted mb-4">Here's what's happening today, <?= date('l, F j, Y') ?>.</p>

<div class="row g-3 mb-4">
  <?php if (in_array('total_students', $widgets) || in_array('my_students', $widgets)): ?>
  <div class="col-md-3 col-sm-6">
    <div class="card stat-card bg-navy p-3">
      <div class="d-flex justify-content-between align-items-center">
        <div><div class="small">Total Students</div><h3 class="mb-0"><?= $totalStudents ?></h3></div>
        <i class="bi bi-people-fill stat-icon"></i>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array('total_teachers', $widgets)): ?>
  <div class="col-md-3 col-sm-6">
    <div class="card stat-card bg-purple p-3">
      <div class="d-flex justify-content-between align-items-center">
        <div><div class="small">Total Teachers</div><h3 class="mb-0"><?= $totalTeachers ?></h3></div>
        <i class="bi bi-person-badge-fill stat-icon"></i>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array('total_classes', $widgets) || in_array('my_classes', $widgets)): ?>
  <div class="col-md-3 col-sm-6">
    <div class="card stat-card bg-teal p-3">
      <div class="d-flex justify-content-between align-items-center">
        <div><div class="small"><?= $user instanceof Teacher ? 'My Classes' : 'Total Classes' ?></div>
        <h3 class="mb-0"><?= $user instanceof Teacher ? count($myClasses) : $totalClasses ?></h3></div>
        <i class="bi bi-easel-fill stat-icon"></i>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array('today_attendance', $widgets)): ?>
  <div class="col-md-3 col-sm-6">
    <div class="card stat-card bg-orange p-3">
      <div class="d-flex justify-content-between align-items-center">
        <div><div class="small">Present Today</div><h3 class="mb-0"><?= $todaySummary['present'] ?></h3></div>
        <i class="bi bi-check2-circle stat-icon"></i>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="fw-bold"><i class="bi bi-pie-chart-fill me-1"></i>Today's Attendance Summary</h6>
      <table class="table table-sm mb-0">
        <tr><td>Present</td><td class="text-end fw-bold text-success"><?= $todaySummary['present'] ?></td></tr>
        <tr><td>Absent</td><td class="text-end fw-bold text-danger"><?= $todaySummary['absent'] ?></td></tr>
        <tr><td>Late</td><td class="text-end fw-bold text-warning"><?= $todaySummary['late'] ?></td></tr>
        <tr><td>Excused</td><td class="text-end fw-bold text-info"><?= $todaySummary['excused'] ?></td></tr>
      </table>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="fw-bold"><i class="bi bi-lightning-fill me-1"></i>Quick Actions</h6>
      <div class="d-grid gap-2">
        <a href="<?= BASE_URL ?>/attendance.php" class="btn btn-outline-primary btn-sm text-start"><i class="bi bi-check2-square me-1"></i>Mark Today's Attendance</a>
        <a href="<?= BASE_URL ?>/students.php" class="btn btn-outline-secondary btn-sm text-start"><i class="bi bi-person-plus-fill me-1"></i>Manage Students</a>
        <a href="<?= BASE_URL ?>/reports.php" class="btn btn-outline-dark btn-sm text-start"><i class="bi bi-bar-chart-line-fill me-1"></i>View Reports</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
