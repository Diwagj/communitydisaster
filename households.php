<?php
require_once __DIR__ . '/includes/auth.php';
requireRoles(['admin']);
$rows=[]; try { $rows=db()->query('SELECT household_id,household_head,member_count,address,vulnerability_class,emergency_contact_no FROM households ORDER BY household_head')->fetchAll(); } catch(Throwable $e) { error_log($e->getMessage()); }
pageStart('Household Registry');
if (!$rows) echo '<div class="empty">No records are available yet.</div>'; else { echo '<section class="panel"><div class="table-wrap"><table><thead><tr>';echo '<th>ID</th>';echo '<th>Household head</th>';echo '<th>Members</th>';echo '<th>Address</th>';echo '<th>Vulnerability</th>';echo '<th>Emergency contact</th>';echo '</tr></thead><tbody>'; foreach($rows as $row){echo '<tr>';echo '<td>'.h((string)($row['household_id'] ?? '')).'</td>';echo '<td>'.h((string)($row['household_head'] ?? '')).'</td>';echo '<td>'.h((string)($row['member_count'] ?? '')).'</td>';echo '<td>'.h((string)($row['address'] ?? '')).'</td>';echo '<td>'.h((string)($row['vulnerability_class'] ?? '')).'</td>';echo '<td>'.h((string)($row['emergency_contact_no'] ?? '')).'</td>';echo '</tr>'; } echo '</tbody></table></div></section>'; }
pageEnd();
