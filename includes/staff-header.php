<?php
$staffMember = currentStaff();
?>
<header class="top">
    <div class="wrap top-inner">
        <a class="brand" href="dashboard.php">
            <img src="assets/logo.png" alt="Barangay logo">
            <span>
                <b>SURONG GUISAD DRRM</b>
                <small>Staff Management Portal</small>
            </span>
        </a>

        <div class="staff-id">
            <span>
                <?= h($staffMember['full_name'] ?? '') ?>
                ·
                <?= h(roleName($staffMember['role'] ?? '')) ?>
            </span>

            <a class="button button-light" href="logout.php">Sign out</a>
        </div>
    </div>
</header>
