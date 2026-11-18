<?php
$pageTitle = $pageTitle ?? 'Staff Portal';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> | Surong Guisad DRRM</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php require __DIR__ . '/staff-header.php'; ?>

    <div class="wrap staff-layout">
        <?php require __DIR__ . '/staff-sidebar.php'; ?>

        <main class="content">
            <div class="crumb">Staff Portal / <?= h($pageTitle) ?></div>
            <h1><?= h($pageTitle) ?></h1>
