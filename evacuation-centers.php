<?php
require_once __DIR__ . '/includes/auth.php';
$centers = [];
try { $centers = db()->query("SELECT center_name,address,capacity,status,contact_no FROM evacuation_centers ORDER BY center_name")->fetchAll(); }
catch(Throwable $e) { error_log($e->getMessage()); }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Evacuation Centers | Surong Guisad</title><link rel="stylesheet" href="assets/style.css"></head><body><?php publicHeader('evacuation-centers.php'); ?><main class="section"><div class="wrap"><div class="section-head"><div><div class="kicker">Public information</div><h2>Evacuation Centers</h2></div><p>Check the published center details and status. Follow official instructions during an evacuation.</p></div>
<?php if (!$centers): ?><div class="empty">Evacuation center information has not yet been added.</div><?php else: ?><div class="cards"><?php foreach($centers as $c): ?><article class="card"><h3><?= h($c['center_name']) ?></h3><p><?= h($c['address']) ?></p><p style="margin-top:8px">Capacity: <?= (int)$c['capacity'] ?></p><p>Status: <span class="status"><?= h(ucfirst($c['status'])) ?></span></p><?php if($c['contact_no']): ?><p>Contact: <?= h($c['contact_no']) ?></p><?php endif; ?></article><?php endforeach; ?></div><?php endif; ?>
</div></main><footer class="footer"><div class="wrap"><a href="index.php">← Back to homepage</a></div></footer></body></html>
