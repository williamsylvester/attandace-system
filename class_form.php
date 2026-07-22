<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();

$user = Auth::currentUser();
$isAdmin = $user instanceof Admin;

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$existing = $id ? ClassRoom::findById($id) : null;
if ($id && !$existing) {
    setFlash('error', 'Class not found.');
    redirect('/classes_page.php');
}

// Only admin can create a brand new class
if (!$id && !$isAdmin) {
    setFlash('error', 'Only an administrator can create new classes.');
    redirect('/classes_page.php');
}

$db = Database::getConnection();
$teachers = $db->query("SELECT id, full_name FROM users WHERE role='teacher' ORDER BY full_name")->fetchAll();

$errors = [];
$formData = [
    'class_name' => $existing['class_name'] ?? '',
    'class_code' => $existing['class_code'] ?? '',
    'teacher_id' => $existing['teacher_id'] ?? ($user instanceof Teacher ? $user->getId() : ''),
];

// ---------- Handle class details save (admin only) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_class'])) {
    if (!$isAdmin) {
        $errors[] = 'Only an administrator can modify class details.';
    } elseif (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid session token.';
    } else {
        $formData['class_name'] = trim($_POST['class_name'] ?? '');
        $formData['class_code'] = trim($_POST['class_code'] ?? '');
        $formData['teacher_id'] = (int)($_POST['teacher_id'] ?? 0);

        $v = new Validator();
        $v->required($formData['class_name'], 'class_name')
          ->required($formData['class_code'], 'class_code')
          ->required($formData['teacher_id'], 'teacher_id');

        if ($v->hasErrors()) {
            $errors = array_values($v->getErrors());
        } else {
            $classRoom = new ClassRoom($formData['class_name'], $formData['class_code'], $formData['teacher_id'], $id);
            $result = $id ? $classRoom->update() : $classRoom->create();
            if ($result !== false) {
                setFlash('success', $id ? 'Class updated.' : 'Class created.');
                redirect('/classes_page.php');
            } else {
                $errors[] = 'Could not save class. The class code may already exist.';
            }
        }
    }
}

// ---------- Handle enrollment changes ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_student']) && $id) {
    if (Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        ClassRoom::enrollStudent($id, (int)$_POST['student_id']);
        setFlash('success', 'Student enrolled.');
    }
    redirect('/class_form.php?id=' . $id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unenroll_student']) && $id) {
    if (Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        ClassRoom::unenrollStudent($id, (int)$_POST['student_id']);
        setFlash('success', 'Student removed from class.');
    }
    redirect('/class_form.php?id=' . $id);
}

$enrolledStudents = $id ? ClassRoom::getEnrolledStudents($id) : [];
$enrolledIds = array_column($enrolledStudents, 'id');
$allStudents = Student::all();
$availableStudents = array_filter($allStudents, fn($s) => !in_array($s['id'], $enrolledIds));

$pageTitle = $id ? 'Manage Class' : 'Add Class';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<h4 class="mb-3"><i class="bi bi-easel-fill me-2"></i><?= $id ? 'Manage Class' : 'Add New Class' ?></h4>

<?php foreach ($errors as $err): ?>
  <div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card p-4">
      <h6 class="fw-bold mb-3">Class Details</h6>
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
        <input type="hidden" name="save_class" value="1">
        <div class="mb-3">
          <label class="form-label">Class Code *</label>
          <input type="text" name="class_code" class="form-control" required value="<?= e($formData['class_code']) ?>" <?= !$isAdmin ? 'disabled' : '' ?>>
        </div>
        <div class="mb-3">
          <label class="form-label">Class Name *</label>
          <input type="text" name="class_name" class="form-control" required value="<?= e($formData['class_name']) ?>" <?= !$isAdmin ? 'disabled' : '' ?>>
        </div>
        <div class="mb-3">
          <label class="form-label">Assigned Teacher *</label>
          <select name="teacher_id" class="form-select" required <?= !$isAdmin ? 'disabled' : '' ?>>
            <option value="">-- select teacher --</option>
            <?php foreach ($teachers as $t): ?>
              <option value="<?= (int)$t['id'] ?>" <?= (int)$formData['teacher_id'] === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php if ($isAdmin): ?>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill me-1"></i>Save</button>
        <?php else: ?>
        <p class="text-muted small mb-0">Only an administrator can edit class details.</p>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <?php if ($id): ?>
  <div class="col-md-6">
    <div class="card p-4">
      <h6 class="fw-bold mb-3">Enroll a Student</h6>
      <form method="POST" class="d-flex gap-2 mb-3">
        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
        <select name="student_id" class="form-select" required>
          <option value="">-- select student --</option>
          <?php foreach ($availableStudents as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= e($s['full_name']) ?> (<?= e($s['student_number']) ?>)</option>
          <?php endforeach; ?>
        </select>
        <button type="submit" name="enroll_student" value="1" class="btn btn-outline-primary text-nowrap"><i class="bi bi-plus-lg"></i> Enroll</button>
      </form>

      <h6 class="fw-bold">Enrolled Students (<?= count($enrolledStudents) ?>)</h6>
      <ul class="list-group">
        <?php foreach ($enrolledStudents as $s): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= e($s['full_name']) ?> <span class="text-muted small">(<?= e($s['student_number']) ?>)</span>
            <form method="POST" class="m-0">
              <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
              <input type="hidden" name="student_id" value="<?= (int)$s['id'] ?>">
              <button type="submit" name="unenroll_student" value="1" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
            </form>
          </li>
        <?php endforeach; ?>
        <?php if (empty($enrolledStudents)): ?>
          <li class="list-group-item text-muted">No students enrolled yet.</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="mt-3">
  <a href="<?= BASE_URL ?>/classes_page.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Classes</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
