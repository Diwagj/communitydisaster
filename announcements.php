<?php
require_once __DIR__ . '/includes/auth.php';

$announcements = [];

try {
    $sql = '
        SELECT title, message, category, posted_at
        FROM announcements
        WHERE is_active = 1
        ORDER BY posted_at DESC
    ';

    $announcements = db()->query($sql)->fetchAll();
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    $databaseError = true;
}

$activePage = 'announcements.php';
$pageTitle = 'Announcements';
require __DIR__ . '/includes/public-page-start.php';
?>

<div class="section-head">
    <div>
        <div class="kicker">Community information</div>
        <h2>Official announcements & advisories</h2>
    </div>

    <p>Only published and active announcements are shown here.</p>
</div>

<?php if (!empty($databaseError)): ?>
    <div class="notice error">
        Announcement information is temporarily unavailable.
    </div>
<?php elseif (empty($announcements)): ?>
    <div class="empty">There are currently no active announcements.</div>
<?php else: ?>
    <?php foreach ($announcements as $announcement): ?>
        <article class="panel">
            <span class="status">
                <?= h(str_replace('_', ' ', ucfirst($announcement['category']))) ?>
            </span>

            <h2 style="margin-top: 12px;">
                <?= h($announcement['title']) ?>
            </h2>

            <p><?= nl2br(h($announcement['message'])) ?></p>

            <div class="small">
                Posted <?= h($announcement['posted_at']) ?>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/public-page-end.php'; ?>
