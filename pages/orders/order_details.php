<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Order Details';
$currentPage = 'orders';

$orderId = (int) ($_GET['id'] ?? 0);
$errors = [];
$statuses = ['Pending', 'Preparing', 'Ready', 'Served', 'Cancelled'];

if ($orderId <= 0) {
    setFlashMessage('error', 'Invalid order requested.');
    header('Location: ' . BASE_URL . '/pages/orders/view_orders.php');
    exit;
}

$connection = getDbConnection();
$statement = $connection->prepare('SELECT o.id, o.customer_id, o.admin_id, o.order_date, o.total_amount, o.payment_status, o.order_status, c.full_name AS customer_name, a.full_name AS admin_name FROM orders o LEFT JOIN customers c ON c.id = o.customer_id LEFT JOIN admins a ON a.id = o.admin_id WHERE o.id = ? LIMIT 1');
$statement->bind_param('i', $orderId);
$statement->execute();
$orderResult = $statement->get_result();
$order = $orderResult->fetch_assoc();
$statement->close();

if (!$order) {
    $connection->close();
    setFlashMessage('error', 'The selected order was not found.');
    header('Location: ' . BASE_URL . '/pages/orders/view_orders.php');
    exit;
}

$itemStatement = $connection->prepare('SELECT oi.quantity, oi.unit_price, oi.line_total, mi.name AS menu_item_name FROM order_items oi LEFT JOIN menu_items mi ON mi.id = oi.menu_item_id WHERE oi.order_id = ? ORDER BY oi.id ASC');
$itemStatement->bind_param('i', $orderId);
$itemStatement->execute();
$orderItems = $itemStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$itemStatement->close();
$connection->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update the order status from the detail form.
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf, 'update_order_status')) {
        $errors[] = 'Invalid request. Please try again.';
    }

    $newStatus = trim($_POST['order_status'] ?? '');
    if (!in_array($newStatus, $statuses, true)) {
        $errors[] = 'Please select a valid order status.';
    }

    if (empty($errors)) {
        $connection = getDbConnection();
        $updateStatement = $connection->prepare('UPDATE orders SET order_status = ? WHERE id = ?');
        $updateStatement->bind_param('si', $newStatus, $orderId);
        $updateStatement->execute();
        $updateStatement->close();
        $connection->close();

        setFlashMessage('success', 'Order status updated successfully.');
        header('Location: ' . BASE_URL . '/pages/orders/order_details.php?id=' . $orderId);
        exit;
    }
}

require_once ROOT_PATH . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-semibold mb-1">Order Details</h3>
        <p class="text-muted mb-0">View and update the selected order.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/pages/orders/view_orders.php" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card p-4 mb-4">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger rounded-3" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-3">
            <p class="text-muted mb-1">Order ID</p>
            <h5 class="fw-semibold">#<?php echo (int) $order['id']; ?></h5>
        </div>
        <div class="col-md-3">
            <p class="text-muted mb-1">Customer</p>
            <h5 class="fw-semibold"><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></h5>
        </div>
        <div class="col-md-3">
            <p class="text-muted mb-1">Payment Status</p>
            <h5 class="fw-semibold"><?php echo htmlspecialchars(ucfirst($order['payment_status'] ?? 'unpaid')); ?></h5>
        </div>
        <div class="col-md-3">
            <p class="text-muted mb-1">Placed On</p>
            <h5 class="fw-semibold"><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($order['order_date']))); ?></h5>
        </div>
    </div>

    <div class="mt-4">
        <form method="POST" class="row g-3 align-items-end">
            <?php echo csrfInputField('update_order_status'); ?>
            <div class="col-md-8">
                <label class="form-label">Update Order Status</label>
                <select name="order_status" class="form-select">
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo ($order['order_status'] ?? 'Pending') === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Update Status</button>
            </div>
        </form>
    </div>
</div>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-semibold mb-0">Items</h5>
        <div class="text-end">
            <p class="text-muted mb-0">Grand Total</p>
            <h4 class="fw-semibold mb-0">$<?php echo number_format((float) $order['total_amount'], 2); ?></h4>
        </div>
    </div>

    <?php if (empty($orderItems)): ?>
        <?php echo renderEmptyState('No items found', 'This order does not contain any line items yet.'); ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr class="text-muted">
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['menu_item_name'] ?? 'Unknown item'); ?></td>
                            <td><?php echo (int) $item['quantity']; ?></td>
                            <td>$<?php echo number_format((float) $item['unit_price'], 2); ?></td>
                            <td>$<?php echo number_format((float) $item['line_total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>/pages/orders/delete_order.php" class="mt-4" onsubmit="return confirm('Delete this order and all of its items?');">
        <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
        <?php echo csrfInputField('delete_order'); ?>
        <button type="submit" class="btn btn-outline-danger">Delete Order</button>
    </form>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
