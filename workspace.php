<?php
require_once __DIR__ . '/includes/auth.php';
$moduleKey = (string) ($_GET['module'] ?? '');
require __DIR__ . '/includes/workspace.php';