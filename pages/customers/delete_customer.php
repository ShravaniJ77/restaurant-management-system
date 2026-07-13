<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/customers/view_customers.php');
    exit;
}

$customerId = (int) ($_POST['id'] ?? 0);
// Verify CSRF token for delete operations
$csrf = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrf, 'delete_customer')) {
    setFlashMessage('error', 'Invalid request.');
    header('Location: ' . BASE_URL . '/pages/customers/view_customers.php');
    exit;
}
if ($customerId <= 0) {
    setFlashMessage('error', 'Invalid customer selected.');
    header('Location: ' . BASE_URL . '/pages/customers/view_customers.php');
    exit;
}

$connection = getDbConnection();

// Check if any orders reference this customer before deleting, otherwise the
// DELETE below fails on the fk_order_customer foreign key constraint.
$checkStatement = $connection->prepare('SELECT COUNT(*) AS order_count FROM orders WHERE customer_id = ?');
$checkStatement->bind_param('i', $customerId);
$checkStatement->execute();
$checkResult = $checkStatement->get_result();
$orderCount = (int) ($checkResult->fetch_assoc()['order_count'] ?? 0);
$checkStatement->close();

if ($orderCount > 0) {
    $connection->close();
    setFlashMessage('error', 'Cannot delete this customer. There are ' . $orderCount . ' order(s) linked to their account.');
    header('Location: ' . BASE_URL . '/pages/customers/view_customers.php');
    exit;
}

$statement = $connection->prepare('DELETE FROM customers WHERE id = ?');
$statement->bind_param('i', $customerId);
$statement->execute();
$statement->close();
$connection->close();

setFlashMessage('success', 'Customer deleted successfully.');
header('Location: ' . BASE_URL . '/pages/customers/view_customers.php');
exit;
