<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Invoice';
$currentPage = 'billing';

// Load the latest order by default and allow the user to switch to another order.
$selectedOrderId = (int) ($_GET['order_id'] ?? 0);
$orders = [];
$selectedOrder = null;
$selectedOrderItems = [];
$invoiceNumber = '';
$invoiceDate = '';

$connection = getDbConnection();

$ordersStatement = $connection->prepare('SELECT o.id, o.order_date, o.total_amount, o.order_status, c.full_name AS customer_name FROM orders o LEFT JOIN customers c ON c.id = o.customer_id ORDER BY o.order_date DESC');
$ordersStatement->execute();
$orders = $ordersStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$ordersStatement->close();

if ($selectedOrderId <= 0 && !empty($orders)) {
    $selectedOrderId = (int) $orders[0]['id'];
}

if ($selectedOrderId > 0) {
    // Fetch the selected order and its line items for invoice rendering.
    $orderStatement = $connection->prepare('SELECT o.id, o.order_date, o.total_amount, o.payment_status, o.order_status, c.full_name AS customer_name, c.phone, c.email, c.address, a.full_name AS admin_name FROM orders o LEFT JOIN customers c ON c.id = o.customer_id LEFT JOIN admins a ON a.id = o.admin_id WHERE o.id = ? LIMIT 1');
    $orderStatement->bind_param('i', $selectedOrderId);
    $orderStatement->execute();
    $selectedOrder = $orderStatement->get_result()->fetch_assoc();
    $orderStatement->close();

    if ($selectedOrder) {
        $itemStatement = $connection->prepare('SELECT oi.quantity, oi.unit_price, oi.line_total, mi.name AS menu_item_name FROM order_items oi LEFT JOIN menu_items mi ON mi.id = oi.menu_item_id WHERE oi.order_id = ? ORDER BY oi.id ASC');
        $itemStatement->bind_param('i', $selectedOrderId);
        $itemStatement->execute();
        $selectedOrderItems = $itemStatement->get_result()->fetch_all(MYSQLI_ASSOC);
        $itemStatement->close();

        $invoiceNumber = 'INV-' . str_pad((string) $selectedOrder['id'], 4, '0', STR_PAD_LEFT);
        $invoiceDate = date('d M Y, H:i', strtotime($selectedOrder['order_date']));
    }
}

$connection->close();

require_once ROOT_PATH . 'includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-semibold mb-1">Invoice</h3>
        <p class="text-muted mb-0">Generate a professional bill from a completed order.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/pages/orders/view_orders.php" class="btn btn-outline-secondary">Manage Orders</a>
</div>

<div class="card p-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-md-8">
            <label class="form-label">Select Order</label>
            <select name="order_id" class="form-select">
                <option value="">Choose an order</option>
                <?php foreach ($orders as $order): ?>
                    <option value="<?php echo (int) $order['id']; ?>" <?php echo $selectedOrderId === (int) $order['id'] ? 'selected' : ''; ?>>#<?php echo (int) $order['id']; ?> - <?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?> - <?php echo htmlspecialchars($order['order_status']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-4">
            <button type="submit" class="btn btn-primary w-100">Load Invoice</button>
        </div>
    </form>
</div>

<?php if (!$selectedOrder): ?>
    <div class="card p-4 mt-4">
        <?php echo renderEmptyState('No invoice available', 'Create an order first to generate a bill.'); ?>
    </div>
<?php else: ?>
    <div class="card p-4 mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="fw-semibold mb-1">Invoice Preview</h4>
                <p class="text-muted mb-0">A clean invoice preview ready for printing.</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/pages/billing/printable_bill.php?order_id=<?php echo (int) $selectedOrder['id']; ?>" target="_blank" class="btn btn-primary">Print Invoice</a>
        </div>

        <div class="border rounded-4 p-4 invoice-preview">
            <div class="row g-3 align-items-start">
                <div class="col-md-8">
                    <h3 class="fw-bold mb-1">The Golden Spoon</h3>
                    <p class="text-muted mb-1">123 Restaurant Street, Downtown</p>
                    <p class="text-muted mb-0">Phone: +1 555 123 456 | Email: billing@thegoldenspoon.com</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <h5 class="fw-semibold mb-1">Invoice <?php echo htmlspecialchars($invoiceNumber); ?></h5>
                    <p class="text-muted mb-1">Date: <?php echo htmlspecialchars($invoiceDate); ?></p>
                    <span class="badge bg-success"><?php echo htmlspecialchars($selectedOrder['order_status']); ?></span>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="fw-semibold">Bill To</h6>
                    <p class="mb-1 fw-semibold"><?php echo htmlspecialchars($selectedOrder['customer_name'] ?? 'Guest'); ?></p>
                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($selectedOrder['address'] ?? 'No address provided'); ?></p>
                    <p class="mb-1 text-muted">Phone: <?php echo htmlspecialchars($selectedOrder['phone'] ?? 'N/A'); ?></p>
                    <p class="mb-0 text-muted">Email: <?php echo htmlspecialchars($selectedOrder['email'] ?? 'N/A'); ?></p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-semibold">Order Summary</h6>
                    <p class="mb-1 text-muted">Order ID: #<?php echo (int) $selectedOrder['id']; ?></p>
                    <p class="mb-1 text-muted">Payment Status: <?php echo htmlspecialchars(ucfirst($selectedOrder['payment_status'] ?? 'unpaid')); ?></p>
                    <p class="mb-1 text-muted">Served By: <?php echo htmlspecialchars($selectedOrder['admin_name'] ?? 'System'); ?></p>
                    <p class="mb-0 text-muted">Order Time: <?php echo htmlspecialchars($invoiceDate); ?></p>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($selectedOrderItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['menu_item_name'] ?? 'Unknown item'); ?></td>
                                <td>$<?php echo number_format((float) $item['unit_price'], 2); ?></td>
                                <td><?php echo (int) $item['quantity']; ?></td>
                                <td>$<?php echo number_format((float) $item['line_total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end mt-3">
                <div class="col-md-4">
                    <div class="d-flex justify-content-between border-top pt-3">
                        <span class="fw-semibold">Grand Total</span>
                        <span class="fw-semibold">$<?php echo number_format((float) $selectedOrder['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
