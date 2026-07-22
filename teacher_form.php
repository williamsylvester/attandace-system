<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireAdmin();

$db = Database::getConnection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$existing = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id AND role='teacher'");
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        setFlash('error', 'Teacher not found.');
        redirect('/teachers.php');
    }
}

$errors = [];
$formData = [
    'full_name' => $existing['full_name'] ?? '',
    'username'  => $existing['username'] ?? '',
    'email'     => $existing['email'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid session token.';
    } else {
        $formData['full_name'] = trim($_POST['full_name'] ?? '');
        $formData['username']  = trim($_POST['username'] ?? '');
        $formData['email']     = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $v = new Validator();
        $v->required($formData['full_name'], 'full_name')
          ->required($formData['username'], 'username')
          ->required($formData['email'], 'email')
          ->email($formData['email'], 'email');

        if (!$id) {
            $v->required($password, 'password')->minLength($password, 6, 'password');
        } elseif (!empty($password)) {
            $v->minLength($password, 6, 'password');
        }

        if ($v->hasErrors()) {
            $errors = array_values($v->getErrors());
        } else {
            try {
                if ($id) {
                    if (!empty($password)) {
                        $stmt = $db->prepare("UPDATE users SET full_name=:fn, username=:un, email=:em, password_hash=:ph WHERE id=:id");
                        $stmt->execute([
                            'fn' => $formData['full_name'], 'un' => $formData['username'],
                            'em' => $formData['email'], 'ph' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id
                        ]);
                    } else {
                        $stmt = $db->prepare("UPDATE users SET full_name=:fn, username=:un, email=:em WHERE id=:id");
                        $stmt->execute(['fn' => $formData['full_name'], 'un' => $formData['username'], 'em' => $formData['email'], 'id' => $id]);
                    }
                    setFlash('success', 'Teacher account updated.');
                } else {
                    $stmt = $db->prepare("INSERT INTO users (full_name, username, email, password_hash, role) VALUES (:fn,:un,:em,:ph,'teacher')");
                    $stmt->execute([
                        'fn' => $formData['full_name'], 'un' => $formData['username'],
                        'em' => $formData['email'], 'ph' => password_hash($password, PASSWORD_DEFAULT)
                    ]);
                    setFlash('success', 'Teacher account created.');
                }
                redirect('/teachers.php');
            } catch (PDOException $e) {
                $errors[] = 'Could not save teacher. The username or email may already be in use.';
            }
        }
    }
}

$pageTitle = $id ? 'Edit Teacher' : 'Add Teacher';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<h4 class="mb-3"><i class="bi bi-person-badge-fill me-2"></i><?= $id ? 'Edit Teacher Account' : 'Add New Teacher' ?></h4>

<div class="card p-4" style="max-width:600px;">
  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger py-2"><?= e($err) ?></div>
  <?php endforeach; ?>

  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
    <div class="mb-3">
      <label class="form-label">Full Name *</label>
      <input type="text" name="full_name" class="form-control" required value="<?= e($formData['full_name']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Username *</label>
      <input type="text" name="username" class="form-control" required value="<?= e($formData['username']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Email *</label>
      <input type="email" name="email" class="form-control" required value="<?= e($formData['email']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Password <?= $id ? '(leave blank to keep current)' : '*' ?></label>
      <input type="password" name="password" class="form-control" <?= $id ? '' : 'required' ?> minlength="6">
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill me-1"></i>Save</button>
      <a href="<?= BASE_URL ?>/teachers.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to List</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
