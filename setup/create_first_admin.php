<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
if(PHP_SAPI!=='cli'){http_response_code(403);exit("Run this script from the command line only.\n");}
try{
 $pdo=db();
 if((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn()>0)exit("Setup stopped: a user already exists.\n");
 $name=trim((string)readline('Administrator full name: '));
 $username=trim((string)readline('Username (4-50 characters): '));
 $password=(string)readline('Password (minimum 8 characters): ');
 if($name===''||strlen($name)>100||!preg_match('/^[A-Za-z0-9._-]{4,50}$/',$username)||strlen($password)<8)exit("Invalid details. No account created.\n");
 $stmt=$pdo->prepare("INSERT INTO users(full_name,username,password_hash,role,is_active) VALUES(?,?,?,'admin',1)");
 $stmt->execute([$name,$username,password_hash($password,PASSWORD_DEFAULT)]);
 echo "First System Administrator account created. Sign in through login.php.\n";
}catch(Throwable $e){fwrite(STDERR,"Setup failed: ".$e->getMessage()."\n");exit(1);}
