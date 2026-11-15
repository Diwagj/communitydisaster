<?php
require_once __DIR__ . '/includes/auth.php';
requireRoles(['admin']);
$errors=[];$success='';
$values=['full_name'=>'','username'=>'','contact_no'=>'','role'=>'responder'];
if($_SERVER['REQUEST_METHOD']==='POST'){
 checkCsrf();
 foreach($values as $k=>$v)$values[$k]=trim((string)($_POST[$k]??''));
 $password=(string)($_POST['password']??'');$confirm=(string)($_POST['confirm_password']??'');
 if($values['full_name']===''||strlen($values['full_name'])>100)$errors[]='Enter a full name (maximum 100 characters).';
 if(!preg_match('/^[A-Za-z0-9._-]{4,50}$/',$values['username']))$errors[]='Username must be 4–50 characters using letters, numbers, dots, underscores, or hyphens.';
 if($values['contact_no']!==''&&strlen($values['contact_no'])>20)$errors[]='Contact number must be 20 characters or fewer.';
 if(!in_array($values['role'],['admin','bdrrmc','official','responder'],true))$errors[]='Select a valid staff role.';
 if(strlen($password)<8)$errors[]='Password must contain at least 8 characters.';
 if($password!==$confirm)$errors[]='Password and confirmation do not match.';
 if(!$errors){$q=db()->prepare('SELECT user_id FROM users WHERE username=? LIMIT 1');$q->execute([$values['username']]);if($q->fetch())$errors[]='That username is already in use.';}
 if(!$errors){
  try{$q=db()->prepare('INSERT INTO users(full_name,username,password_hash,role,contact_no,is_active) VALUES(?,?,?,?,?,1)');$q->execute([$values['full_name'],$values['username'],password_hash($password,PASSWORD_DEFAULT),$values['role'],$values['contact_no']?:null]);$success='Staff account created successfully.';$values=['full_name'=>'','username'=>'','contact_no'=>'','role'=>'responder'];}
  catch(Throwable $e){error_log($e->getMessage());$errors[]='The account could not be saved. Check the database and try again.';}
 }
}
pageStart('Create Staff Account');
if($success)echo '<div class="notice success">'.h($success).'</div>';
if($errors){echo '<div class="notice error"><strong>Please correct:</strong><ul>';foreach($errors as $e)echo '<li>'.h($e).'</li>';echo '</ul></div>';}
echo '<section class="panel"><p class="muted">Only the System Administrator can create staff accounts. Public visitors do not receive accounts.</p><form method="post" action="admin-create-user.php"><input type="hidden" name="csrf" value="'.h(csrf()).'"><div class="form-grid">';
echo '<div class="field full"><label for="full_name">Full name *</label><input id="full_name" name="full_name" maxlength="100" value="'.h($values['full_name']).'" required></div>';
echo '<div class="field"><label for="username">Username *</label><input id="username" name="username" maxlength="50" value="'.h($values['username']).'" required></div>';
echo '<div class="field"><label for="contact_no">Contact number</label><input id="contact_no" name="contact_no" maxlength="20" value="'.h($values['contact_no']).'"></div>';
echo '<div class="field"><label for="role">Staff role *</label><select id="role" name="role" required>';
foreach(['admin'=>'System Administrator','bdrrmc'=>'BDRRMC Coordinator','official'=>'Barangay Official','responder'=>'CDRRMC Agent / Responder'] as $k=>$v)echo '<option value="'.h($k).'" '.($values['role']===$k?'selected':'').'>'.h($v).'</option>';
echo '</select></div><div class="field"><label for="password">Temporary password *</label><input id="password" name="password" type="password" minlength="8" required></div><div class="field"><label for="confirm_password">Confirm password *</label><input id="confirm_password" name="confirm_password" type="password" minlength="8" required></div></div><button class="button" type="submit">Create account</button></form></section>';
pageEnd();
