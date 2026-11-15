<?php
require_once __DIR__ . '/includes/auth.php';
if(signedIn()) go('dashboard.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    checkCsrf();
    $username=trim((string)($_POST['username']??'')); $password=(string)($_POST['password']??'');
    if($username===''||$password==='') $error='Enter your username and password.';
    else{
        try{
            $stmt=db()->prepare('SELECT user_id,full_name,username,password_hash,role,is_active FROM users WHERE username=? LIMIT 1');
            $stmt->execute([$username]); $u=$stmt->fetch();
            if(!$u || !password_verify($password,$u['password_hash'])) $error='The username or password is incorrect.';
            elseif((int)$u['is_active']!==1) $error='This account is inactive. Contact the System Administrator.';
            else{
                session_regenerate_id(true);
                $_SESSION['staff']=['user_id'=>(int)$u['user_id'],'full_name'=>$u['full_name'],'username'=>$u['username'],'role'=>$u['role']];
                go('dashboard.php');
            }
        }catch(Throwable $e){error_log($e->getMessage());$error='Unable to sign in. Check the database connection.';}
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Staff Login | Surong Guisad</title><link rel="stylesheet" href="assets/style.css"></head><body><?php publicHeader(); ?><main class="auth-wrap"><section class="auth-card"><div class="kicker">Authorized personnel only</div><h1>Staff Login</h1><p class="muted">Sign in using an account created by the System Administrator.</p><?php if($error): ?><div class="notice error"><?= h($error) ?></div><?php endif; ?><form method="post" action="login.php"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><div class="field"><label for="username">Username</label><input id="username" name="username" maxlength="50" autocomplete="username" required></div><div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div><button class="button" type="submit">Sign in</button></form><p class="small" style="margin-top:18px">Public visitors do not need an account. <a href="index.php" style="color:#16768c">Return to public portal</a>.</p></section></main></body></html>
