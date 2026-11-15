<?php
require_once __DIR__ . '/includes/auth.php';
requireRoles(['admin', 'bdrrmc']);
$rows=[]; try { $rows=db()->query('SELECT item_id,item_name,category,unit,stock_qty FROM relief_items ORDER BY item_name')->fetchAll(); } catch(Throwable $e) { error_log($e->getMessage()); }
pageStart('Relief Inventory');
if (!$rows) echo '<div class="empty">No records are available yet.</div>'; else { echo '<section class="panel"><div class="table-wrap"><table><thead><tr>';echo '<th>ID</th>';echo '<th>Item</th>';echo '<th>Category</th>';echo '<th>Unit</th>';echo '<th>Stock</th>';echo '</tr></thead><tbody>'; foreach($rows as $row){echo '<tr>';echo '<td>'.h((string)($row['item_id'] ?? '')).'</td>';echo '<td>'.h((string)($row['item_name'] ?? '')).'</td>';echo '<td>'.h((string)($row['category'] ?? '')).'</td>';echo '<td>'.h((string)($row['unit'] ?? '')).'</td>';echo '<td>'.h((string)($row['stock_qty'] ?? '')).'</td>';echo '</tr>'; } echo '</tbody></table></div></section>'; }
pageEnd();
