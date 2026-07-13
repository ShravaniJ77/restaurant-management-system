<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Admin Login';
$currentPage = 'login';
$showLayout = false;

if (requireLogin()) {
    header('Location: ' . BASE_URL . '/pages/dashboard/dashboard.php');
    exit;
}

ensureDefaultAdmin();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs and verify CSRF token
    $email = sanitizeString($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken, 'login')) {
        $errors[] = 'Invalid request. Please try again.';
    }

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter both your email address and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $connection = getDbConnection();
        $statement = $connection->prepare('SELECT id, full_name, email, password_hash FROM admins WHERE email = ? LIMIT 1');
        $statement->bind_param('s', $email);
        $statement->execute();
        $result = $statement->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password_hash'])) {
                // Regenerate session id after successful login to prevent fixation.
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int) $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['last_activity'] = time();

                $statement->close();
                $connection->close();

                setFlashMessage('success', 'Welcome back, ' . htmlspecialchars($admin['full_name']) . '!');
                header('Location: ' . BASE_URL . '/pages/dashboard/dashboard.php');
                exit;
            }
        }

        $errors[] = 'Invalid email or password. Please try again.';
        $statement->close();
        $connection->close();
    }
}

require_once ROOT_PATH . 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4 rounded-4 shadow-sm">
            <div class="text-center mb-4">
                <div class="brand-icon mx-auto mb-3">
                    <i class="bi bi-lock"></i>
                </div>
                <h3 class="fw-semibold">Admin Login</h3>
                <p class="text-muted">Secure access to the restaurant management console.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger rounded-3" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/pages/auth/login.php">
                <?php echo csrfInputField('login'); ?>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Sign In</button>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
