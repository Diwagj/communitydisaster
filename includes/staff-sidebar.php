<?php
$items = [
    ['dashboard.php', 'Dashboard', null],
    ['incidents.php', 'Incident Reports', 'incidents.view'],
    ['response.php', 'Emergency Response', 'response.view'],
    ['evacuation.php', 'Evacuation Management', 'evacuation.view'],
    ['households.php', 'Household Registry', 'households.view'],
    ['inventory.php', 'Relief Inventory', 'inventory.view'],
    ['distribution.php', 'Relief Distribution', 'distribution.view'],
    ['manage-announcements.php', 'Announcements', 'announcements.view'],
    ['reports.php', 'Disaster Reports', 'reports.view'],
];

if (isSystemAdministrator()) {
    $items[] = ['users.php', 'User Accounts', null];
    $items[] = ['permissions.php', 'User Permissions', null];
}
?>
<aside class="side">
    <div class="side-title">SYSTEM MODULES</div>
    <?php foreach ($items as [$url, $label, $permission]): ?>
        <?php $allowed = ($permission === null) || hasPermission($permission); ?>
        <?php if ($allowed): ?>
            <a href="<?= h($url) ?>"><?= h($label) ?></a>
        <?php else: ?>
            <span class="disabled" aria-disabled="true" title="Not granted by the System Administrator">
                <?= h($label) ?> <small>🔒</small>
            </span>
        <?php endif; ?>
    <?php endforeach; ?>
    <div class="side-note">
        Module access is individually configured by the System Administrator.
    </div>
</aside>