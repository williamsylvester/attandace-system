<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/interfaces/Crudable.php';

/**
 * Student.php
 * OOP CONCEPTS:
 * - Encapsulation: all properties are private; only accessible via getters/setters.
 * - Implements the Crudable interface -> guarantees create/update/delete/findById exist.
 * - The phone number is ENCRYPTED before it is saved (setPhone stores plain text in the
 *   object, but create()/update() encrypt it right before writing to the database).
 */
class Student implements Crudable
{
    private ?int $id;
    private string $studentNumber;
    private string $fullName;
    private string $email;
    private string $phone;      // kept in memory as plain text
    private string $courseName;
    private string $yearLevel;
    private PDO $db;

    public function __construct(
        string $studentNumber,
        string $fullName,
        string $email,
        string $phone = '',
        string $courseName = '',
        string $yearLevel = '',
        ?int $id = null
    ) {
        $this->studentNumber = $studentNumber;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->phone = $phone;
        $this->courseName = $courseName;
        $this->yearLevel = $yearLevel;
        $this->id = $id;
        $this->db = Database::getConnection();
    }

    // ---------- Getters ----------
    public function getId(): ?int { return $this->id; }
    public function getStudentNumber(): string { return $this->studentNumber; }
    public function getFullName(): string { return $this->fullName; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): string { return $this->phone; }
    public function getCourseName(): string { return $this->courseName; }
    public function getYearLevel(): string { return $this->yearLevel; }

    // ---------- Setters ----------
    public function setId(int $id): void { $this->id = $id; }

    // ---------- CRUD (Crudable interface implementation) ----------
    public function create()
    {
        $sql = "INSERT INTO students (student_number, full_name, email, phone_encrypted, course_name, year_level)
                VALUES (:sn, :fn, :em, :ph, :cn, :yl)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            'sn' => $this->studentNumber,
            'fn' => $this->fullName,
            'em' => $this->email,
            'ph' => Security::encrypt($this->phone),
            'cn' => $this->courseName,
            'yl' => $this->yearLevel,
        ]);
        if ($ok) {
            $this->id = (int)$this->db->lastInsertId();
            return $this->id;
        }
        return false;
    }

    public function update(): bool
    {
        if (!$this->id) return false;
        $sql = "UPDATE students SET student_number = :sn, full_name = :fn, email = :em,
                phone_encrypted = :ph, course_name = :cn, year_level = :yl WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'sn' => $this->studentNumber,
            'fn' => $this->fullName,
            'em' => $this->email,
            'ph' => Security::encrypt($this->phone),
            'cn' => $this->courseName,
            'yl' => $this->yearLevel,
            'id' => $this->id,
        ]);
    }

    public function delete(): bool
    {
        if (!$this->id) return false;
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM students WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['phone_decrypted'] = Security::decrypt($row['phone_encrypted']);
        return $row;
    }

    // ---------- Extra query helpers ----------
    public static function all(): array
    {
        $db = Database::getConnection();
        $rows = $db->query("SELECT * FROM students ORDER BY full_name")->fetchAll();
        foreach ($rows as &$r) {
            $r['phone_decrypted'] = Security::decrypt($r['phone_encrypted']);
        }
        return $rows;
    }

    public static function search(string $keyword): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM students
                WHERE full_name LIKE :kw OR student_number LIKE :kw OR course_name LIKE :kw
                ORDER BY full_name");
        $stmt->execute(['kw' => '%' . $keyword . '%']);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['phone_decrypted'] = Security::decrypt($r['phone_encrypted']);
        }
        return $rows;
    }

    public static function countAll(): int
    {
        $db = Database::getConnection();
        return (int)$db->query("SELECT COUNT(*) AS c FROM students")->fetch()['c'];
    }
}
