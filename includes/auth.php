<?php
require_once __DIR__ . '/functions.php';

// Check whether a user session exists.
function requireLogin(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['admin_id']);
}

// Enforce authentication and verify admin account is active.
function requireAdminAuth(): void
{
    if (!requireLogin()) {
        setFlashMessage('error', 'Please sign in to continue.');
        header('Location: ' . BASE_URL . '/pages/auth/login.php');
        exit;
    }

    // Verify admin still active in database.
    $adminId = (int) ($_SESSION['admin_id'] ?? 0);
    if ($adminId <= 0) {
        logoutAdmin();
        header('Location: ' . BASE_URL . '/pages/auth/login.php');
        exit;
    }

    $connection = getDbConnection();
    $stmt = $connection->prepare('SELECT status FROM admins WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    $connection->close();

    if (!$row || ($row['status'] ?? '') !== 'active') {
        // Account disabled; log out and inform user.
        logoutAdmin();
        setFlashMessage('error', 'Your account is not active. Please contact the administrator.');
        header('Location: ' . BASE_URL . '/pages/auth/login.php');
        exit;
    }
}

function ensureDefaultAdmin(): void
{
    $connection = getDbConnection();

    $checkStatement = $connection->prepare('SELECT id FROM admins LIMIT 1');
    $checkStatement->execute();
    $result = $checkStatement->get_result();

    if ($result->num_rows === 0) {
        $fullName = 'System Administrator';
        $email = 'admin@example.com';
        $passwordHash = password_hash('Admin@123', PASSWORD_DEFAULT);
        $phone = '0000000000';
        $status = 'active';

        $insertStatement = $connection->prepare('INSERT INTO admins (full_name, email, password_hash, phone, status) VALUES (?, ?, ?, ?, ?)');
        $insertStatement->bind_param('sssss', $fullName, $email, $passwordHash, $phone, $status);
        $insertStatement->execute();
        $insertStatement->close();
    }

    $checkStatement->close();
    $connection->close();
}

function logoutAdmin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $message = 'You have been logged out successfully.';
    session_unset();
    session_destroy();

    session_start();
    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => $message,
    ];
}
?>
