<?php
$currentRole = currentStaff()['role'] ?? '';

$navigationItems = [
    [
        'url' => 'dashboard.php',
        'label' => 'Dashboard',
        'roles' => ['admin', 'bdrrmc', 'official', 'responder'],
    ],
    [
        'url' => 'incidents.php',
        'label' => 'Incident Reports',
        'roles' => ['admin', 'bdrrmc', 'official', 'responder'],
    ],
    [
        'url' => 'response.php',
        'label' => 'Emergency Response',
        'roles' => ['admin', 'bdrrmc', 'responder'],
    ],
    [
        'url' => 'evacuation.php',
        'label' => 'Evacuation Management',
        'roles' => ['admin', 'bdrrmc', 'official', 'responder'],
    ],
    [
        'url' => 'households.php',
        'label' => 'Household Registry',
        'roles' => ['admin'],
    ],
    [
        'url' => 'inventory.php',
        'label' => 'Relief Inventory',
        'roles' => ['admin', 'bdrrmc'],
    ],
    [
        'url' => 'distribution.php',
        'label' => 'Relief Distribution',
        'roles' => ['admin', 'bdrrmc', 'official'],
    ],
    [
        'url' => 'manage-announcements.php',
        'label' => 'Announcements',
        'roles' => ['admin', 'bdrrmc', 'official'],
    ],
    [
        'url' => 'reports.php',
        'label' => 'Disaster Reports',
        'roles' => ['admin', 'bdrrmc', 'official'],
    ],
    [
        'url' => 'users.php',
        'label' => 'User Accounts',
        'roles' => ['admin'],
    ],
];
?>

<aside class="side">
    <div class="side-title">SYSTEM MODULES</div>

    <?php foreach ($navigationItems as $item): ?>
        <?php $hasAccess = in_array($currentRole, $item['roles'], true); ?>

        <?php if ($hasAccess): ?>
            <a href="<?= h($item['url']) ?>">
                <?= h($item['label']) ?>
            </a>
        <?php else: ?>
            <span
                class="disabled"
                title="Your account does not have access"
                aria-disabled="true"
            >
                <?= h($item['label']) ?>
                <small>🔒</small>
            </span>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="side-note">
        Greyed-out modules are unavailable for your role.
        PHP checks permissions when a page is opened directly.
    </div>
</aside>
