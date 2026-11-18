<?php
require_once __DIR__ . '/includes/auth.php';

requireRoles(['admin']);

$errors = [];
$successMessage = '';

$formData = [
    'full_name' => '',
    'username' => '',
    'contact_no' => '',
    'role' => 'responder',
];

$password = '';
$confirmPassword = '';

$availableRoles = [
    'admin' => 'System Administrator',
    'bdrrmc' => 'BDRRMC Coordinator',
    'official' => 'Barangay Official',
    'responder' => 'CDRRMC Agent / Responder',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    foreach ($formData as $field => $defaultValue) {
        $formData[$field] = trim((string) ($_POST[$field] ?? $defaultValue));
    }

    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    // Validate the administrator-entered information.
    if ($formData['full_name'] === '' || strlen($formData['full_name']) > 100) {
        $errors[] = 'Enter a full name (maximum 100 characters).';
    }

    if (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $formData['username'])) {
        $errors[] = 'Username must be 4–50 characters and use only letters, numbers, dots, underscores, or hyphens.';
    }

    if ($formData['contact_no'] !== '' && strlen($formData['contact_no']) > 20) {
        $errors[] = 'Contact number must be 20 characters or fewer.';
    }

    if (!array_key_exists($formData['role'], $availableRoles)) {
        $errors[] = 'Select a valid staff role.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must contain at least 8 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password and confirmation do not match.';
    }

    // Check that the username is not already registered.
    if (empty($errors)) {
        $checkUsername = db()->prepare(
            'SELECT user_id FROM users WHERE username = :username LIMIT 1'
        );

        $checkUsername->execute([
            'username' => $formData['username'],
        ]);

        if ($checkUsername->fetch()) {
            $errors[] = 'That username is already in use.';
        }
    }

    // Save the account only after all validation passes.
    if (empty($errors)) {
        try {
            $insertUser = db()->prepare(
                'INSERT INTO users
                    (full_name, username, password_hash, role, contact_no, is_active)
                 VALUES
                    (:full_name, :username, :password_hash, :role, :contact_no, 1)'
            );

            $insertUser->execute([
                'full_name' => $formData['full_name'],
                'username' => $formData['username'],
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $formData['role'],
                'contact_no' => $formData['contact_no'] !== ''
                    ? $formData['contact_no']
                    : null,
            ]);

            $successMessage = 'Staff account created successfully.';

            $formData = [
                'full_name' => '',
                'username' => '',
                'contact_no' => '',
                'role' => 'responder',
            ];

            $password = '';
            $confirmPassword = '';
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $errors[] = 'The account could not be saved. Please check the database and try again.';
        }
    }
}

$pageTitle = 'Create Staff Account';
require __DIR__ . '/includes/staff-layout-start.php';
?>

<?php if ($successMessage !== ''): ?>
    <div class="notice success">
        <?= h($successMessage) ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="notice error">
        <strong>Please correct the following:</strong>

        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= h($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<section class="panel">
    <p class="muted">
        Only the System Administrator can create staff accounts.
        Public visitors do not receive accounts.
    </p>

    <form method="post" action="admin-create-user.php">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= h(csrfToken()) ?>"
        >

        <div class="form-grid">
            <div class="field full">
                <label for="full_name">Full name *</label>
                <input
                    id="full_name"
                    name="full_name"
                    maxlength="100"
                    value="<?= h($formData['full_name']) ?>"
                    required
                >
            </div>

            <div class="field">
                <label for="username">Username *</label>
                <input
                    id="username"
                    name="username"
                    maxlength="50"
                    value="<?= h($formData['username']) ?>"
                    required
                >
            </div>

            <div class="field">
                <label for="contact_no">Contact number</label>
                <input
                    id="contact_no"
                    name="contact_no"
                    maxlength="20"
                    value="<?= h($formData['contact_no']) ?>"
                >
            </div>

            <div class="field">
                <label for="role">Staff role *</label>
                <select id="role" name="role" required>
                    <?php foreach ($availableRoles as $roleValue => $roleLabel): ?>
                        <option
                            value="<?= h($roleValue) ?>"
                            <?= $formData['role'] === $roleValue ? 'selected' : '' ?>
                        >
                            <?= h($roleLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="password">Temporary password *</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    minlength="8"
                    required
                >
            </div>

            <div class="field">
                <label for="confirm_password">Confirm password *</label>
                <input
                    id="confirm_password"
                    name="confirm_password"
                    type="password"
                    minlength="8"
                    required
                >
            </div>
        </div>

        <button class="button" type="submit">Create account</button>
    </form>
</section>

<?php require __DIR__ . '/includes/staff-layout-end.php'; ?>
