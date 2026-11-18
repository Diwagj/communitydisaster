<?php
require_once __DIR__ . '/includes/auth.php';

$centers = [];

try {
    $sql = '
        SELECT center_name, address, capacity, status, contact_no
        FROM evacuation_centers
        ORDER BY center_name
    ';

    $centers = db()->query($sql)->fetchAll();
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    $databaseError = true;
}

$activePage = 'evacuation-centers.php';
$pageTitle = 'Evacuation Centers';
require __DIR__ . '/includes/public-page-start.php';
?>

<div class="section-head">
    <div>
        <div class="kicker">Public information</div>
        <h2>Evacuation Centers</h2>
    </div>

    <p>
        Check the published center details and status.
        Follow official instructions during an evacuation.
    </p>
</div>

<?php if (!empty($databaseError)): ?>
    <div class="notice error">
        Evacuation center information is temporarily unavailable.
    </div>
<?php elseif (empty($centers)): ?>
    <div class="empty">
        Evacuation center information has not yet been added.
    </div>
<?php else: ?>
    <div class="cards">
        <?php foreach ($centers as $center): ?>
            <article class="card">
                <h3><?= h($center['center_name']) ?></h3>
                <p><?= h($center['address']) ?></p>

                <p style="margin-top: 8px;">
                    Capacity: <?= (int) $center['capacity'] ?>
                </p>

                <p>
                    Status:
                    <span class="status"><?= h(ucfirst($center['status'])) ?></span>
                </p>

                <?php if (!empty($center['contact_no'])): ?>
                    <p>Contact: <?= h($center['contact_no']) ?></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/public-page-end.php'; ?>
