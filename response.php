<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Emergency Response';
$allowedRoles = ['admin', 'bdrrmc', 'responder'];
$query = "SELECT incident_id, incident_type, location, status, response_team, assigned_to, action_taken FROM incidents WHERE status IN ('verified', 'responding') ORDER BY updated_at DESC";
$tableColumns = [
    ['field' => 'incident_id', 'label' => 'Incident'],
    ['field' => 'incident_type', 'label' => 'Type'],
    ['field' => 'location', 'label' => 'Location'],
    ['field' => 'status', 'label' => 'Status'],
    ['field' => 'response_team', 'label' => 'Team'],
    ['field' => 'assigned_to', 'label' => 'Assigned user ID'],
    ['field' => 'action_taken', 'label' => 'Action taken']
];

require __DIR__ . '/includes/module-page.php';
