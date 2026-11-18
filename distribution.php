<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Relief Distribution';
$allowedRoles = ['admin', 'bdrrmc', 'official'];
$query = 'SELECT distribution_id, household_id, item_id, quantity, received_by, distributed_by, distribution_date FROM relief_distributions ORDER BY distribution_date DESC';
$tableColumns = [
    ['field' => 'distribution_id', 'label' => 'ID'],
    ['field' => 'household_id', 'label' => 'Household ID'],
    ['field' => 'item_id', 'label' => 'Item ID'],
    ['field' => 'quantity', 'label' => 'Quantity'],
    ['field' => 'received_by', 'label' => 'Received by'],
    ['field' => 'distributed_by', 'label' => 'Distributed by'],
    ['field' => 'distribution_date', 'label' => 'Date']
];

require __DIR__ . '/includes/module-page.php';
