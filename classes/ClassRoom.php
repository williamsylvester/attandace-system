<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/interfaces/Crudable.php';

/**
 * ClassRoom.php  (named ClassRoom because "class" is a reserved PHP keyword)
 * Represents a subject/class group taught by one teacher.
 *
 * OOP CONCEPT: Encapsulation + implements Crudable (same interface Student
 * uses) -> demonstrates POLYMORPHISM: different classes, same method names,
 * different internal SQL/behaviour.
 */
class ClassRoom implements Crudable
{
    private ?int $id;
    private string $className;
    private string $classCode;
    private int $teacherId;
    private PDO $db;

    public function __construct(string $className, string $classCode, int $teacherId, ?int $id = null)
    {
        $this->className = $className;
        $this->classCode = $classCode;
        $this->teacherId = $teacherId;
        $this->id = $id;
        $this->db = Database::getConnection();
    }

    public function getId(): ?int { return $this->id; }
    public function getClassName(): string { return $this->className; }
    public function getClassCode(): string { return $this->classCode; }
    public function getTeacherId(): int { return $this->teacherId; }

    public function create()
    {
        $stmt = $this->db->prepare("INSERT INTO classes (class_name, class_code, teacher_id) VALUES (:cn, :cc, :tid)");
        $ok = $stmt->execute(['cn' => $this->className, 'cc' => $this->classCode, 'tid' => $this->teacherId]);
        if ($ok) {
            $this->id = (int)$this->db->lastInsertId();
            return $this->id;
        }
        return false;
    }

    public function update(): bool
    {
        if (!$this->id) return false;
        $stmt = $this->db->prepare("UPDATE classes SET class_name = :cn, class_code = :cc, teacher_id = :tid WHERE id = :id");
        return $stmt->execute(['cn' => $this->className, 'cc' => $this->classCode, 'tid' => $this->teacherId, 'id' => $this->id]);
    }

    public function delete(): bool
    {
        if (!$this->id) return false;
        $stmt = $this->db->prepare("DELETE FROM classes WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT c.*, u.full_name AS teacher_name FROM classes c
                JOIN users u ON u.id = c.teacher_id WHERE c.id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(): array
    {
        $db = Database::getConnection();
        return $db->query("SELECT c.*, u.full_name AS teacher_name FROM classes c
                JOIN users u ON u.id = c.teacher_id ORDER BY c.class_name")->fetchAll();
    }

    public static function countAll(): int
    {
        $db = Database::getConnection();
        return (int)$db->query("SELECT COUNT(*) AS c FROM classes")->fetch()['c'];
    }

    // Students enrolled in this class
    public static function getEnrolledStudents(int $classId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT s.* FROM students s
                JOIN enrollments e ON e.student_id = s.id
                WHERE e.class_id = :cid ORDER BY s.full_name");
        $stmt->execute(['cid' => $classId]);
        return $stmt->fetchAll();
    }

    public static function enrollStudent(int $classId, int $studentId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT IGNORE INTO enrollments (class_id, student_id) VALUES (:cid, :sid)");
        return $stmt->execute(['cid' => $classId, 'sid' => $studentId]);
    }

    public static function unenrollStudent(int $classId, int $studentId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM enrollments WHERE class_id = :cid AND student_id = :sid");
        return $stmt->execute(['cid' => $classId, 'sid' => $studentId]);
    }
}
