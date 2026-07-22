<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (Auth::isLoggedIn()) {
    redirect('/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid session token. Please try again.';
    } else {
        $login = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $v = new Validator();
        $v->required($login, 'login')->required($password, 'password');

        if ($v->hasErrors()) {
            $errors = array_values($v->getErrors());
        } else {
            $auth = new Auth();
            if ($auth->attemptLogin($login, $password)) {
                redirect('/dashboard.php');
            } else {
                $errors[] = 'Invalid email/username or password.';
            }
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="login-wrapper">
  <div class="card login-card p-4">
    <div class="card-body">
      <div class="text-center mb-4">
        <i class="bi bi-clipboard2-check-fill" style="font-size:2.5rem;color:#1e3a5f;"></i>
        <h4 class="fw-bold mt-2 mb-0"><?= APP_NAME ?></h4>
        <p class="text-muted small">Sign in to continue</p>
      </div>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger py-2"><?= e($err) ?></div>
      <?php endforeach; ?>

      <form method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?= Security::generateCsrfToken() ?>">
        <div class="mb-3">
          <label class="form-label">Email address or username</label>
          <input type="text" name="email" class="form-control" required autofocus
                 placeholder="admin@school.edu or admin"
                 value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right me-1"></i>Login</button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
