<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/interfaces/Crudable.php';

/**
 * AttendanceRecord.php
 * One row = one student's attendance status for one class on one date.
 *
 * OOP CONCEPT: Encapsulation + Crudable interface (polymorphism, 3rd class
 * implementing the same contract as Student and ClassRoom).
 */
class AttendanceRecord implements Crudable
{
    private ?int $id;
    private int $studentId;
    private int $classId;
    private string $attendanceDate; // Y-m-d
    private string $status;         // present | absent | late | excused
    private int $recordedBy;
    private PDO $db;

    public function __construct(int $studentId, int $classId, string $attendanceDate, string $status, int $recordedBy, ?int $id = null)
    {
        $this->studentId = $studentId;
        $this->classId = $classId;
        $this->attendanceDate = $attendanceDate;
        $this->status = $status;
        $this->recordedBy = $recordedBy;
        $this->id = $id;
        $this->db = Database::getConnection();
    }

    public function getId(): ?int { return $this->id; }
    public function getStatus(): string { return $this->status; }

    public function create()
    {
        // Prevent duplicate attendance for same student/class/date - update instead
        $stmt = $this->db->prepare("SELECT id FROM attendance WHERE student_id = :sid AND class_id = :cid AND attendance_date = :dt");
        $stmt->execute(['sid' => $this->studentId, 'cid' => $this->classId, 'dt' => $this->attendanceDate]);
        $existing = $stmt->fetch();

        if ($existing) {
            $this->id = (int)$existing['id'];
            $this->update();
            return $this->id;
        }

        $stmt = $this->db->prepare("INSERT INTO attendance (student_id, class_id, attendance_date, status, recorded_by)
                VALUES (:sid, :cid, :dt, :st, :rb)");
        $ok = $stmt->execute([
            'sid' => $this->studentId,
            'cid' => $this->classId,
            'dt'  => $this->attendanceDate,
            'st'  => $this->status,
            'rb'  => $this->recordedBy,
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
        $stmt = $this->db->prepare("UPDATE attendance SET status = :st, recorded_by = :rb WHERE id = :id");
        return $stmt->execute(['st' => $this->status, 'rb' => $this->recordedBy, 'id' => $this->id]);
    }

    public function delete(): bool
    {
        if (!$this->id) return false;
        $stmt = $this->db->prepare("DELETE FROM attendance WHERE id = :id");
        return $stmt->execute(['id' => $this->id]);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM attendance WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Get all attendance for a specific class + date (used to pre-fill the marking form)
    public static function getForClassAndDate(int $classId, string $date): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM attendance WHERE class_id = :cid AND attendance_date = :dt");
        $stmt->execute(['cid' => $classId, 'dt' => $date]);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['student_id']] = $r['status'];
        }
        return $map;
    }

    // Report search: filter by class, date range and/or status
    public static function search(?int $classId, ?string $from, ?string $to, ?string $status): array
    {
        $db = Database::getConnection();
        $sql = "SELECT a.*, s.full_name AS student_name, s.student_number, c.class_name
                FROM attendance a
                JOIN students s ON s.id = a.student_id
                JOIN classes c ON c.id = a.class_id
                WHERE 1=1";
        $params = [];

        if ($classId) {
            $sql .= " AND a.class_id = :cid";
            $params['cid'] = $classId;
        }
        if ($from) {
            $sql .= " AND a.attendance_date >= :from";
            $params['from'] = $from;
        }
        if ($to) {
            $sql .= " AND a.attendance_date <= :to";
            $params['to'] = $to;
        }
        if ($status) {
            $sql .= " AND a.status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY a.attendance_date DESC, s.full_name";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Dashboard stat: how many present/absent today
    public static function getTodaySummary(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT status, COUNT(*) AS total FROM attendance WHERE attendance_date = :today GROUP BY status");
        $stmt->execute(['today' => date('Y-m-d')]);
        $rows = $stmt->fetchAll();
        $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
        foreach ($rows as $r) {
            $summary[$r['status']] = (int)$r['total'];
        }
        return $summary;
    }
}
