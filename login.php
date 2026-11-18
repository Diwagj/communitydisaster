<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $errorMessage = 'Enter your username and password.';
    } else {
        try {
            $sql = '
                SELECT
                    user_id,
                    full_name,
                    username,
                    password_hash,
                    role,
                    is_active
                FROM users
                WHERE username = :username
                LIMIT 1
            ';

            $statement = db()->prepare($sql);
            $statement->execute(['username' => $username]);

            $user = $statement->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $errorMessage = 'The username or password is incorrect.';
            } elseif ((int) $user['is_active'] !== 1) {
                $errorMessage = 'This account is inactive. Contact the System Administrator.';
            } else {
                session_regenerate_id(true);

                $_SESSION['staff'] = [
                    'user_id' => (int) $user['user_id'],
                    'full_name' => $user['full_name'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                ];

                redirect('dashboard.php');
            }
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $errorMessage = 'Unable to sign in. Please check the database connection.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Login | Surong Guisad</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php
    $activePage = '';
    require __DIR__ . '/includes/public-header.php';
    ?>

    <main class="auth-wrap">
        <section class="auth-card">
            <div class="kicker">Authorized personnel only</div>
            <h1>Staff Login</h1>

            <p class="muted">
                Sign in using an account created by the System Administrator.
            </p>

            <?php if ($errorMessage !== ''): ?>
                <div class="notice error">
                    <?= h($errorMessage) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= h(csrfToken()) ?>"
                >

                <div class="field">
                    <label for="username">Username</label>
                    <input
                        id="username"
                        name="username"
                        maxlength="50"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button class="button" type="submit">Sign in</button>
            </form>

            <p class="small" style="margin-top: 18px;">
                Public visitors do not need an account.
                <a href="index.php" style="color: #16768c;">
                    Return to public portal
                </a>.
            </p>
        </section>
    </main>
</body>
</html>
