<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| Session setup
|--------------------------------------------------------------------------
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/*
|--------------------------------------------------------------------------
| General helper functions
|--------------------------------------------------------------------------
*/

function h(?string $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['staff']) && is_array($_SESSION['staff']);
}

function currentStaff(): ?array
{
    return isLoggedIn() ? $_SESSION['staff'] : null;
}

/*
|--------------------------------------------------------------------------
| Authentication and authorization
|--------------------------------------------------------------------------
*/

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireRoles(array $allowedRoles): void
{
    requireLogin();

    $currentRole = currentStaff()['role'] ?? '';

    if (!in_array($currentRole, $allowedRoles, true)) {
        http_response_code(403);
        exit('Access denied. Your account cannot access this page.');
    }
}

function isSystemAdministrator(): bool
{
    return isLoggedIn()
        && (currentStaff()['role'] ?? '') === 'admin';
}

function roleName(string $role): string
{
    $roleNames = [
        'admin' => 'System Administrator',
        'bdrrmc' => 'BDRRMC Coordinator',
        'official' => 'Barangay Official',
        'responder' => 'CDRRMC Agent / Responder',
    ];

    return $roleNames[$role] ?? 'Staff';
}

/*
|--------------------------------------------------------------------------
| CSRF protection for forms
|--------------------------------------------------------------------------
*/

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(): void
{
    $submittedToken = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submittedToken)
        || !hash_equals(csrfToken(), $submittedToken)
    ) {
        http_response_code(400);
        exit('Invalid form submission. Refresh the page and try again.');
    }
}
