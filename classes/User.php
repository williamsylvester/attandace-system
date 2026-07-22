<?php
require_once __DIR__ . '/Database.php';

/**
 * User.php (ABSTRACT CLASS)
 *
 * OOP CONCEPTS DEMONSTRATED:
 * - Abstraction: "abstract class" cannot be instantiated directly (you can never
 *   do "new User()"). It only exists to be extended.
 * - Encapsulation: properties are protected, accessed via getters.
 * - Inheritance: Admin and Teacher both "extends User".
 * - Polymorphism: getDashboardWidgets() and getRoleLabel() are declared here as
 *   abstract, and Admin / Teacher each provide their OWN version. When the
 *   dashboard calls $user->getDashboardWidgets(), PHP automatically runs the
 *   correct version depending on which subclass the object actually is.
 */
abstract class User
{
    protected int $id;
    protected string $username;
    protected string $email;
    protected string $fullName;
    protected string $role;
    protected PDO $db;

    public function __construct(int $id, string $username, string $email, string $fullName, string $role)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->fullName = $fullName;
        $this->role = $role;
        $this->db = Database::getConnection();
    }

    // ---------- Getters (encapsulation) ----------
    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getFullName(): string { return $this->fullName; }
    public function getRole(): string { return $this->role; }

    // Every subclass MUST implement this differently -> polymorphism
    abstract public function getRoleLabel(): string;

    // Every subclass decides which dashboard stat cards it can see -> polymorphism
    abstract public function getDashboardWidgets(): array;

    /**
     * Factory method: builds the correct subclass object (Admin or Teacher)
     * from a row fetched out of the "users" table.
     * This is a common OOP pattern used together with polymorphism.
     */
    public static function createFromRow(array $row): User
    {
        if ($row['role'] === 'admin') {
            return new Admin((int)$row['id'], $row['username'], $row['email'], $row['full_name']);
        }
        return new Teacher((int)$row['id'], $row['username'], $row['email'], $row['full_name']);
    }
}
