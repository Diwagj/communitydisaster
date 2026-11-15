<?php
require_once __DIR__ . '/includes/auth.php';
requireRoles(['admin', 'bdrrmc', 'official', 'responder']);
$rows=[]; try { $rows=db()->query('SELECT incident_id,reporter_name,incident_type,location,reported_at,status FROM incidents ORDER BY reported_at DESC')->fetchAll(); } catch(Throwable $e) { error_log($e->getMessage()); }
pageStart('Incident Reports');
if (!$rows) echo '<div class="empty">No records are available yet.</div>'; else { echo '<section class="panel"><div class="table-wrap"><table><thead><tr>';echo '<th>ID</th>';echo '<th>Reporter</th>';echo '<th>Type</th>';echo '<th>Location</th>';echo '<th>Reported</th>';echo '<th>Status</th>';echo '</tr></thead><tbody>'; foreach($rows as $row){echo '<tr>';echo '<td>'.h((string)($row['incident_id'] ?? '')).'</td>';echo '<td>'.h((string)($row['reporter_name'] ?? '')).'</td>';echo '<td>'.h((string)($row['incident_type'] ?? '')).'</td>';echo '<td>'.h((string)($row['location'] ?? '')).'</td>';echo '<td>'.h((string)($row['reported_at'] ?? '')).'</td>';echo '<td>'.h((string)($row['status'] ?? '')).'</td>';echo '</tr>'; } echo '</tbody></table></div></section>'; }
pageEnd();
