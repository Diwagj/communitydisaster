<?php
require_once __DIR__ . '/includes/auth.php';
requireRoles(['admin', 'bdrrmc', 'official']);
$rows=[]; try { $rows=db()->query('SELECT announcement_id,title,category,posted_at,is_active FROM announcements ORDER BY posted_at DESC')->fetchAll(); } catch(Throwable $e) { error_log($e->getMessage()); }
pageStart('Announcement Management');
if (!$rows) echo '<div class="empty">No records are available yet.</div>'; else { echo '<section class="panel"><div class="table-wrap"><table><thead><tr>';echo '<th>ID</th>';echo '<th>Title</th>';echo '<th>Category</th>';echo '<th>Posted</th>';echo '<th>Active</th>';echo '</tr></thead><tbody>'; foreach($rows as $row){echo '<tr>';echo '<td>'.h((string)($row['announcement_id'] ?? '')).'</td>';echo '<td>'.h((string)($row['title'] ?? '')).'</td>';echo '<td>'.h((string)($row['category'] ?? '')).'</td>';echo '<td>'.h((string)($row['posted_at'] ?? '')).'</td>';echo '<td>'.h((string)($row['is_active'] ?? '')).'</td>';echo '</tr>'; } echo '</tbody></table></div></section>'; }
pageEnd();
