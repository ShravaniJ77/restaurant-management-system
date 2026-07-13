<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Add Customer';
$currentPage = 'customers';

$errors = [];
$fullName = '';
$phone = '';
$email = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token and sanitize inputs
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf, 'add_customer')) {
        $errors[] = 'Invalid request. Please try again.';
    }

    $fullName = sanitizeString($_POST['full_name'] ?? '');
    $phone = sanitizeString($_POST['phone'] ?? '');
    $email = sanitizeString($_POST['email'] ?? '');
    $address = sanitizeString($_POST['address'] ?? '');

    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    }

    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        $connection = getDbConnection();
        $statement = $connection->prepare('INSERT INTO customers (full_name, phone, email, address) VALUES (?, ?, ?, ?)');
        $statement->bind_param('ssss', $fullName, $phone, $email, $address);
        $statement->execute();
        $statement->close();
        $connection->close();

        setFlashMessage('success', 'Customer added successfully.');
        header('Location: ' . BASE_URL . '/pages/customers/view_customers.php');
        exit;
    }
}

require_once ROOT_PATH . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-semibold mb-1">Add Customer</h3>
        <p class="text-muted mb-0">Create a new customer profile.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/pages/customers/view_customers.php" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card p-4">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger rounded-3" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <?php echo csrfInputField('add_customer'); ?>
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" placeholder="Enter customer name" value="<?php echo htmlspecialchars($fullName); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="Enter phone number" value="<?php echo htmlspecialchars($phone); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="customer@example.com" value="<?php echo htmlspecialchars($email); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="4" placeholder="Customer address"><?php echo htmlspecialchars($address); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Customer</button>
    </form>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
