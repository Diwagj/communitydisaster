<?php
require_once __DIR__ . '/includes/auth.php';
requireRoles(['admin']);
$users=db()->query('SELECT user_id,full_name,username,role,contact_no,is_active,created_at FROM users ORDER BY created_at DESC')->fetchAll();
pageStart('User Accounts');
echo '<div class="panel"><p class="muted">Staff accounts are created only by the System Administrator.</p><p><a class="button" href="admin-create-user.php">＋ Create staff account</a></p><div class="table-wrap"><table><thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Contact</th><th>Status</th><th>Created</th></tr></thead><tbody>';
foreach($users as $u)echo '<tr><td>'.h($u['full_name']).'</td><td>'.h($u['username']).'</td><td>'.h(roleLabel($u['role'])).'</td><td>'.h($u['contact_no']).'</td><td>'.((int)$u['is_active']?'Active':'Inactive').'</td><td>'.h($u['created_at']).'</td></tr>';
echo '</tbody></table></div></div>';
pageEnd();
