<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (currentUser()) {
    redirect(in_array('System Administrator', currentUser()['roles'] ?? [], true) ? 'admin-create-user.php' : 'index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $login = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($login === '' || $password === '') {
        $error = 'Enter your username or email and password.';
    } else {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT user_id, username, email, password_hash, first_name, last_name, account_status FROM users WHERE username = :login OR email = :email LIMIT 1");
            $stmt->execute(['login' => $login, 'email' => $login]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'The username/email or password is incorrect.';
            } elseif ($user['account_status'] !== 'active') {
                $error = match ($user['account_status']) {
                    'pending' => 'Your account is pending activation. Contact the System Administrator.',
                    'inactive' => 'Your account is inactive. Contact the System Administrator.',
                    'locked' => 'Your account is locked. Contact the System Administrator.',
                    default => 'This account cannot sign in. Contact the System Administrator.',
                };
            } else {
                $roleStmt = $pdo->prepare('SELECT r.role_name FROM user_roles ur INNER JOIN roles r ON r.role_id = ur.role_id WHERE ur.user_id = ?');
                $roleStmt->execute([(int)$user['user_id']]);
                $roles = array_column($roleStmt->fetchAll(), 'role_name');

                if (!$roles) {
                    $error = 'No system role is assigned to this account. Contact the System Administrator.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'user_id' => (int)$user['user_id'],
                        'username' => $user['username'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'roles' => $roles,
                    ];
                    $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE user_id = ?')->execute([(int)$user['user_id']]);
                    writeAuditLog($pdo, (int)$user['user_id'], 'USER_LOGIN', 'user', (int)$user['user_id']);
                    redirect(in_array('System Administrator', $roles, true) ? 'admin-create-user.php' : 'index.php');
                }
            }
        } catch (Throwable $exception) {
            error_log('Login error: ' . $exception->getMessage());
            $error = 'The system could not complete sign-in. Check the database connection or contact the administrator.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in | Surong Guisad DRRM</title>
  <script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="assets/app.css">
</head>
<body>
<div class="auth-shell">
  <aside class="auth-aside">
    <a class="brand" href="index.php"><div class="brand-mark"><img src="assets/logo.png" alt="Barangay Surong Guisad logo"></div><div><div class="brand-name">SURONG GUISAD</div><div class="brand-sub">Disaster Response Portal</div></div></a>
    <div class="aside-copy">
      <div class="eyebrow"><span class="eyebrow-dot"></span> Secure personnel access</div>
      <h1>Coordinate response with clarity and confidence.</h1>
      <p>Sign in using your authorized account to access the tools and information assigned to your role.</p>
    </div>
    <div class="aside-note">Community Disaster Risk Reduction and Emergency Response Management System<br>Barangay Surong Guisad</div>
  </aside>
  <main class="auth-main">
    <div class="auth-card">
      <a class="back-link" href="index.php">← Back to homepage</a>
      <div class="form-eyebrow">Welcome back</div>
      <h2>Sign in to your account</h2>
      <p class="form-intro">Enter your account credentials to continue to the system.</p>
      <?php if ($error !== ''): ?><div class="form-errors" role="alert"><?= e($error) ?></div><?php endif; ?>
      <form method="post" action="login.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <div class="field"><label for="username">Username or email</label><input id="username" name="username" type="text" placeholder="Enter your username or email" autocomplete="username" required value="<?= e($_POST['username'] ?? '') ?>"></div>
        <div class="field"><label for="password">Password</label><div class="password-wrap"><input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required><button class="show-password" type="button" data-toggle-password="password">SHOW</button></div></div>
        <div class="form-row"><span></span><span class="field-help">For password assistance, contact the System Administrator.</span></div>
        <button class="btn btn-primary btn-block" type="submit">Sign in <span aria-hidden="true">→</span></button>
      </form>
      <div class="auth-bottom">Access is restricted to authorized users. If you need an account, contact the system administrator or authorized barangay personnel.</div>
    </div>
  </main>
</div>
<script src="assets/app.js"></script>
</body></html>
