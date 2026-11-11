<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
$user = currentUser();
if ($user) {
    try { writeAuditLog(getPDO(), (int)$user['user_id'], 'USER_LOGOUT', 'user', (int)$user['user_id']); }
    catch (Throwable $exception) { error_log('Logout audit error: ' . $exception->getMessage()); }
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
redirect('login.php');