<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/orders/view_orders.php');
    exit;
}

$orderId = (int) ($_POST['id'] ?? 0);
// Verify CSRF token
$csrf = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrf, 'delete_order')) {
    setFlashMessage('error', 'Invalid request.');
    header('Location: ' . BASE_URL . '/pages/orders/view_orders.php');
    exit;
}
if ($orderId <= 0) {
    setFlashMessage('error', 'Invalid order selected.');
    header('Location: ' . BASE_URL . '/pages/orders/view_orders.php');
    exit;
}

$connection = getDbConnection();
$connection->begin_transaction();

try {
    $itemStatement = $connection->prepare('DELETE FROM order_items WHERE order_id = ?');
    $itemStatement->bind_param('i', $orderId);
    $itemStatement->execute();
    $itemStatement->close();

    $orderStatement = $connection->prepare('DELETE FROM orders WHERE id = ?');
    $orderStatement->bind_param('i', $orderId);
    $orderStatement->execute();
    $orderStatement->close();

    $connection->commit();
    setFlashMessage('success', 'Order deleted successfully.');
} catch (Exception $exception) {
    $connection->rollback();
    setFlashMessage('error', 'Unable to delete the order right now.');
}

$connection->close();
header('Location: ' . BASE_URL . '/pages/orders/view_orders.php');
exit;
