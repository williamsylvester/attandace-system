<?php
/**
 * Database.php
 * A SIMPLE, single-responsibility class that opens one PDO connection
 * and hands it out to every other class that needs it (Singleton pattern).
 *
 * OOP CONCEPT DEMONSTRATED: Encapsulation
 * - The PDO connection ($connection) is PRIVATE. Nothing outside this class
 *   can touch it directly; other classes must go through getConnection().
 */
class Database
{
    private static ?Database $instance = null; // holds the single instance
    private PDO $connection;                    // encapsulated / hidden connection

    // Constructor is private so no other code can do "new Database()"
    private function __construct()
    {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements = SQL injection safety
        ];

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    // Only way to get access to the connection (simple, one-line usage)
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }
}
