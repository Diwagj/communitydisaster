<?php
declare(strict_types=1);




class Auth
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['staff']) && is_array($_SESSION['staff']);
    }

    public function currentStaff(): ?array
    {
        return $this->isLoggedIn() ? $_SESSION['staff'] : null;
    }

    public function isSystemAdministrator(): bool
    {
        return $this->isLoggedIn() && (currentStaff()['role'] ?? '') === 'admin';
    }

    public function roleName(string $role): string
    {
        switch ($role) {
            case 'admin':
                return 'System Administrator';
            case 'bdrrmc':
                return 'BDRRMC Coordinator';
            case 'official':
                return 'Barangay Official';
            case 'responder':
                return 'CDRRMC Agent / Responder';
            default:
                return 'Staff';
        }
    }

    public function hasPermission(string $permission): bool
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        
        if ($this->isSystemAdministrator()) {
            return true;
        }

        try {
            $stmt = $this->pdo->prepare(
                'SELECT allowed
                 FROM user_permissions
                 WHERE user_id = :uid AND permission_key = :permission
                 LIMIT 1'
            );
            $stmt->execute([
                'uid' => (int) $_SESSION['staff']['user_id'],
                'permission' => $permission,
            ]);

            return (int) $stmt->fetchColumn() === 1;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public function verifyCsrfToken(): void
    {
        $submitted = $_POST['csrf_token'] ?? '';

        if (!is_string($submitted) || !hash_equals($this->csrfToken(), $submitted)) {
            http_response_code(400);
            exit('Invalid form submission. Refresh the page and try again.');
        }
    }

    public function auditLog(string $action, string $entityType, ?int $entityId, string $details = ''): void
    {
        if (!$this->isLoggedIn()) {
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details)
                 VALUES (:uid, :action, :entity, :id, :details)'
            );
            $stmt->execute([
                'uid' => (int) $_SESSION['staff']['user_id'],
                'action' => $action,
                'entity' => $entityType,
                'id' => $entityId,
                'details' => $details,
            ]);
        } catch (PDOException $e) {
            error_log('Audit log error: ' . $e->getMessage());
        }
    }
}