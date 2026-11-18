<?php
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$currentRole = currentStaff()['role'];

$modules = [
    [
        'url' => 'incidents.php',
        'title' => 'Incident Reports',
        'description' => 'Review reports submitted by the public.',
        'roles' => ['admin', 'bdrrmc', 'official', 'responder'],
    ],
    [
        'url' => 'response.php',
        'title' => 'Emergency Response',
        'description' => 'Coordinate response assignments and progress.',
        'roles' => ['admin', 'bdrrmc', 'responder'],
    ],
    [
        'url' => 'evacuation.php',
        'title' => 'Evacuation Management',
        'description' => 'View evacuation centers and occupancy records.',
        'roles' => ['admin', 'bdrrmc', 'official', 'responder'],
    ],
    [
        'url' => 'households.php',
        'title' => 'Household Registry',
        'description' => 'Confidential household and resident information.',
        'roles' => ['admin'],
    ],
    [
        'url' => 'inventory.php',
        'title' => 'Relief Inventory',
        'description' => 'Review relief goods and stock records.',
        'roles' => ['admin', 'bdrrmc'],
    ],
    [
        'url' => 'distribution.php',
        'title' => 'Relief Distribution',
        'description' => 'Review recorded relief distributions.',
        'roles' => ['admin', 'bdrrmc', 'official'],
    ],
    [
        'url' => 'manage-announcements.php',
        'title' => 'Announcement Management',
        'description' => 'Manage public emergency announcements.',
        'roles' => ['admin', 'bdrrmc', 'official'],
    ],
    [
        'url' => 'reports.php',
        'title' => 'Disaster Reports',
        'description' => 'View system summaries and incident statistics.',
        'roles' => ['admin', 'bdrrmc', 'official'],
    ],
    [
        'url' => 'users.php',
        'title' => 'User Accounts',
        'description' => 'Create and review staff accounts.',
        'roles' => ['admin'],
    ],
];

$pageTitle = 'Staff Dashboard';
require __DIR__ . '/includes/staff-layout-start.php';
?>

<div class="info-banner" style="margin-bottom: 18px;">
    Signed in as
    <strong><?= h(currentStaff()['full_name']) ?></strong>
    · <?= h(roleName($currentRole)) ?>.
    Your role determines which modules you can open.
</div>

<div class="module-grid">
    <?php foreach ($modules as $module): ?>
        <?php $hasAccess = in_array($currentRole, $module['roles'], true); ?>

        <article class="module-card">
            <h3><?= h($module['title']) ?></h3>
            <p><?= h($module['description']) ?></p>

            <?php if ($hasAccess): ?>
                <a class="button" href="<?= h($module['url']) ?>">
                    Open module
                </a>
            <?php else: ?>
                <span class="locked">🔒 Restricted for your role</span>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/staff-layout-end.php'; ?>
