<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Announcement Management';
$allowedRoles = ['admin', 'bdrrmc', 'official'];
$query = 'SELECT announcement_id, title, category, posted_at, is_active FROM announcements ORDER BY posted_at DESC';
$tableColumns = [
    ['field' => 'announcement_id', 'label' => 'ID'],
    ['field' => 'title', 'label' => 'Title'],
    ['field' => 'category', 'label' => 'Category'],
    ['field' => 'posted_at', 'label' => 'Posted'],
    ['field' => 'is_active', 'label' => 'Active']
];

require __DIR__ . '/includes/module-page.php';
