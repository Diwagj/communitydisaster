<?php
require_once __DIR__ . '/includes/auth.php';
requireRoles(['admin', 'bdrrmc', 'official']);
$rows=[]; try { $rows=db()->query('SELECT incident_type,status,COUNT(*) AS total FROM incidents GROUP BY incident_type,status ORDER BY incident_type,status')->fetchAll(); } catch(Throwable $e) { error_log($e->getMessage()); }
pageStart('Disaster Reports');
if (!$rows) echo '<div class="empty">No records are available yet.</div>'; else { echo '<section class="panel"><div class="table-wrap"><table><thead><tr>';echo '<th>Incident type</th>';echo '<th>Status</th>';echo '<th>Total</th>';echo '</tr></thead><tbody>'; foreach($rows as $row){echo '<tr>';echo '<td>'.h((string)($row['incident_type'] ?? '')).'</td>';echo '<td>'.h((string)($row['status'] ?? '')).'</td>';echo '<td>'.h((string)($row['total'] ?? '')).'</td>';echo '</tr>'; } echo '</tbody></table></div></section>'; }
pageEnd();
