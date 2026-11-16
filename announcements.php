<?php
require_once __DIR__ . '/includes/auth.php';
$items = [];
try {
    $items = db()->query("SELECT title,message,category,posted_at FROM announcements WHERE is_active=1 ORDER BY posted_at DESC")->fetchAll();
} catch (Throwable $e) { error_log($e->getMessage()); }
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Announcements | Surong Guisad</title><link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
        <?php publicHeader('announcements.php'); ?>
        <main class="section"><div class="wrap">
            <div class="section-head">
                <div>
                    <div class="kicker">
                        Community information
                    </div>
                    <h2>Official announcements & advisories</h2>
                </div>
                <p>Only published and active announcements are shown here.</p>
            </div>
        <?php if (!$items): ?>
            <div class="empty">There are currently no active announcements.</div>
            <?php else: foreach($items as $item): ?>
                <article class="panel">
                    <span class="status"><?= h(str_replace('_',' ',ucfirst($item['category']))) ?></span>
                    <h2 style="margin-top:12px"><?= h($item['title']) ?></h2>
                    <p><?= nl2br(h($item['message'])) ?></p>
                    <div class="small">Posted <?= h($item['posted_at']) ?></div>
                </article><?php endforeach; endif; ?>
            </div>
        </main>
        <footer class="footer">
            <div class="wrap">
                <a href="index.php">← Back to homepage</a>
            </div>
        </footer>
    </body>
</html>