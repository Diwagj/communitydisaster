<?php
require_once __DIR__ . '/includes/auth.php';
requireRoles(['admin', 'bdrrmc', 'responder']);
$rows=[]; try { $rows=db()->query("SELECT incident_id,incident_type,location,status,response_team,assigned_to,action_taken FROM incidents WHERE status IN ('verified','responding') ORDER BY updated_at DESC")->fetchAll(); } catch(Throwable $e) { error_log($e->getMessage()); }
pageStart('Emergency Response');
if (!$rows) echo '<div class="empty">No records are available yet.</div>'; else { echo '<section class="panel"><div class="table-wrap"><table><thead><tr>';echo '<th>Incident</th>';echo '<th>Type</th>';echo '<th>Location</th>';echo '<th>Status</th>';echo '<th>Team</th>';echo '<th>Assigned user ID</th>';echo '<th>Action taken</th>';echo '</tr></thead><tbody>'; foreach($rows as $row){echo '<tr>';echo '<td>'.h((string)($row['incident_id'] ?? '')).'</td>';echo '<td>'.h((string)($row['incident_type'] ?? '')).'</td>';echo '<td>'.h((string)($row['location'] ?? '')).'</td>';echo '<td>'.h((string)($row['status'] ?? '')).'</td>';echo '<td>'.h((string)($row['response_team'] ?? '')).'</td>';echo '<td>'.h((string)($row['assigned_to'] ?? '')).'</td>';echo '<td>'.h((string)($row['action_taken'] ?? '')).'</td>';echo '</tr>'; } echo '</tbody></table></div></section>'; }
pageEnd();
