<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$isHttps,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
function h(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $location): never { header('Location: '.$location); exit; }
function isLoggedIn(): bool { return isset($_SESSION['staff']) && is_array($_SESSION['staff']); }
function currentStaff(): ?array { return isLoggedIn() ? $_SESSION['staff'] : null; }
function requireLogin(): void { if (!isLoggedIn()) redirect('login.php'); }
function isSystemAdministrator(): bool { return isLoggedIn() && (currentStaff()['role'] ?? '') === 'admin'; }
function roleName(string $role): string { return ['admin'=>'System Administrator','bdrrmc'=>'BDRRMC Coordinator','official'=>'Barangay Official','responder'=>'CDRRMC Agent / Responder'][$role] ?? 'Staff'; }
function hasPermission(string $permission): bool {
    if (!isLoggedIn()) return false;
    if (isSystemAdministrator()) return true;
    try {
        $stmt=db()->prepare('SELECT allowed FROM user_permissions WHERE user_id=:uid AND permission_key=:permission LIMIT 1');
        $stmt->execute(['uid'=>(int)currentStaff()['user_id'],'permission'=>$permission]);
        return (int)$stmt->fetchColumn()===1;
    } catch (PDOException $e) { error_log($e->getMessage()); return false; }
}
function requirePermission(string $permission): void { requireLogin(); if (!hasPermission($permission)) { http_response_code(403); exit('Access denied. Your account has not been granted this permission.'); } }
function requireRoles(array $allowedRoles): void { requireLogin(); if (!isSystemAdministrator() && !in_array(currentStaff()['role']??'', $allowedRoles, true)) { http_response_code(403); exit('Access denied.'); } }
function csrfToken(): string { if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function verifyCsrfToken(): void { $token=$_POST['csrf_token']??''; if(!is_string($token)||!hash_equals(csrfToken(),$token)){http_response_code(400);exit('Invalid form submission. Refresh and try again.');} }
function auditLog(string $action,string $entity,?int $entityId,string $details=''):void {
    try{$q=db()->prepare('INSERT INTO audit_logs(user_id,action,entity_type,entity_id,details) VALUES(:uid,:action,:entity,:id,:details)');$q->execute(['uid'=>(int)currentStaff()['user_id'],'action'=>$action,'entity'=>$entity,'id'=>$entityId,'details'=>$details]);}
    catch(PDOException $e){error_log('Audit log: '.$e->getMessage());}
}
