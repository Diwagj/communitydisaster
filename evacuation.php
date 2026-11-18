<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Evacuation Management';
$allowedRoles = ['admin', 'bdrrmc', 'official', 'responder'];
$query = 'SELECT center_id, center_name, address, capacity, status FROM evacuation_centers ORDER BY center_name';
$tableColumns = [
    ['field' => 'center_id', 'label' => 'ID'],
    ['field' => 'center_name', 'label' => 'Center'],
    ['field' => 'address', 'label' => 'Address'],
    ['field' => 'capacity', 'label' => 'Capacity'],
    ['field' => 'status', 'label' => 'Status']
];

require __DIR__ . '/includes/module-page.php';
