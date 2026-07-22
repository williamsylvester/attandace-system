<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$auth = new Auth();
$auth->logout();
redirect('/auth/login.php');
