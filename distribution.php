<?php
require_once __DIR__ . '/includes/auth.php';
requireRoles(['admin', 'bdrrmc', 'official']);
$rows=[]; try { $rows=db()->query('SELECT distribution_id,household_id,item_id,quantity,received_by,distributed_by,distribution_date FROM relief_distributions ORDER BY distribution_date DESC')->fetchAll(); } catch(Throwable $e) { error_log($e->getMessage()); }
pageStart('Relief Distribution');
if (!$rows) echo '<div class="empty">No records are available yet.</div>'; else { echo '<section class="panel"><div class="table-wrap"><table><thead><tr>';echo '<th>ID</th>';echo '<th>Household ID</th>';echo '<th>Item ID</th>';echo '<th>Quantity</th>';echo '<th>Received by</th>';echo '<th>Distributed by</th>';echo '<th>Date</th>';echo '</tr></thead><tbody>'; foreach($rows as $row){echo '<tr>';echo '<td>'.h((string)($row['distribution_id'] ?? '')).'</td>';echo '<td>'.h((string)($row['household_id'] ?? '')).'</td>';echo '<td>'.h((string)($row['item_id'] ?? '')).'</td>';echo '<td>'.h((string)($row['quantity'] ?? '')).'</td>';echo '<td>'.h((string)($row['received_by'] ?? '')).'</td>';echo '<td>'.h((string)($row['distributed_by'] ?? '')).'</td>';echo '<td>'.h((string)($row['distribution_date'] ?? '')).'</td>';echo '</tr>'; } echo '</tbody></table></div></section>'; }
pageEnd();
