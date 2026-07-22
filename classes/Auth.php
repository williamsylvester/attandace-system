<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Admin.php';
require_once __DIR__ . '/Teacher.php';

/**
 * Auth.php
 * Handles login, logout and "who is logged in" logic.
 *
 * OOP CONCEPT: Encapsulation (db connection is private) + uses polymorphism
 * via User::createFromRow() to return the right subclass.
 */
class Auth
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Attempts to log a user in. Returns true/false.
    public function attemptLogin(string $login, string $password): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :login_email OR username = :login_username LIMIT 1");
        $stmt->execute([
            'login_email' => $login,
            'login_username' => $login,
        ]);
        $row = $stmt->fetch();

        if ($row && password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id']  = $row['id'];
            $_SESSION['role']     = $row['role'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['full_name'] = $row['full_name'];
            return true;
        }
        return false;
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/auth/login.php');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        }
    }

    // Returns the current logged-in user as an Admin or Teacher OBJECT (polymorphism)
    public static function currentUser(): ?User
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $row = $stmt->fetch();
        return $row ? User::createFromRow($row) : null;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }
}
