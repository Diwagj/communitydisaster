<?php
require_once __DIR__ . '/includes/auth.php';
requireRoles(['admin', 'bdrrmc', 'official', 'responder']);
$rows=[]; try { $rows=db()->query('SELECT center_id,center_name,address,capacity,status FROM evacuation_centers ORDER BY center_name')->fetchAll(); } catch(Throwable $e) { error_log($e->getMessage()); }
pageStart('Evacuation Management');
if (!$rows) echo '<div class="empty">No records are available yet.</div>'; else { echo '<section class="panel"><div class="table-wrap"><table><thead><tr>';echo '<th>ID</th>';echo '<th>Center</th>';echo '<th>Address</th>';echo '<th>Capacity</th>';echo '<th>Status</th>';echo '</tr></thead><tbody>'; foreach($rows as $row){echo '<tr>';echo '<td>'.h((string)($row['center_id'] ?? '')).'</td>';echo '<td>'.h((string)($row['center_name'] ?? '')).'</td>';echo '<td>'.h((string)($row['address'] ?? '')).'</td>';echo '<td>'.h((string)($row['capacity'] ?? '')).'</td>';echo '<td>'.h((string)($row['status'] ?? '')).'</td>';echo '</tr>'; } echo '</tbody></table></div></section>'; }
pageEnd();
