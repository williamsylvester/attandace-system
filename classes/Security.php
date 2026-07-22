<?php
/**
 * Security.php
 * Utility class that centralises encryption/decryption of sensitive data
 * and CSRF token handling.
 *
 * OOP CONCEPT DEMONSTRATED: Abstraction
 * - Calling code only sees encrypt()/decrypt()/generateCsrfToken(); the
 *   messy openssl_* details are hidden inside the class.
 */
class Security
{
    // Encrypts sensitive data (e.g. a student's phone number) before it is stored
    public static function encrypt(string $plainText): string
    {
        $ivLength = openssl_cipher_iv_length(ENCRYPTION_METHOD);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($plainText, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
        // store iv together with the cipher text so we can decrypt later
        return base64_encode($iv . '::' . $encrypted);
    }

    // Decrypts data - only called from pages an authorised (logged-in) user can reach
    public static function decrypt(?string $encodedData): string
    {
        if (empty($encodedData)) {
            return '';
        }
        $decoded = base64_decode($encodedData);
        $parts = explode('::', $decoded, 2);
        if (count($parts) !== 2) {
            return ''; // not valid encrypted data
        }
        [$iv, $encrypted] = $parts;
        $result = openssl_decrypt($encrypted, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
        return $result === false ? '' : $result;
    }

    // ---------- CSRF protection ----------
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(?string $token): bool
    {
        return isset($_SESSION['csrf_token']) && $token !== null && hash_equals($_SESSION['csrf_token'], $token);
    }

    // Basic string sanitiser for output (prevents XSS when echoing user input)
    public static function clean(?string $value): string
    {
        return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
