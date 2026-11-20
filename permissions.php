<?php
require_once __DIR__.'/includes/auth.php';
requireRoles(['admin']);
$groups=[
 'households'=>['Household and Resident Registry',['households.view'=>'View','households.create'=>'Add','households.edit'=>'Edit']],
 'incidents'=>['Disaster Incident Reporting',['incidents.view'=>'View','incidents.create'=>'Add reports','incidents.edit'=>'Edit reports','incidents.verify'=>'Verify / reject','incidents.affected_households'=>'Link affected households']],
 'response'=>['Emergency Response Management',['response.view'=>'View','response.assign'=>'Assign team','response.update'=>'Update response']],
 'evacuation'=>['Evacuation Center Management',['evacuation.view'=>'View','evacuation.create'=>'Add / edit centers','evacuation.evacuate'=>'Register / check out evacuees']],
 'inventory'=>['Relief Goods Inventory',['inventory.view'=>'View','inventory.create'=>'Add items','inventory.edit'=>'Edit items','inventory.stock'=>'Record stock movements']],
 'distribution'=>['Relief Distribution Management',['distribution.view'=>'View','distribution.create'=>'Release relief goods']],
 'announcements'=>['Emergency Announcement Management',['announcements.view'=>'View','announcements.create'=>'Create announcements','announcements.edit'=>'Edit / publish']],
 'reports'=>['Disaster Reports',['reports.view'=>'View']],
];
$users=db()->query('SELECT user_id,full_name,username,role,is_active FROM users ORDER BY full_name')->fetchAll();
$selectedId=(int)($_GET['user_id']??$_POST['user_id']??0);$selectedUser=null;foreach($users as $u)if((int)$u['user_id']===$selectedId)$selectedUser=$u;
$notice='';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verifyCsrfToken();
 if(!$selectedUser){$error='Select a valid staff account.';}else{
  $pdo=db();$pdo->beginTransaction();
  try{$del=$pdo->prepare('DELETE FROM user_permissions WHERE user_id=:uid');$del->execute(['uid'=>$selectedId]);$ins=$pdo->prepare('INSERT INTO user_permissions(user_id,permission_key,allowed) VALUES(:uid,:permission,1)');foreach($groups as $g=>$item)foreach($item[1] as $key=>$label)if(isset($_POST['permissions'][$key]))$ins->execute(['uid'=>$selectedId,'permission'=>$key]);$pdo->commit();auditLog('update_permissions','user',$selectedId,'Updated individual permissions');$notice='Permissions saved.';}
  catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log($e->getMessage());$error='Could not save permissions. Run database_upgrade_v5.sql first.';}
 }
}
$current=[];if($selectedUser){$q=db()->prepare('SELECT permission_key FROM user_permissions WHERE user_id=:uid AND allowed=1');$q->execute(['uid'=>$selectedId]);$current=$q->fetchAll(PDO::FETCH_COLUMN);}
$pageTitle='User Permissions';require __DIR__.'/includes/staff-layout-start.php';
?>
<section class="panel"><p>Grant permissions to each staff account individually. Permissions are not automatically determined by job title. The System Administrator always has full access.</p>
<?php if($notice):?><div class="notice success"><?=h($notice)?></div><?php endif;?><?php if($error):?><div class="notice error"><?=h($error)?></div><?php endif;?>
<form method="get"><div class="field"><label for="user_id">Staff account</label><select id="user_id" name="user_id" required><option value="">Select account</option><?php foreach($users as $u):?><option value="<?=$u['user_id']?>" <?=$selectedId===(int)$u['user_id']?'selected':''?>><?=h($u['full_name'].' ('.$u['username'].') — '.roleName($u['role']))?></option><?php endforeach;?></select></div><button class="button">Load permissions</button></form>
<?php if($selectedUser):?><hr><h2><?=h($selectedUser['full_name'])?></h2><form method="post" action="permissions.php?user_id=<?=$selectedId?>"><input type="hidden" name="csrf_token" value="<?=h(csrfToken())?>"><input type="hidden" name="user_id" value="<?=$selectedId?>">
<?php foreach($groups as $group=>$data):?><h3><?=h($data[0])?></h3><div class="form-grid"><?php foreach($data[1] as $key=>$label):?><label class="check-field"><input type="checkbox" name="permissions[<?=h($key)?>]" value="1" <?=in_array($key,$current,true)?'checked':''?>> <?=h($label)?></label><?php endforeach;?></div><?php endforeach;?><button class="button">Save permissions</button></form><?php endif;?></section>
<?php require __DIR__.'/includes/staff-layout-end.php';?>
