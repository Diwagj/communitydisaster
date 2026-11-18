<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Relief Inventory';
$allowedRoles = ['admin', 'bdrrmc'];
$query = 'SELECT item_id, item_name, category, unit, stock_qty FROM relief_items ORDER BY item_name';
$tableColumns = [
    ['field' => 'item_id', 'label' => 'ID'],
    ['field' => 'item_name', 'label' => 'Item'],
    ['field' => 'category', 'label' => 'Category'],
    ['field' => 'unit', 'label' => 'Unit'],
    ['field' => 'stock_qty', 'label' => 'Stock']
];

require __DIR__ . '/includes/module-page.php';
