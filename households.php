<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Household Registry';
$allowedRoles = ['admin'];
$query = 'SELECT household_id, household_head, member_count, address, vulnerability_class, emergency_contact_no FROM households ORDER BY household_head';
$tableColumns = [
    ['field' => 'household_id', 'label' => 'ID'],
    ['field' => 'household_head', 'label' => 'Household head'],
    ['field' => 'member_count', 'label' => 'Members'],
    ['field' => 'address', 'label' => 'Address'],
    ['field' => 'vulnerability_class', 'label' => 'Vulnerability'],
    ['field' => 'emergency_contact_no', 'label' => 'Emergency contact']
];

require __DIR__ . '/includes/module-page.php';
