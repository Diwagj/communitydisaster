<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Incident Reports';
$allowedRoles = ['admin', 'bdrrmc', 'official', 'responder'];
$query = 'SELECT incident_id, reporter_name, incident_type, location, reported_at, status FROM incidents ORDER BY reported_at DESC';
$tableColumns = [
    ['field' => 'incident_id', 'label' => 'ID'],
    ['field' => 'reporter_name', 'label' => 'Reporter'],
    ['field' => 'incident_type', 'label' => 'Type'],
    ['field' => 'location', 'label' => 'Location'],
    ['field' => 'reported_at', 'label' => 'Reported'],
    ['field' => 'status', 'label' => 'Status']
];

require __DIR__ . '/includes/module-page.php';
