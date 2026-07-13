<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Edit Customer';
$currentPage = 'customers';

$customerId = (int) ($_GET['id'] ?? 0);
$errors = [];

if ($customerId <= 0) {
    setFlashMessage('error', 'Invalid customer selected.');
    header('Location: ' . BASE_URL . '/pages/customers/view_customers.php');
    exit;
}

$connection = getDbConnection();
$statement = $connection->prepare('SELECT id, full_name, phone, email, address FROM customers WHERE id = ? LIMIT 1');
$statement->bind_param('i', $customerId);
$statement->execute();
$result = $statement->get_result();
$customer = $result->fetch_assoc();
$statement->close();
$connection->close();

if (!$customer) {
    setFlashMessage('error', 'The selected customer was not found.');
    header('Location: ' . BASE_URL . '/pages/customers/view_customers.php');
    exit;
}

$fullName = $customer['full_name'];
$phone = $customer['phone'];
$email = $customer['email'];
$address = $customer['address'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf, 'edit_customer')) {
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
        $updateStatement = $connection->prepare('UPDATE customers SET full_name = ?, phone = ?, email = ?, address = ? WHERE id = ?');
        $updateStatement->bind_param('ssssi', $fullName, $phone, $email, $address, $customerId);
        $updateStatement->execute();
        $updateStatement->close();
        $connection->close();

        setFlashMessage('success', 'Customer updated successfully.');
        header('Location: ' . BASE_URL . '/pages/customers/view_customers.php');
        exit;
    }
}

require_once ROOT_PATH . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-semibold mb-1">Edit Customer</h3>
        <p class="text-muted mb-0">Update customer account details.</p>
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
        <?php echo csrfInputField('edit_customer'); ?>
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($fullName); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="4"><?php echo htmlspecialchars($address); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Customer</button>
    </form>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
