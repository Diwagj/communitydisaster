<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$modules = [
    ['incidents.php', 'Incident Reports', 'Review, verify, and link affected households to reports.', 'incidents.view'],
    ['response.php', 'Emergency Response', 'Assign response teams and log field updates.', 'response.view'],
    ['evacuation.php', 'Evacuation Management', 'Manage evacuation centers, capacity, and evacuee check-ins.', 'evacuation.view'],
    ['households.php', 'Household Registry', 'Search, add, and update confidential household records.', 'households.view'],
    ['inventory.php', 'Relief Inventory', 'Track relief items, stock movements, and low-stock alerts.', 'inventory.view'],
    ['distribution.php', 'Relief Distribution', 'Record relief goods released to affected households.', 'distribution.view'],
    ['manage-announcements.php', 'Announcement Management', 'Create, edit, and publish public disaster advisories.', 'announcements.view'],
    ['reports.php', 'Disaster Reports', 'View filtered operational summaries and live analytics.', 'reports.view'],
];

if (isSystemAdministrator()) {
    $modules[] = ['users.php', 'User Accounts', 'Create and manage staff user accounts.', null];
    $modules[] = ['permissions.php', 'User Permissions', 'Configure granular module permissions per user.', null];
}

$pageTitle = 'Staff Dashboard';
require __DIR__ . '/includes/staff-layout-start.php';
?>

<div class="info-banner" style="margin-bottom: 18px;">
    Signed in as <strong><?= h(currentStaff()['full_name']) ?></strong>
    · <?= h(roleName(currentStaff()['role'])) ?>.
    Access is enforced by individual permissions assigned to your account.
</div>

<div class="module-grid">
    <?php foreach ($modules as [$url, $title, $description, $permission]): ?>
        <?php $allowed = ($permission === null) || hasPermission($permission); ?>
        <article class="module-card">
            <h3><?= h($title) ?></h3>
            <p><?= h($description) ?></p>
            <?php if ($allowed): ?>
                <a class="button" href="<?= h($url) ?>">Open module</a>
            <?php else: ?>
                <span class="locked">🔒 Not granted</span>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/staff-layout-end.php'; ?>