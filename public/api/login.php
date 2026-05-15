<?php
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new Auth();

// ── Brute-force / rate-limit check ───────────────────────────────────────────
if ($auth->isLockedOut()) {
    $remaining = ceil($auth->lockoutSecondsRemaining() / 60);
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => "Too many failed attempts. Please try again in {$remaining} minute(s)."
    ]);
    exit;
}

// ── Input validation ─────────────────────────────────────────────────────────
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

// ── Attempt login ─────────────────────────────────────────────────────────────
try {
    if ($auth->login($username, $password)) {
        $auth->resetLoginAttempts();
        $user     = $auth->getUser();
        $redirect = ($user['role'] === 'principal') ? 'principal_dashboard.php' : 'dashboard.php';
        echo json_encode(['success' => true, 'redirect' => $redirect]);
    } else {
        $auth->recordFailedAttempt();
        $attemptsLeft = max(0, 5 - ($_SESSION['login_attempts'] ?? 0));
        $msg = $attemptsLeft > 0
            ? "Invalid username or password. {$attemptsLeft} attempt(s) remaining."
            : 'Account temporarily locked. Please try again in 15 minutes.';
        echo json_encode(['success' => false, 'message' => $msg]);
    }
} catch (Exception $e) {
    error_log('Login exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A system error occurred. Please contact the administrator.']);
}
?>