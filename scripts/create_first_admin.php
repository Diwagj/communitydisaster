<?php
declare(strict_types=1);

// Run once from Command Prompt/PowerShell: php scripts/create_first_admin.php
// This script refuses to run if any user already exists.
require_once __DIR__ . '/../config/database.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.
");
}

try {
    $pdo = getPDO();
    $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        exit("Setup stopped: a user account already exists. Use the admin page to create additional accounts.
");
    }

    $roleStmt = $pdo->prepare("SELECT role_id FROM roles WHERE role_name = 'System Administrator' LIMIT 1");
    $roleStmt->execute();
    $roleId = $roleStmt->fetchColumn();
    if (!$roleId) exit("System Administrator role not found. Import the project SQL file first.
");

    $first = trim((string)readline('First name: '));
    $last = trim((string)readline('Last name: '));
    $username = trim((string)readline('Username (4-80 characters): '));
    $email = trim((string)readline('Email (optional): '));
    $password = (string)readline('Password (minimum 8 characters): ');

    if ($first === '' || $last === '' || !preg_match('/^[A-Za-z0-9._-]{4,80}$/', $username) || strlen($password) < 8 || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
        exit("Invalid input. No account was created. Run the script again and enter valid details.
");
    }

    $pdo->beginTransaction();
    $insert = $pdo->prepare("INSERT INTO users (username,email,password_hash,first_name,last_name,account_status) VALUES (?,?,?,?,?,'active')");
    $insert->execute([$username, $email !== '' ? $email : null, password_hash($password, PASSWORD_DEFAULT), $first, $last]);
    $userId = (int)$pdo->lastInsertId();
    $assign = $pdo->prepare('INSERT INTO user_roles (user_id,role_id,assigned_by) VALUES (?,?,NULL)');
    $assign->execute([$userId, (int)$roleId]);
    $pdo->commit();
    echo "First System Administrator created successfully. Sign in through /login.php.
";
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "Setup failed: " . $exception->getMessage() . "
");
    exit(1);
}
