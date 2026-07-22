<?php
$currentUser = Auth::currentUser();
$isAdmin = $currentUser instanceof Admin;
?>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/dashboard.php">
      <i class="bi bi-clipboard2-check-fill me-2"></i><?= APP_NAME ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/students.php"><i class="bi bi-people-fill me-1"></i>Students</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/classes_page.php"><i class="bi bi-easel-fill me-1"></i>Classes</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/attendance.php"><i class="bi bi-check2-square me-1"></i>Mark Attendance</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-bar-chart-line-fill me-1"></i>Reports</a></li>
        <?php if ($isAdmin): ?>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/teachers.php"><i class="bi bi-person-badge-fill me-1"></i>Teachers</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item d-flex align-items-center me-3 text-light">
          <i class="bi bi-person-circle me-1"></i>
          <?= e($currentUser ? $currentUser->getFullName() : 'Guest') ?>
          <span class="badge bg-light text-dark ms-2"><?= e($currentUser ? $currentUser->getRoleLabel() : 'Guest') ?></span>
        </li>
        <li class="nav-item">
          <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="container-fluid py-4 px-4">
<?php $flash = getFlash(); if ($flash): ?>
  <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
