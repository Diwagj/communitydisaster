<?php
require_once __DIR__ . '/includes/auth.php';

$centers = [];
$allowedStatuses = ['open', 'full', 'closed'];
$selectedStatus = trim((string) ($_GET['status'] ?? ''));
$searchKeyword = trim((string) ($_GET['q'] ?? ''));

try {
    $conditions = [];
    $params = [];

    if ($selectedStatus !== '' && in_array($selectedStatus, $allowedStatuses, true)) {
        $conditions[] = 'c.status = :status';
        $params['status'] = $selectedStatus;
    } else {
        $selectedStatus = '';
    }

    if ($searchKeyword !== '') {
        $conditions[] = '(c.center_name LIKE :q1 OR c.address LIKE :q2)';
        $params['q1'] = '%' . $searchKeyword . '%';
        $params['q2'] = '%' . $searchKeyword . '%';
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $sql = "SELECT
                c.center_name,
                c.address,
                c.capacity,
                c.status,
                c.contact_no,
                COUNT(e.evacuee_id) AS current_occupants
            FROM evacuation_centers c
            LEFT JOIN evacuees e ON e.center_id = c.center_id AND e.time_out IS NULL
            {$whereClause}
            GROUP BY c.center_id, c.center_name, c.address, c.capacity, c.status, c.contact_no
            ORDER BY c.center_name";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $centers = $stmt->fetchAll();
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
    <p>Check live occupancy, available capacity, and status before proceeding to a center.</p>
</div>

<form method="get" action="evacuation-centers.php" class="panel filter-bar">
    <div class="form-grid">
        <div class="field">
            <label for="status">Filter by status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                <option value="open" <?= $selectedStatus === 'open' ? 'selected' : '' ?>>Open</option>
                <option value="full" <?= $selectedStatus === 'full' ? 'selected' : '' ?>>Full</option>
                <option value="closed" <?= $selectedStatus === 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
        </div>

        <div class="field">
            <label for="q">Search center or address</label>
            <input id="q" name="q" value="<?= h($searchKeyword) ?>" placeholder="Search center name or street...">
        </div>
    </div>
    <button class="button" type="submit">Filter</button>
    <?php if ($selectedStatus !== '' || $searchKeyword !== ''): ?>
        <a class="button button-light" href="evacuation-centers.php">Reset</a>
    <?php endif; ?>
</form>

<?php if (!empty($databaseError)): ?>
    <div class="notice error">Evacuation center information is temporarily unavailable.</div>
<?php elseif (empty($centers)): ?>
    <div class="empty">No evacuation centers matched your filter.</div>
<?php else: ?>
    <div class="cards">
        <?php foreach ($centers as $center): ?>
            <?php
            $capacity = (int) $center['capacity'];
            $occupants = (int) $center['current_occupants'];
            $available = max(0, $capacity - $occupants);
            ?>
            <article class="card">
                <h3><?= h($center['center_name']) ?></h3>
                <p><?= h($center['address']) ?></p>

                <p style="margin-top: 8px;">
                    Occupancy: <strong><?= $occupants ?> / <?= $capacity ?></strong>
                    (<?= $available ?> slots left)
                </p>

                <p>
                    Status:
                    <span class="status <?= $center['status'] === 'open' ? 'status-open' : '' ?>">
                        <?= h(ucfirst($center['status'])) ?>
                    </span>
                </p>

                <?php if (!empty($center['contact_no'])): ?>
                    <p>Contact: <?= h($center['contact_no']) ?></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/public-page-end.php'; ?>