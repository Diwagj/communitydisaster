<?php
require_once __DIR__ . '/includes/auth.php';

$stats = [
    'active_announcements' => 0,
    'open_centers' => 0,
    'available_slots' => 0,
];

try {
    $stats['active_announcements'] = (int) db()->query(
        'SELECT COUNT(*) FROM announcements WHERE is_active = 1'
    )->fetchColumn();

    $centerStats = db()->query(
        "SELECT
            COUNT(*) AS open_centers,
            COALESCE(SUM(c.capacity - (
                SELECT COUNT(*) FROM evacuees e
                WHERE e.center_id = c.center_id AND e.time_out IS NULL
            )), 0) AS available_slots
         FROM evacuation_centers c
         WHERE c.status = 'open'"
    )->fetch();

    if ($centerStats) {
        $stats['open_centers'] = (int) $centerStats['open_centers'];
        $stats['available_slots'] = max(0, (int) $centerStats['available_slots']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barangay Surong Guisad | DRRM Portal</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php require __DIR__ . '/includes/public-header.php'; ?>

    <main>
        <section class="hero">
            <div class="wrap hero-grid">
                <div>
                    <div class="eyebrow">Barangay Surong Guisad · Community Portal</div>
                    <h1>Prepared communities. Coordinated response. Safer lives.</h1>
                    <p>
                        Access live disaster advisories, check open evacuation centers and available capacity,
                        and submit emergency incident reports to the barangay response team.
                    </p>
                    <div class="actions">
                        <a class="button" href="report-incident.php">Report an incident</a>
                        <a class="button button-outline" href="announcements.php">View announcements</a>
                    </div>
                </div>

                <div class="hero-panel">
                    <h2>Live Community Status</h2>
                    <p>Public portal services require no account.</p>

                    <div class="quick-grid">
                        <div class="quick">
                            <b><?= $stats['active_announcements'] ?> Active Advisories</b>
                            <small>Official barangay notices</small>
                        </div>
                        <div class="quick">
                            <b><?= $stats['open_centers'] ?> Open Centers</b>
                            <small><?= $stats['available_slots'] ?> available slots</small>
                        </div>
                        <div class="quick">
                            <b>Incident Reporting</b>
                            <small>Submit hazard reports with photos</small>
                        </div>
                        <div class="quick">
                            <b>Staff Portal</b>
                            <small>Role & permission protected</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="wrap">
                <div class="section-head">
                    <div>
                        <div class="kicker">Public services</div>
                        <h2>How can we help?</h2>
                    </div>
                    <p>
                        Confidential household and inventory records are restricted to authorized personnel.
                    </p>
                </div>

                <div class="cards">
                    <a class="card" href="announcements.php">
                        <div class="card-icon">◉</div>
                        <h3>Announcements & Advisories</h3>
                        <p>Filter and read emergency notices, preparedness reminders, and evacuation instructions.</p>
                    </a>

                    <a class="card" href="report-incident.php">
                        <div class="card-icon">⌖</div>
                        <h3>Report an Incident</h3>
                        <p>Submit a flood, fire, landslide, or road obstruction report and receive a tracking reference number.</p>
                    </a>

                    <a class="card" href="evacuation-centers.php">
                        <div class="card-icon">⌂</div>
                        <h3>Evacuation Centers</h3>
                        <p>View live occupancy, available capacity, location, and contact details of evacuation centers.</p>
                    </a>
                </div>
            </div>
        </section>

        <section class="section" style="padding-top: 0;">
            <div class="wrap">
                <div class="info-banner">
                    <strong>For emergencies:</strong>
                    If there is immediate danger, contact local emergency services directly.
                </div>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/includes/public-footer.php'; ?>
</body>
</html>