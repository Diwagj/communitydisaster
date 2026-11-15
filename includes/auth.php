<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'secure' => $https,
        'httponly' => true, 'samesite' => 'Lax'
    ]);
    session_start();
}

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function go(string $url): never
{
    header('Location: ' . $url);
    exit;
}
function signedIn(): bool
{
    return isset($_SESSION['staff']) && is_array($_SESSION['staff']);
}
function staff(): ?array
{
    return signedIn() ? $_SESSION['staff'] : null;
}
function requireStaff(): void
{
    if (!signedIn()) {
        go('login.php');
    }
}
function requireRoles(array $allowed): void
{
    requireStaff();
    if (!in_array(staff()['role'] ?? '', $allowed, true)) {
        http_response_code(403);
        exit('Access denied. Your account does not have permission to view this page.');
    }
}
function isAdmin(): bool
{
    return signedIn() && (staff()['role'] ?? '') === 'admin';
}
function csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function checkCsrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf(), $token)) {
        http_response_code(400);
        exit('Invalid form submission. Return to the page, refresh it, and try again.');
    }
}
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function takeFlash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
function roleLabel(string $role): string
{
    return [
        'admin' => 'System Administrator',
        'bdrrmc' => 'BDRRMC Coordinator',
        'official' => 'Barangay Official',
        'responder' => 'CDRRMC Agent / Responder',
    ][$role] ?? 'Staff';
}
function publicHeader(string $active = ''): void
{
    $links = [
        'announcements.php' => 'Announcements',
        'evacuation-centers.php' => 'Evacuation Centers',
        'report-incident.php' => 'Report Incident',
    ];
    echo '<header class="top"><div class="wrap top-inner">';
    echo '<a class="brand" href="index.php"><img src="assets/logo.png" alt="Barangay logo"><span><b>BARANGAY SURONG GUISAD</b><small>Disaster Response Portal</small></span></a>';
    echo '<nav class="public-nav">';
    foreach ($links as $url => $label) {
        echo '<a href="' . h($url) . '"' . ($active === $url ? ' class="current"' : '') . '>' . h($label) . '</a>';
    }
    echo '</nav></div></header>';
}
function staffHeader(string $pageTitle): void
{
    $s = staff();
    echo '<header class="top"><div class="wrap top-inner">';
    echo '<a class="brand" href="dashboard.php"><img src="assets/logo.png" alt="Barangay logo"><span><b>SURONG GUISAD DRRM</b><small>Staff Management Portal</small></span></a>';
    echo '<div class="staff-id"><span>' . h($s['full_name'] ?? '') . ' · ' . h(roleLabel($s['role'] ?? '')) . '</span><a class="button button-light" href="logout.php">Sign out</a></div>';
    echo '</div></header>';
}
function staffNav(): void
{
    $role = staff()['role'] ?? '';
    $items = [
        ['dashboard.php','Dashboard',['admin','bdrrmc','official','responder']],
        ['incidents.php','Incident Reports',['admin','bdrrmc','official','responder']],
        ['response.php','Emergency Response',['admin','bdrrmc','responder']],
        ['evacuation.php','Evacuation Management',['admin','bdrrmc','official','responder']],
        ['households.php','Household Registry',['admin']],
        ['inventory.php','Relief Inventory',['admin','bdrrmc']],
        ['distribution.php','Relief Distribution',['admin','bdrrmc','official']],
        ['manage-announcements.php','Announcements',['admin','bdrrmc','official']],
        ['reports.php','Disaster Reports',['admin','bdrrmc','official']],
        ['users.php','User Accounts',['admin']],
    ];
    echo '<aside class="side"><div class="side-title">SYSTEM MODULES</div>';
    foreach ($items as [$url,$label,$roles]) {
        if (in_array($role, $roles, true)) {
            echo '<a href="' . h($url) . '">' . h($label) . '</a>';
        } else {
            echo '<span class="disabled" title="Your account does not have access">' . h($label) . ' <small>🔒</small></span>';
        }
    }
    echo '<div class="side-note">Greyed-out modules are unavailable for your role. Access is also checked by PHP on every page.</div></aside>';
}
function pageStart(string $title): void
{
    staffHeader($title);
    echo '<div class="wrap staff-layout">';
    staffNav();
    echo '<main class="content"><div class="crumb">Staff Portal / ' . h($title) . '</div><h1>' . h($title) . '</h1>';
}
function pageEnd(): void
{
    echo '</main></div><footer class="footer"><div class="wrap">Barangay Surong Guisad · Community Disaster Risk Reduction and Emergency Response Management System</div></footer>';
}
