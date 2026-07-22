<?php
require_once __DIR__ . '/User.php';

/**
 * Admin.php
 * OOP CONCEPT: Inheritance (extends User) + Polymorphism (overrides abstract methods)
 */
class Admin extends User
{
    public function __construct(int $id, string $username, string $email, string $fullName)
    {
        parent::__construct($id, $username, $email, $fullName, 'admin');
    }

    public function getRoleLabel(): string
    {
        return 'Administrator';
    }

    // Admin sees ALL statistics
    public function getDashboardWidgets(): array
    {
        return ['total_students', 'total_teachers', 'total_classes', 'today_attendance', 'manage_users'];
    }
}
