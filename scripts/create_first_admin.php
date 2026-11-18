<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

// This script must only be run from PowerShell or Command Prompt.
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script can only run through the command line.\n");
    exit(1);
}

/**
 * Reads one line from the command-line terminal.
 */
function prompt(string $message): string
{
    echo $message;

    return trim((string) fgets(STDIN));
}

try {
    // Connect using the single shared db() helper.
    $connection = db();

    // Stop if an administrator already exists.
    $checkAdmin = $connection->prepare(
        "SELECT COUNT(*) FROM users WHERE role = 'admin'"
    );
    $checkAdmin->execute();

    if ((int) $checkAdmin->fetchColumn() > 0) {
        exit("An administrator account already exists. No account was created.\n");
    }

    echo "Create the first System Administrator account.\n";
    echo "Note: Password input will be visible while typing in this terminal.\n\n";

    // Collect administrator information.
    $fullName = prompt("Full name: ");
    $username = prompt("Username (4-50 characters): ");
    $password = prompt("Password (minimum 8 characters): ");

    // Validate the submitted information.
    $validName = $fullName !== '' && strlen($fullName) <= 100;
    $validUsername = preg_match('/^[A-Za-z0-9._-]{4,50}$/', $username) === 1;
    $validPassword = strlen($password) >= 8;

    if (!$validName || !$validUsername || !$validPassword) {
        exit("Invalid information. No administrator account was created.\n");
    }

    // Make sure the username is not already used.
    $checkUsername = $connection->prepare(
        'SELECT COUNT(*) FROM users WHERE username = :username'
    );
    $checkUsername->execute(['username' => $username]);

    if ((int) $checkUsername->fetchColumn() > 0) {
        exit("That username is already taken. Run the script again with another username.\n");
    }

    // Store a password hash, never the plain-text password.
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert the administrator account using the existing users table structure.
    $insertAdmin = $connection->prepare(
        "INSERT INTO users
            (full_name, username, password_hash, role, is_active)
         VALUES
            (:full_name, :username, :password_hash, 'admin', 1)"
    );

    $insertAdmin->execute([
        'full_name' => $fullName,
        'username' => $username,
        'password_hash' => $passwordHash,
    ]);

    echo "\nAdministrator account created successfully.\n";
    echo "You can now sign in through login.php.\n";

} catch (Throwable $exception) {
    fwrite(STDERR, "Setup failed: " . $exception->getMessage() . "\n");
    exit(1);
}
