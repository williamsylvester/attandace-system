<?php
/**
 * functions.php - small procedural helpers used across pages
 */

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function e(?string $value): string
{
    return Security::clean($value);
}
