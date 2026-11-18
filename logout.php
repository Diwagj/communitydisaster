<?php
require_once __DIR__ . '/includes/auth.php';

// Remove the session data.
$_SESSION = [];

// Expire the session cookie in the browser.
if (ini_get('session.use_cookies')) {
    $cookieParameters = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $cookieParameters['path'],
        $cookieParameters['domain'],
        $cookieParameters['secure'],
        $cookieParameters['httponly']
    );
}

session_destroy();

redirect('index.php');
