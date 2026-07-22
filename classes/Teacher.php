<?php
require_once __DIR__ . '/User.php';

/**
 * Teacher.php
 * OOP CONCEPT: Inheritance (extends User) + Polymorphism (overrides abstract methods)
 */
class Teacher extends User
{
    public function __construct(int $id, string $username, string $email, string $fullName)
    {
        parent::__construct($id, $username, $email, $fullName, 'teacher');
    }

    public function getRoleLabel(): string
    {
        return 'Teacher';
    }

    // Teacher sees only THEIR classes' statistics (no user management)
    public function getDashboardWidgets(): array
    {
        return ['my_classes', 'my_students', 'today_attendance'];
    }

    // Extra method unique to Teacher (not in Admin) - normal OOP specialisation
    public function getAssignedClasses(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE teacher_id = :tid ORDER BY class_name");
        $stmt->execute(['tid' => $this->id]);
        return $stmt->fetchAll();
    }
}
