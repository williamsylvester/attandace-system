<?php
/**
 * bootstrap.php
 * Single entry point included at the top of every page.
 * Loads configuration, all OOP classes, and helper functions.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Security.php';
require_once __DIR__ . '/../classes/Validator.php';
require_once __DIR__ . '/../classes/interfaces/Crudable.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Teacher.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Student.php';
require_once __DIR__ . '/../classes/ClassRoom.php';
require_once __DIR__ . '/../classes/AttendanceRecord.php';
require_once __DIR__ . '/functions.php';
