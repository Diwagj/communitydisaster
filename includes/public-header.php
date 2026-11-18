<?php
$activePage = $activePage ?? '';
$publicLinks = [
    'announcements.php' => 'Announcements',
    'evacuation-centers.php' => 'Evacuation Centers',
    'report-incident.php' => 'Report Incident',
];
?>
<header class="top">
    <div class="wrap top-inner">
        <a class="brand" href="index.php">
            <img src="assets/logo.png" alt="Barangay logo">
            <span>
                <b>BARANGAY SURONG GUISAD</b>
                <small>Disaster Response Portal</small>
            </span>
        </a>

        <nav class="public-nav" aria-label="Public navigation">
            <?php foreach ($publicLinks as $url => $label): ?>
                <a
                    href="<?= h($url) ?>"
                    <?= $activePage === $url ? 'class="current"' : '' ?>
                >
                    <?= h($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>
