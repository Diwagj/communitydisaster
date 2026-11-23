<?php
require_once __DIR__ . '/includes/auth.php';

$announcements = [];
$categories = [
    'advisory' => 'Advisory',
    'evacuation_instruction' => 'Evacuation Instruction',
    'emergency' => 'Emergency',
    'preparedness' => 'Preparedness',
];

$selectedCategory = trim((string) ($_GET['category'] ?? ''));
$searchKeyword = trim((string) ($_GET['q'] ?? ''));

try {
    $conditions = ['is_active = 1'];
    $params = [];

   
    if ($selectedCategory !== '' && array_key_exists($selectedCategory, $categories)) {
        $conditions[] = 'category = :category';
        $params['category'] = $selectedCategory;
    } else {
        $selectedCategory = '';
    }

    
    if ($searchKeyword !== '') {
        $conditions[] = '(title LIKE :q1 OR message LIKE :q2)';
        $params['q1'] = '%' . $searchKeyword . '%';
        $params['q2'] = '%' . $searchKeyword . '%';
    }

    $sql = 'SELECT title, message, category, posted_at
            FROM announcements
            WHERE ' . implode(' AND ', $conditions) . '
            ORDER BY posted_at DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $announcements = $stmt->fetchAll();
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
    <p>Filter published advisories by category or keyword.</p>
</div>

<form method="get" action="announcements.php" class="panel filter-bar">
    <div class="form-grid">
        <div class="field">
            <label for="category">Filter by category</label>
            <select id="category" name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= h($key) ?>" <?= $selectedCategory === $key ? 'selected' : '' ?>>
                        <?= h($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="q">Search keyword</label>
            <input id="q" name="q" value="<?= h($searchKeyword) ?>" placeholder="Search title or details...">
        </div>
    </div>
    <button class="button" type="submit">Filter</button>
    <?php if ($selectedCategory !== '' || $searchKeyword !== ''): ?>
        <a class="button button-light" href="announcements.php">Reset</a>
    <?php endif; ?>
</form>

<?php if (!empty($databaseError)): ?>
    <div class="notice error">Announcement information is temporarily unavailable.</div>
<?php elseif (empty($announcements)): ?>
    <div class="empty">No active announcements matched your filter.</div>
<?php else: ?>
    <?php foreach ($announcements as $announcement): ?>
        <article class="panel">
            <span class="status">
                <?= h(str_replace('_', ' ', ucfirst($announcement['category']))) ?>
            </span>
            <h2 style="margin-top: 12px;"><?= h($announcement['title']) ?></h2>
            <p><?= nl2br(h($announcement['message'])) ?></p>
            <div class="small">Posted <?= h($announcement['posted_at']) ?></div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/public-page-end.php'; ?>