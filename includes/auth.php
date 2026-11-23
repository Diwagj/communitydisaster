<?php
declare(strict_types=1);


ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';


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

function auth(): Auth
{
    static $authInstance = null;

    if ($authInstance instanceof Auth) {
        return $authInstance;
    }

    $authInstance = new Auth(db());
    return $authInstance;
}


function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function isLoggedIn(): bool
{
    return auth()->isLoggedIn();
}

function currentStaff(): ?array
{
    return auth()->currentStaff();
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function isSystemAdministrator(): bool
{
    return auth()->isSystemAdministrator();
}

function roleName(string $role): string
{
    return auth()->roleName($role);
}

function hasPermission(string $permission): bool
{
    return auth()->hasPermission($permission);
}

function requirePermission(string $permission): void
{
    requireLogin();

    if (!hasPermission($permission)) {
        http_response_code(403);
        exit('Access denied. Your account has not been granted this permission.');
    }
}

function requireRoles(array $allowedRoles): void
{
    requireLogin();

    if (!isSystemAdministrator() && !in_array(currentStaff()['role'] ?? '', $allowedRoles, true)) {
        http_response_code(403);
        exit('Access denied.');
    }
}

function csrfToken(): string
{
    return auth()->csrfToken();
}

function verifyCsrfToken(): void
{
    auth()->verifyCsrfToken();
}

function auditLog(string $action, string $entity, ?int $entityId, string $details = ''): void
{
    auth()->auditLog($action, $entity, $entityId, $details);
}


function formatRef(string $prefix, int $id): string
{
    return $prefix . '-' . date('Y') . '-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
}