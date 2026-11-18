<?php
require_once __DIR__ . '/includes/auth.php';

requireRoles(['admin']);

$sql = '
    SELECT
        user_id,
        full_name,
        username,
        role,
        contact_no,
        is_active,
        created_at
    FROM users
    ORDER BY created_at DESC
';

$users = db()->query($sql)->fetchAll();

$pageTitle = 'User Accounts';
require __DIR__ . '/includes/staff-layout-start.php';
?>

<section class="panel">
    <p class="muted">
        Staff accounts are created and managed by the System Administrator.
    </p>

    <p>
        <a class="button" href="admin-create-user.php">
            + Create staff account
        </a>
    </p>

    <?php if (empty($users)): ?>
        <div class="empty">There are no staff accounts yet.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= h($user['full_name']) ?></td>
                            <td><?= h($user['username']) ?></td>
                            <td><?= h(roleName($user['role'])) ?></td>
                            <td><?= h($user['contact_no']) ?></td>
                            <td>
                                <?= (int) $user['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                            </td>
                            <td><?= h($user['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/staff-layout-end.php'; ?>
