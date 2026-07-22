<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (Auth::isLoggedIn()) {
    redirect('/dashboard.php');
} else {
    redirect('/auth/login.php');
}
