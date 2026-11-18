<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Disaster Reports';
$allowedRoles = ['admin', 'bdrrmc', 'official'];
$query = 'SELECT incident_type, status, COUNT(*) AS total FROM incidents GROUP BY incident_type, status ORDER BY incident_type, status';
$tableColumns = [
    ['field' => 'incident_type', 'label' => 'Incident type'],
    ['field' => 'status', 'label' => 'Status'],
    ['field' => 'total', 'label' => 'Total']
];

require __DIR__ . '/includes/module-page.php';
