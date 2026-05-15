<?php
require_once __DIR__ . '/Database.php';

class Auth
{
    private $db = null;

    // Login brute-force settings
    private const MAX_ATTEMPTS    = 5;
    private const LOCKOUT_SECONDS = 900; // 15 minutes

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function getDb()
    {
        if ($this->db === null) {
            $this->db = Database::getInstance()->getConnection();
        }
        return $this->db;
    }

    public function login($username, $password)
    {
        $stmt = $this->getDb()->prepare("SELECT user_id, username, password_hash, full_name, role FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Password correct
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            // Update last login
            $updateStmt = $this->getDb()->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id");
            $updateStmt->execute(['id' => $user['user_id']]);

            return true;
        }

        return false;
    }

    public function logout()
    {
        session_unset();
        session_destroy();
    }

    public function isAuthenticated()
    {
        return isset($_SESSION['user_id']);
    }

    public function getUser()
    {
        if ($this->isAuthenticated()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'full_name' => $_SESSION['full_name']
            ];
        }
        return null;
    }

    public function isFaculty()
    {
        return $this->isAuthenticated() && $_SESSION['role'] === 'admin';
    }

    public function isHod()
    {
        return $this->isAuthenticated() && $_SESSION['role'] === 'hod';
    }

    public function isPrincipal()
    {
        return $this->isAuthenticated() && $_SESSION['role'] === 'principal';
    }

    public function requireLogin()
    {
        if (!$this->isAuthenticated()) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }

    public function requirePrincipal()
    {
        $this->requireLogin();
        if (!$this->isPrincipal()) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }

    public function requireFaculty()
    {
        $this->requireLogin();
        if (!$this->isFaculty()) {
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        }
    }

    public function requireHod()
    {
        $this->requireLogin();
        if (!$this->isHod()) {
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        }
    }

    public function requireFacultyOrHod()
    {
        $this->requireLogin();
        if ($this->isPrincipal()) {
            header('Location: ' . BASE_URL . '/principal_dashboard.php');
            exit;
        }
        if (!$this->isFaculty() && !$this->isHod()) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }

    // ─── CSRF Protection ─────────────────────────────────────────────────────

    /**
     * Generate (or retrieve existing) CSRF token for the current session.
     */
    public function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate a CSRF token submitted via POST field or X-CSRF-Token header.
     */
    public function validateCsrfToken(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // ─── Login Rate Limiting ─────────────────────────────────────────────────

    /**
     * Check if this client is currently locked out due to too many failed logins.
     */
    public function isLockedOut(): bool
    {
        if (!isset($_SESSION['login_attempts'])) {
            return false;
        }
        $attempts  = $_SESSION['login_attempts'];
        $lastFail  = $_SESSION['login_last_fail'] ?? 0;
        $elapsed   = time() - $lastFail;

        if ($attempts >= self::MAX_ATTEMPTS) {
            if ($elapsed < self::LOCKOUT_SECONDS) {
                return true; // still within lockout window
            }
            // lockout expired — reset
            $this->resetLoginAttempts();
        }
        return false;
    }

    /**
     * Returns seconds remaining in the lockout window, or 0 if not locked.
     */
    public function lockoutSecondsRemaining(): int
    {
        if (!$this->isLockedOut()) return 0;
        $elapsed = time() - ($_SESSION['login_last_fail'] ?? 0);
        return max(0, self::LOCKOUT_SECONDS - $elapsed);
    }

    /** Call on every failed login attempt. */
    public function recordFailedAttempt(): void
    {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['login_last_fail'] = time();
    }

    /** Call on successful login to clear the counter. */
    public function resetLoginAttempts(): void
    {
        unset($_SESSION['login_attempts'], $_SESSION['login_last_fail']);
    }
}
?>